<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_Listing_PickupStoreController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Pickup Stores'));

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/DropDown.css')
            ->addJs('mage/adminhtml/rules.js')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addJs('M2ePro/Plugin/ActionColumn.js')
            ->addJs('M2ePro/AttributeHandler.js')
            ->addJs('M2ePro/Listing/ProductGridHandler.js')
            ->addJs('M2ePro/ActionHandler.js')
            ->addJs('M2ePro/Listing/ActionHandler.js')
            ->addJs('M2ePro/Ebay/Listing/ActionHandler.js')
            ->addJs('M2ePro/Listing/MovingHandler.js')
            ->addJs('M2ePro/GridHandler.js')
            ->addJs('M2ePro/Listing/GridHandler.js')
            ->addJs('M2ePro/Listing/EditListingTitle.js')
            ->addJs('M2ePro/Ebay/Listing/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/ViewGridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/BidsHandler.js')
            ->addJs('M2ePro/Ebay/Listing/VariationProductManageHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Ebay/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/PickupStore/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/PickupStore/Step/Products/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/PickupStore/Step/Stores/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/VariationProductManageVariationsGridHandler.js');

        $this->_initPopUp();
        $this->setPageHelpLink();

        return $this;
    }

    protected function initListing()
    {
        $id = $this->getRequest()->getParam('id');

        try {
            $model = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $id);
        } catch (LogicException $e) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_ebay_listing/index');
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);
    }

    //########################################

    public function indexAction()
    {
        $this->initListing();
        $this->setRuleData('ebay_rule_pickup_store');

        $this->_initAction()->_addContent(
            $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_pickupStore')
        )->renderLayout();
    }

    //########################################

    public function pickupStoreGridAction()
    {
        $this->initListing();
        $this->setRuleData('ebay_rule_pickup_store');

        $response = $this->loadLayout()->getLayout()
                         ->createBlock('M2ePro/adminhtml_ebay_listing_pickupStore_grid')
                         ->toHtml();

        $this->getResponse()->setBody($response);
    }

    //########################################

    public function assignAction()
    {
        $productsIds = $this->getRequest()->getParam('products_ids');
        $storesIds = $this->getRequest()->getParam('stores_ids');

        if (empty($productsIds) || empty($storesIds)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        !is_array($productsIds) && $productsIds = explode(',', $productsIds);
        !is_array($storesIds) && $storesIds = explode(',', $storesIds);

        $messages = array();
        if (empty($productsIds) || empty($storesIds)) {
            $messages[] = array(
                'type' => 'warning',
                'text' => '<p>' . Mage::helper('M2ePro')->__('Stores cannot be assigned'). '</p>'
            );
        } else {
            $listingProductPickupStoreCollection = Mage::getModel('M2ePro/Ebay_Listing_Product_PickupStore')
                ->getCollection()
                ->addFieldToFilter('listing_product_id', array('in' => $productsIds))
                ->addFieldToFilter('account_pickup_store_id', array('in' => $storesIds));

            $existData = array();
            foreach ($listingProductPickupStoreCollection as $existItem) {
                $existData[] = $existItem['listing_product_id'] . '|' . $existItem['account_pickup_store_id'];
            }

            $insertData = array();
            foreach ($productsIds as $productId) {
                foreach ($storesIds as $storeId) {
                    $key = $productId . '|' . $storeId;
                    if (in_array($key, $existData)) {
                        continue;
                    }

                    $insertData[] = array(
                        'listing_product_id'      => $productId,
                        'account_pickup_store_id' => $storeId,
                        'is_process_required'     => 1,
                    );
                }
            }

            if (!empty($insertData)) {
                $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
                $tableEbayListingProductPickupStore = Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('m2epro_ebay_listing_product_pickup_store');

                $connWrite->insertMultiple($tableEbayListingProductPickupStore, $insertData);
            }

            $messages[] = array(
                'type' => 'success',
                'text' => Mage::helper('M2ePro')->__('Stores have been successfully assigned.')
            );
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'messages' => $messages
                )
            )
        );
    }

    public function unassignAction()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        $listingProductPickupStoreIds = $this->getRequest()->getParam('ids');

        if (empty($listingProductPickupStoreIds)) {
            $this->_getSession()->addWarning(Mage::helper('M2ePro')->__('You should provide correct parameters.'));
            return $this->_redirect('*/*/index', array('id' => $listingId));
        }

        if (!is_array($listingProductPickupStoreIds)) {
            $listingProductPickupStoreIds = array($listingProductPickupStoreIds);
        }

        $this->markInventoryForDelete($listingProductPickupStoreIds);

        $tableEbayListingProductPickupStore = Mage::getResourceModel('M2ePro/Ebay_Listing_Product_PickupStore')
                                                    ->getMainTable();
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connWrite->delete(
            $tableEbayListingProductPickupStore,
            '`id` IN ('.implode(',', $listingProductPickupStoreIds).')'
        );

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Stores have been successfully unassigned.'));
        return $this->_redirect('*/*/index', array('id' => $listingId));
    }

    // ---------------------------------------

    protected function markInventoryForDelete(array $listingProductPickupStoreIds)
    {
        if (empty($listingProductPickupStoreIds)) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Resource_Ebay_Listing_Product_PickupStore_Collection $collection */
        $collection = Mage::getModel('M2ePro/Ebay_Listing_Product_PickupStore')->getCollection();
        $collection->addFieldToFilter('main_table.id', array('in' => $listingProductPickupStoreIds));
        $collection->getSelect()->join(
            array('elp' => Mage::getResourceModel('M2ePro/Ebay_Listing_Product')->getMainTable()),
            'elp.listing_product_id=main_table.listing_product_id',
            array('online_sku' => 'online_sku')
        );
        $collection->getSelect()->joinLeft(
            array('lpv' => Mage::getResourceModel('M2ePro/Listing_Product_Variation')->getMainTable()),
            'lpv.listing_product_id=elp.listing_product_id',
            array('id')
        );
        $collection->getSelect()->joinLeft(
            array('elpv' => Mage::getResourceModel('M2ePro/Ebay_Listing_Product_Variation')->getMainTable()),
            'elpv.listing_product_variation_id=lpv.id',
            array('variation_online_sku' => 'online_sku')
        );
        $collection->getSelect()->joinLeft(
            array('meapss' => Mage::getResourceModel('M2ePro/Ebay_Account_PickupStore_State')->getMainTable()),
            'meapss.account_pickup_store_id = main_table.account_pickup_store_id
                 AND (meapss.sku = elp.online_sku OR meapss.sku = elpv.online_sku)',
            array('state_id' => 'id')
        );

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $connRead->query($collection->getSelect()->__toString());

        $idsForDelete = array();
        while ($row = $stmtTemp->fetch(PDO::FETCH_ASSOC)) {
            !empty($row['state_id']) && $idsForDelete[] = $row['state_id'];
        }

        if (empty($idsForDelete)) {
            return false;
        }

        $pickupStoreStateTable = Mage::getResourceModel('M2ePro/Ebay_Account_PickupStore_State')
                                       ->getMainTable();
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connWrite->update(
            $pickupStoreStateTable,
            array('is_deleted' => 1),
            '`id` IN ('.implode(',', $idsForDelete).')'
        );
    }

    //########################################

    public function productsStepAction()
    {
        $this->initListing();
        $this->setRuleData('ebay_rule_pickup_store_step_products');

        $gridBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_pickupStore_step_products_grid');

        $helpBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_pickupStore_step_products_help');

        $wrapper = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_pickupStore_step_products_wrapper'
        );
        $wrapper->setChild('help', $helpBlock);
        $wrapper->setChild('products', $gridBlock);

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'data' => $wrapper->toHtml(),
                'messages' => array(),
                )
            )
        );
    }

    public function productsStepGridAction()
    {
        $this->initListing();
        $this->setRuleData('ebay_rule_pickup_store_step_products');

        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_pickupStore_step_products_grid')
            ->toHtml();

        $this->getResponse()->setBody($response);
    }

    // ---------------------------------------

    public function storesStepAction()
    {
        $this->initListing();
        $this->setRuleData('ebay_rule_pickup_store_step_stores');

        $model = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $gridBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_pickupStore_step_stores_grid');
        $gridBlock->setAccountId($model->getAccountId());
        $gridBlock->setMarketplaceId($model->getMarketplaceId());

        $helpBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_pickupStore_step_stores_help');

        // ---------------------------------------
        $wrapper = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_pickupStore_step_stores_wrapper'
        );
        $wrapper->setChild('help', $helpBlock);
        $wrapper->setChild('stores', $gridBlock);
        // ---------------------------------------

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'data' => $wrapper->toHtml(),
                'messages' => array(),
                )
            )
        );
    }

    public function storesStepGridAction()
    {
        $this->initListing();
        $this->setRuleData('ebay_rule_pickup_store_step_stores');

        $model = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $gridBlock = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_pickupStore_step_stores_grid');
        $gridBlock->setAccountId($model->getAccountId());
        $gridBlock->setMarketplaceId($model->getMarketplaceId());

        $this->getResponse()->setBody($gridBlock->toHtml());
    }

    //########################################

    public function logGridAction()
    {
        $listingProductPickupStoreState = (int)$this->getRequest()
                                                    ->getParam('listing_product_pickup_store_state');

        if (empty($listingProductPickupStoreState)) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                    'messages' => array(Mage::helper('M2ePro')->__('You should provide correct parameters.')),
                    )
                )
            );
        }

        $logGrid = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_account_pickupStore_log_grid');
        $logGrid->setListingProductPickupStoreStateId($listingProductPickupStoreState);

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'data' => $logGrid->toHtml(),
                'messages' => array()
                )
            )
        );
    }

    public function logGridAjaxAction()
    {
        $listingProductPickupStoreState = (int)$this->getRequest()
            ->getParam('listing_product_pickup_store_state', 0);

        $logGrid = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_account_pickupStore_log_grid');
        $logGrid->setListingProductPickupStoreStateId($listingProductPickupStoreState);

        return $this->getResponse()->setBody($logGrid->toHtml());
    }

    //########################################

    protected function setRuleData($prefix)
    {
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $storeId = isset($listingData['store_id']) ? (int)$listingData['store_id'] : 0;
        $prefix .= isset($listingData['id']) ? '_'.$listingData['id'] : '';
        Mage::helper('M2ePro/Data_Global')->setValue('rule_prefix', $prefix);

        $ruleModel = Mage::getModel('M2ePro/Ebay_Magento_Product_Rule')->setData(
            array(
                'prefix' => $prefix,
                'store_id' => $storeId,
            )
        );

        $ruleParam = $this->getRequest()->getPost('rule');
        if (!empty($ruleParam)) {
            Mage::helper('M2ePro/Data_Session')->setValue(
                $prefix, $ruleModel->getSerializedFromPost($this->getRequest()->getPost())
            );
        } elseif ($ruleParam !== null) {
            Mage::helper('M2ePro/Data_Session')->setValue($prefix, array());
        }

        $sessionRuleData = Mage::helper('M2ePro/Data_Session')->getValue($prefix);
        if (!empty($sessionRuleData)) {
            $ruleModel->loadFromSerialized($sessionRuleData);
        }

        Mage::helper('M2ePro/Data_Global')->setValue('rule_model', $ruleModel);
    }

    //########################################
}
