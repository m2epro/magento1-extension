<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

// move from listing to listing

class Ess_M2ePro_Adminhtml_Listing_MovingController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //#############################################

    public function moveToListingGridAction()
    {
        Mage::helper('M2ePro/Data_Global')->setValue(
            'componentMode', $this->getRequest()->getParam('componentMode')
        );
        Mage::helper('M2ePro/Data_Global')->setValue(
            'accountId', $this->getRequest()->getParam('accountId')
        );
        Mage::helper('M2ePro/Data_Global')->setValue(
            'marketplaceId', $this->getRequest()->getParam('marketplaceId')
        );
        Mage::helper('M2ePro/Data_Global')->setValue(
            'ignoreListings', json_decode($this->getRequest()->getParam('ignoreListings'))
        );

        $block = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_moving_grid','',
            array(
                'grid_url' => $this->getUrl('*/adminhtml_listing_moving/moveToListingGrid', array('_current'=>true)),
                'moving_handler_js' => 'ListingGridHandlerObj.movingHandler',
            )
        );
        $this->getResponse()->setBody($block->toHtml());
    }

    //#############################################

    public function getFailedProductsGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_moving_failedProducts','',
            array(
                'grid_url' => $this->getUrl('*/adminhtml_listing_moving/failedProductsGrid', array('_current'=>true))
            )
        );
        $this->getResponse()->setBody($block->toHtml());
    }

    public function failedProductsGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_moving_failedProducts_grid','',
            array(
                'grid_url' => $this->getUrl('*/adminhtml_listing_moving/failedProductsGrid', array('_current'=>true))
            )
        );
        $this->getResponse()->setBody($block->toHtml());
    }

    //#############################################

    public function prepareMoveToListingAction()
    {
        $componentMode = $this->getRequest()->getParam('componentMode');
        $selectedProducts = (array)json_decode($this->getRequest()->getParam('selectedProducts'));

        $listingProductCollection = Mage::helper('M2ePro/Component')
            ->getComponentModel($componentMode, 'Listing_Product')
            ->getCollection();

        $listingProductCollection->addFieldToFilter('`main_table`.`id`', array('in' => $selectedProducts));
        $tempData = $listingProductCollection
            ->getSelect()
            ->join( array('listing'=>Mage::getSingleton('core/resource')->getTableName('m2epro_listing')),
                    '`main_table`.`listing_id` = `listing`.`id`' )
            ->join( array('cpe'=>Mage::getSingleton('core/resource')->getTableName('catalog_product_entity')),
                    '`main_table`.`product_id` = `cpe`.`entity_id`' )
            ->group(array('listing.account_id','listing.marketplace_id'))
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('marketplace_id', 'account_id'), 'listing')
            ->query()
            ->fetchAll();

        return $this->getResponse()->setBody(json_encode(array(
            'accountId' => $tempData[0]['account_id'],
            'marketplaceId' => $tempData[0]['marketplace_id'],
        )));
    }

    //#############################################

    public function tryToMoveToListingAction()
    {
        $componentMode = $this->getRequest()->getParam('componentMode');
        $selectedProducts = (array)json_decode($this->getRequest()->getParam('selectedProducts'));
        $listingId = (int)$this->getRequest()->getParam('listingId');

        $listingInstance = Mage::helper('M2ePro/Component')->getCachedComponentObject(
            $componentMode,'Listing',$listingId
        );

        $failedProducts = array();
        foreach ($selectedProducts as $selectedProduct) {
            $listingProductInstance = Mage::helper('M2ePro/Component')->getComponentObject(
                $componentMode,'Listing_Product',$selectedProduct
            );

            if (!$this->productCanBeMoved($listingProductInstance->getProductId(), $listingInstance)) {
                $failedProducts[] = $listingProductInstance->getProductId();
            }
        }

        if (count($failedProducts) == 0) {
            return $this->getResponse()->setBody(json_encode(array('result' => 'success')));
        }

        return $this->getResponse()->setBody(json_encode(array(
            'result' => 'fail',
            'failed_products' => $failedProducts
        )));
    }

    //#############################################

    public function moveToListingAction()
    {
        $componentMode = $this->getRequest()->getParam('componentMode');

        $selectedProducts = (array)json_decode($this->getRequest()->getParam('selectedProducts'));
        $listingId = (int)$this->getRequest()->getParam('listingId');

        /** @var Ess_M2ePro_Model_Listing $listingInstance */
        $listingInstance = Mage::helper('M2ePro/Component')->getCachedComponentObject(
            $componentMode,'Listing',$listingId
        );

        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode($componentMode);

        $variationUpdaterModel = ucwords($listingInstance->getComponentMode())
            .'_Listing_Product_Variation_Updater';

        /** @var Ess_M2ePro_Model_Listing_Product_Variation_Updater $variationUpdaterObject */
        $variationUpdaterObject = Mage::getModel('M2ePro/'.$variationUpdaterModel);
        $variationUpdaterObject->beforeMassProcessEvent();

        $errors = 0;
        foreach ($selectedProducts as $listingProductId) {

            /** @var Ess_M2ePro_Model_Listing_Product $listingProductInstance */
            $listingProductInstance = Mage::helper('M2ePro/Component')
                ->getComponentObject($componentMode,'Listing_Product',$listingProductId);

            if ($listingProductInstance->isLockedObject() ||
                $listingProductInstance->isLockedObject('in_action')) {

                $logModel->addProductMessage(
                    $listingProductInstance->getListingId(),
                    $listingProductInstance->getProductId(),
                    $listingProductInstance->getId(),
                    Ess_M2ePro_Helper_Data::INITIATOR_USER,
                    NULL,
                    Ess_M2ePro_Model_Listing_Log::ACTION_MOVE_TO_LISTING,
                    // M2ePro_TRANSLATIONS
                    // Item was not Moved because it is in progress state now
                    'Item was not Moved because it is in progress state now',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                $errors++;
                continue;
            }

            if (!$this->productCanBeMoved($listingProductInstance->getProductId(), $listingInstance)) {

                $logModel->addProductMessage(
                    $listingProductInstance->getListingId(),
                    $listingProductInstance->getProductId(),
                    $listingProductInstance->getId(),
                    Ess_M2ePro_Helper_Data::INITIATOR_USER,
                    NULL,
                    Ess_M2ePro_Model_Listing_Log::ACTION_MOVE_TO_LISTING,
                    // M2ePro_TRANSLATIONS
                    // Item was not Moved
                    'Item was not Moved',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                $errors++;
                continue;
            }

            $logModel->addProductMessage(
                $listingId,
                $listingProductInstance->getProductId(),
                $listingProductInstance->getId(),
                Ess_M2ePro_Helper_Data::INITIATOR_USER,
                NULL,
                Ess_M2ePro_Model_Listing_Log::ACTION_MOVE_TO_LISTING,
                // M2ePro_TRANSLATIONS
                // Item was successfully Moved
                'Item was successfully Moved',
                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            $logModel->addProductMessage(
                $listingProductInstance->getListingId(),
                $listingProductInstance->getProductId(),
                $listingProductInstance->getId(),
                Ess_M2ePro_Helper_Data::INITIATOR_USER,
                NULL,
                Ess_M2ePro_Model_Listing_Log::ACTION_MOVE_TO_LISTING,
                // M2ePro_TRANSLATIONS
                // Item was successfully Moved
                'Item was successfully Moved',
                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            $isStoresDifferent = $listingProductInstance->getListing()->getStoreId() != $listingInstance->getStoreId();

            $listingProductInstance->setData('listing_id', $listingId)->save();
            $listingProductInstance->setListing($listingInstance);

            if ($isStoresDifferent) {
                $method = 'get'.ucfirst(strtolower($componentMode)).'Item';
                if (!$listingProductInstance->isNotListed()) {
                    $item = $listingProductInstance->getChildObject()->$method();
                    if ($item) {
                        $item->setData('store_id', $listingInstance->getStoreId())->save();
                    }
                }
            }

            if ($listingProductInstance->isComponentModeAmazon()) {
                /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
                $amazonListingProduct = $listingProductInstance->getChildObject();
                $variationManager = $amazonListingProduct->getVariationManager();

                if ($variationManager->isRelationParentType()) {
                    $this->moveChildrenToListing($listingProductId, $listingInstance);
                }
            }

            if ($isStoresDifferent) {
                $variationUpdaterObject->process($listingProductInstance);
            }
        }

        $variationUpdaterObject->afterMassProcessEvent();

        if ($errors == 0) {
            return $this->getResponse()->setBody(json_encode(array('result'=>'success')));
        } else {
            return $this->getResponse()->setBody(json_encode(array('result'=>'error', 'errors'=>$errors)));
        }
    }

    //#############################################

    private function productCanBeMoved($productId, $listing) {

        if ($listing->isComponentModeEbay()) {
            return !$listing->hasProduct($productId);
        }

        // Add attribute set filter
        //----------------------------
        $table = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity');
        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from($table,new Zend_Db_Expr('DISTINCT `entity_id`'))
            ->where('`entity_id` = ?',(int)$productId);

        $productArray = Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($dbSelect);

        if (count($productArray) <= 0) {
            return false;
        }

        return true;
    }

    //#############################################

    private function moveChildrenToListing($parentListingProductId, $listing)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        // Get child products ids
        //--------------------------
        $dbSelect = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Amazon_Listing_Product')->getMainTable(),
                array('listing_product_id', 'sku')
            )
            ->where('`variation_parent_id` = ?',$parentListingProductId);
        $products = $connRead->fetchPairs($dbSelect);

        if(!empty($products)) {
            $connWrite->update(
                Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable(),
                array(
                    'listing_id' => $listing->getId()
                ),
                '`id` IN (' . implode(',', array_keys($products)) . ')'
            );
        }

        $dbSelect = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Amazon_Item')->getMainTable(),
                array('id')
            )
            ->where('`account_id` = ?', $listing->getAccountId())
            ->where('`marketplace_id` = ?',$listing->getMarketplaceId())
            ->where('`sku` IN (?)', implode(',', array_values($products)));
        $items = $connRead->fetchCol($dbSelect);

        if(!empty($items)) {
            $connWrite->update(
                Mage::getResourceModel('M2ePro/Amazon_Item')->getMainTable(),
                array(
                    'store_id' => $listing->getStoreId()
                ),
                '`id` IN ('.implode(',', $items).')'
            );
        }
    }

    //#############################################
}