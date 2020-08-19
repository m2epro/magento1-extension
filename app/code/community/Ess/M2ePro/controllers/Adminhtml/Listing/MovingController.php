<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

// move from listing to listing

class Ess_M2ePro_Adminhtml_Listing_MovingController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //########################################

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
            'ignoreListings', Mage::helper('M2ePro')->jsonDecode($this->getRequest()->getParam('ignoreListings'))
        );

        $movingHandlerJs = 'ListingGridObj.movingHandler';
        if ($this->getRequest()->getParam('componentMode') == Ess_M2ePro_Helper_Component_Ebay::NICK) {
            $movingHandlerJs = 'EbayListingSettingsGridObj.movingHandler';
        }

        $block = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_moving_grid', '',
            array(
                'grid_url' => $this->getUrl('*/adminhtml_listing_moving/moveToListingGrid', array('_current'=>true)),
                'moving_handler_js' => $movingHandlerJs
            )
        );
        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function prepareMoveToListingAction()
    {
        $dbHelper = Mage::helper('M2ePro/Module_Database_Structure');
        $sessionHelper = Mage::helper('M2ePro/Data_Session');
        $componentMode = $this->getRequest()->getParam('componentMode');
        $sessionKey = $componentMode . '_' . Ess_M2ePro_Helper_View::MOVING_LISTING_PRODUCTS_SELECTED_SESSION_KEY;

        if ((bool)$this->getRequest()->getParam('is_first_part')) {
            $sessionHelper->removeValue($sessionKey);
        }

        $selectedProducts = array();
        if ($sessionValue = $sessionHelper->getValue($sessionKey)) {
            $selectedProducts = $sessionValue;
        }

        $selectedProductsPart = $this->getRequest()->getParam('products_part');
        $selectedProductsPart = explode(',', $selectedProductsPart);

        $selectedProducts = array_merge($selectedProducts, $selectedProductsPart);
        $sessionHelper->setValue($sessionKey, $selectedProducts);

        if (!(bool)$this->getRequest()->getParam('is_last_part')) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                    'result' => true
                    )
                )
            );
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component')
            ->getComponentModel($componentMode, 'Listing_Product')
            ->getCollection();

        $listingProductCollection->addFieldToFilter('main_table.id', array('in' => $selectedProducts));
        $row = $listingProductCollection
            ->getSelect()
            ->join(
                array('listing' => $dbHelper->getTableNameWithPrefix('m2epro_listing')),
                '`main_table`.`listing_id` = `listing`.`id`'
            )
            ->join(
                array('cpe' => $dbHelper->getTableNameWithPrefix('catalog_product_entity')),
                '`main_table`.`product_id` = `cpe`.`entity_id`'
            )
            ->group(array('listing.account_id', 'listing.marketplace_id'))
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('marketplace_id', 'account_id'), 'listing')
            ->query()
            ->fetch();

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'result'        => true,
                'accountId'     => (int)$row['account_id'],
                'marketplaceId' => (int)$row['marketplace_id'],
                )
            )
        );
    }

    //########################################

    public function moveToListingAction()
    {
        $sessionHelper = Mage::helper('M2ePro/Data_Session');

        $componentMode = $this->getRequest()->getParam('componentMode');
        $sessionKey = $componentMode . '_' . Ess_M2ePro_Helper_View::MOVING_LISTING_PRODUCTS_SELECTED_SESSION_KEY;
        $selectedProducts = $sessionHelper->getValue($sessionKey);

        /** @var Ess_M2ePro_Model_Listing $targetListing */
        $sourceListing = null;
        $targetListing = Mage::helper('M2ePro/Component')->getCachedComponentObject(
            $componentMode, 'Listing', (int)$this->getRequest()->getParam('listingId')
        );

        /** @var Ess_M2ePro_Model_Listing_Product_Variation_Updater $variationUpdaterObject */
        $variationUpdaterObject = Mage::getModel(
            'M2ePro/' . ucwords($componentMode) . '_Listing_Product_Variation_Updater'
        );
        $variationUpdaterObject->beforeMassProcessEvent();

        $errorsCount = 0;
        foreach ($selectedProducts as $listingProductId) {

            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = Mage::helper('M2ePro/Component')->getComponentObject(
                $componentMode, 'Listing_Product', $listingProductId
            );
            $sourceListing = $listingProduct->getListing();

            if (!$targetListing->getChildObject()->addProductFromListing($listingProduct, $sourceListing)) {
                $errorsCount++;
                continue;
            }

            if ($targetListing->getStoreId() != $sourceListing->getStoreId()) {
                $variationUpdaterObject->process($listingProduct);
            }
        }

        $variationUpdaterObject->afterMassProcessEvent();
        $sessionHelper->removeValue($sessionKey);

        if ($errorsCount) {
            $logViewUrl = $this->getUrl(
                '*/adminhtml_' . $componentMode . '_log/listing', array(
                    'listing_id' => $sourceListing->getId(),
                    'back' => Mage::helper('M2ePro')->makeBackUrlParam(
                        '*/adminhtml_' . $componentMode . '_listing/view',
                        array('id' => $sourceListing->getId())
                    )
                )
            );

            if (count($selectedProducts) == $errorsCount) {
                $this->getSession()->addError(
                    Mage::helper('M2ePro')->__(
                        'Products were not Moved. <a target="_blank" href="%url%">View Log</a> for details.',
                        $logViewUrl
                    )
                );

                return $this->getResponse()->setBody(
                    Mage::helper('M2ePro')->jsonEncode(
                        array(
                        'result' => false
                        )
                    )
                );
            }

            $this->getSession()->addError(
                Mage::helper('M2ePro')->__(
                    '%errors_count% product(s) were not Moved. Please <a target="_blank" href="%url%">view Log</a>
                 for the details.',
                    $errorsCount, $logViewUrl
                )
            );
        } else {
            $this->getSession()->addSuccess(Mage::helper('M2ePro')->__('Product(s) was successfully Moved.'));
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'result' => true
                )
            )
        );
    }

    //########################################
}