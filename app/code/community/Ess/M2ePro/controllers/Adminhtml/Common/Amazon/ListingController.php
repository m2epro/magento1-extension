<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Common_Amazon_ListingController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
            ->_title(Mage::helper('M2ePro')->__('Amazon Listings'));

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/DropDown.css')
            ->addCss('M2ePro/css/Plugin/AutoComplete.css')
            ->addJs('mage/adminhtml/rules.js')
            ->addJs('M2ePro/Plugin/ActionColumn.js')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Plugin/AutoComplete.js')
            ->addJs('M2ePro/Listing/ProductGridHandler.js')
            ->addJs('M2ePro/Listing/Category/TreeHandler.js')
            ->addJs('M2ePro/Listing/AutoActionHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/AutoActionHandler.js')

            ->addJs('M2ePro/GridHandler.js')
            ->addJs('M2ePro/Listing/GridHandler.js')
            ->addJs('M2ePro/Common/Listing/GridHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/GridHandler.js')

            ->addJs('M2ePro/ActionHandler.js')
            ->addJs('M2ePro/Listing/ActionHandler.js')
            ->addJs('M2ePro/Listing/MovingHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/ActionHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/ProductSearchHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/Template/DescriptionHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/Template/ShippingOverrideHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/VariationProductManageHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/FulfillmentHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/RepricingHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/AfnQtyHandler.js')

            ->addJs('M2ePro/TemplateHandler.js')
            ->addJs('M2ePro/Common/Listing/SettingsHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/ChannelSettingsHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/ProductsFilterHandler.js')

            ->addJs('M2ePro/Common/Listing/Product/VariationHandler.js');

        $this->_initPopUp();

        $this->setPageHelpLink(Ess_M2ePro_Helper_Component_Amazon::NICK, 'Edit+M2E+Pro+Listing+Settings');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/listings');
    }

    //########################################

    public function indexAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('*/adminhtml_common_listing/index');
        }

        /** @var $block Ess_M2ePro_Block_Adminhtml_Common_Listing */
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_listing');
        $block->enableAmazonTab();

        $this->getResponse()->setBody($block->getAmazonTabHtml());
    }

    public function listingGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function searchAction()
    {
        /** @var $block Ess_M2ePro_Block_Adminhtml_Common_Listing */
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_listing_search');

        $this->_initAction()->_addContent($block)->renderLayout();
    }

    public function searchGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_search_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function viewAction()
    {
        if ((bool)$this->getRequest()->getParam('do_list', false)) {
            Mage::helper('M2ePro/Data_Session')->setValue(
                'products_ids_for_list',
                implode(',', Mage::helper('M2ePro/Data_Session')->getValue('added_products_ids'))
            );

            return $this->_redirect('*/*/*', array(
                '_current'  => true,
                'do_list'   => NULL
            ));
        }

        $id = $this->getRequest()->getParam('id');
        /* @var $model Ess_M2ePro_Model_Listing */

        try {
            $model = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Listing',$id);
        } catch (LogicException $e) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_common_listing/index');
        }

        $listingProductsIds = $model->getSetting('additional_data', 'adding_listing_products_ids');

        if (!empty($listingProductsIds)) {
            $this->_redirect('*/adminhtml_common_amazon_listing_productAdd/index', array(
                'id' => $id,
                'not_completed' => 1
            ));
            return;
        }

        // Check listing lock object
        // ---------------------------------------
        if ($model->isLockedObject('products_in_action')) {
            $this->_getSession()->addNotice(
                Mage::helper('M2ePro')->__('Some Amazon request(s) are being processed now.')
            );
        }
        // ---------------------------------------

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model->getData());
        Mage::helper('M2ePro/Data_Global')->setValue('marketplace_id', $model->getMarketplaceId());

        // Set rule model
        // ---------------------------------------
        $this->setRuleData('amazon_rule_listing_view');
        // ---------------------------------------

        $this->_initAction();
        $this->setPageHelpLink(Ess_M2ePro_Helper_Component_Amazon::NICK, 'Manage+M2E+Pro+Listings');

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_view'))
            ->renderLayout();
    }

    public function viewGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Listing',$id);

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model->getData());
        Mage::helper('M2ePro/Data_Global')->setValue('marketplace_id', $model->getMarketplaceId());

        // Set rule model
        // ---------------------------------------
        $this->setRuleData('amazon_rule_listing_view');
        // ---------------------------------------

        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_view')->getGridHtml();
        $this->getResponse()->setBody($response);
    }

    //########################################

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $listing = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Listing',$id);

        if (!$listing->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_common_listing/index');
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $listing->getData());

        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_edit'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            $this->_redirect('*/adminhtml_common_listing/index');
        }

        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Listing')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_common_listing/index');
        }

        $oldData = $model->getDataSnapshot();

        // Base prepare
        // ---------------------------------------
        $data = array();
        // ---------------------------------------

        // tab: settings
        // ---------------------------------------
        $keys = array(
            'template_selling_format_id',
            'template_synchronization_id',
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }
        // ---------------------------------------

        $model->addData($data)->save();

        $templateData = array();

        // tab: channel settings
        // ---------------------------------------
        $keys = array(
            'account_id',
            'marketplace_id',

            'sku_mode',
            'sku_custom_attribute',
            'sku_modification_mode',
            'sku_modification_custom_value',
            'generate_sku_mode',

            'general_id_mode',
            'general_id_custom_attribute',
            'worldwide_id_mode',
            'worldwide_id_custom_attribute',

            'search_by_magento_title_mode',

            'condition_mode',
            'condition_value',
            'condition_custom_attribute',

            'condition_note_mode',
            'condition_note_value',

            'image_main_mode',
            'image_main_attribute',

            'gallery_images_mode',
            'gallery_images_limit',
            'gallery_images_attribute',

            'gift_wrap_mode',
            'gift_wrap_attribute',

            'gift_message_mode',
            'gift_message_attribute',

            'handling_time_mode',
            'handling_time_value',
            'handling_time_custom_attribute',

            'restock_date_mode',
            'restock_date_value',
            'restock_date_custom_attribute'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $templateData[$key] = $post[$key];
            }
        }

        if ($templateData['restock_date_value'] === '') {
            $templateData['restock_date_value'] = Mage::helper('M2ePro')->getCurrentGmtDate();
        } else {
            $templateData['restock_date_value'] = Mage::helper('M2ePro')
                                                    ->timezoneDateToGmt($templateData['restock_date_value']);
        }
        // ---------------------------------------

        $model->addData($templateData)->save();
        $newData = $model->getDataSnapshot();

        $model->getChildObject()->setSynchStatusNeed($newData,$oldData);

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('The Listing was successfully saved.'));

        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list',array(),array('edit'=>array('id'=>$id))));
    }

    public function deleteAction()
    {
        $ids = $this->getRequestIds();

        if (count($ids) == 0) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select Item(s) to remove.'));
            $this->_redirect('*/*/index');
            return;
        }

        $deleted = $locked = 0;
        foreach ($ids as $id) {
            $listing = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Listing',$id);
            if ($listing->isLocked()) {
                $locked++;
            } else {
                $listing->deleteInstance();
                $deleted++;
            }
        }

        $tempString = Mage::helper('M2ePro')->__('%amount% Listing(s) were successfully deleted', $deleted);
        $deleted && $this->_getSession()->addSuccess($tempString);

        $tempString = Mage::helper('M2ePro')->__(
            '%amount% Listing(s) have Listed Items and can not be deleted', $locked
        );
        $locked && $this->_getSession()->addError($tempString);

        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl());
    }

    //########################################

    protected function processConnector($action, array $params = array())
    {
        if (!$listingsProductsIds = $this->getRequest()->getParam('selected_products')) {
            return 'You should select Products';
        }

        $params['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER;

        $listingsProductsIds = explode(',', $listingsProductsIds);

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Product_Dispatcher');
        $result = (int)$dispatcherObject->process($action, $listingsProductsIds, $params);
        $actionId = (int)$dispatcherObject->getLogsActionId();

        $listingProductObject = Mage::helper('M2ePro/Component_Amazon')
            ->getModel('Listing_Product')
            ->load($listingsProductsIds[0]);

        $isProcessingItems = false;
        if (!is_null($listingProductObject->getId())) {
            $isProcessingItems = (bool)$listingProductObject->getListing()
                ->isLockedObject('products_in_action');
        }

        if ($result == Ess_M2ePro_Helper_Data::STATUS_ERROR) {
            return json_encode(
                array('result'=>'error','action_id'=>$actionId,'is_processing_items'=>$isProcessingItems)
            );
        }

        if ($result == Ess_M2ePro_Helper_Data::STATUS_WARNING) {
            return json_encode(
                array('result'=>'warning','action_id'=>$actionId,'is_processing_items'=>$isProcessingItems)
            );
        }

        if ($result == Ess_M2ePro_Helper_Data::STATUS_SUCCESS) {
            return json_encode(
                array('result'=>'success','action_id'=>$actionId,'is_processing_items'=>$isProcessingItems)
            );
        }

        return json_encode(
            array('result'=>'error','action_id'=>$actionId,'is_processing_items'=>$isProcessingItems)
        );
    }

    // ---------------------------------------

    public function runListProductsAction()
    {
        return $this->getResponse()->setBody(
            $this->processConnector(Ess_M2ePro_Model_Listing_Product::ACTION_LIST)
        );
    }

    public function runReviseProductsAction()
    {
        return $this->getResponse()->setBody(
            $this->processConnector(Ess_M2ePro_Model_Listing_Product::ACTION_REVISE)
        );
    }

    public function runRelistProductsAction()
    {
        return $this->getResponse()->setBody(
            $this->processConnector(Ess_M2ePro_Model_Listing_Product::ACTION_RELIST)
        );
    }

    public function runStopProductsAction()
    {
        return $this->getResponse()->setBody(
            $this->processConnector(Ess_M2ePro_Model_Listing_Product::ACTION_STOP)
        );
    }

    public function runStopAndRemoveProductsAction()
    {
        return $this->getResponse()->setBody($this->processConnector(
            Ess_M2ePro_Model_Listing_Product::ACTION_STOP, array('remove' => true)
        ));
    }

    public function runDeleteAndRemoveProductsAction()
    {
        return $this->getResponse()->setBody($this->processConnector(
            Ess_M2ePro_Model_Listing_Product::ACTION_DELETE, array('remove' => true)
        ));
    }

    //########################################

    public function switchToAFNAction()
    {
        $productsIds = $this->getRequest()->getParam('products_ids');

        if (empty($productsIds)) {
            return $this->getResponse()->setBody('ERROR: Empty Product ID!');
        }

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $listingProducts = array();
        foreach ($productsIds as $listingProductId) {

            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $listingProductId);

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator $configurator */
            $configurator = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Configurator');
            $configurator->setPartialMode();
            $configurator->allowQty();

            $listingProduct->setActionConfigurator($configurator);
            $listingProducts[] = $listingProduct;
        }

        $params['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER;
        $params['switch_to'] = Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Qty::FULFILLMENT_MODE_AFN;
        $action = Ess_M2ePro_Model_Listing_Product::ACTION_REVISE;

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Product_Dispatcher');
        $result = (int)$dispatcherObject->process($action, $listingProducts, $params);

        return $this->getResponse()->setBody(json_encode(array(
            'messages' => array($this->getSwitchFulfillmentResultMessage($result))
        )));
    }

    public function switchToMFNAction()
    {
        $productsIds = $this->getRequest()->getParam('products_ids');

        if (empty($productsIds)) {
            return $this->getResponse()->setBody('ERROR: Empty Product ID!');
        }

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $listingProducts = array();
        foreach ($productsIds as $listingProductId) {

            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $listingProductId);

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator $configurator */
            $configurator = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Configurator');
            $configurator->setPartialMode();
            $configurator->allowQty();

            $listingProduct->setActionConfigurator($configurator);
            $listingProducts[] = $listingProduct;
        }

        $params['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER;
        $params['switch_to'] = Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Qty::FULFILLMENT_MODE_MFN;
        $action = Ess_M2ePro_Model_Listing_Product::ACTION_REVISE;

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Product_Dispatcher');
        $result = (int)$dispatcherObject->process($action, $listingProducts, $params);

        return $this->getResponse()->setBody(json_encode(array(
            'messages' => array($this->getSwitchFulfillmentResultMessage($result))
        )));
    }

    protected function getSwitchFulfillmentResultMessage($result)
    {
        $messageType = '';
        $messageText = '';

        if ($result == Ess_M2ePro_Helper_Data::STATUS_ERROR) {
            $messageType = 'error';
            $messageText = Mage::helper('M2ePro')->__('
                Fulfillment was not switched. Please check Listing Log for more details.');
        }

        if ($result == Ess_M2ePro_Helper_Data::STATUS_WARNING) {
            $messageType = 'warning';
            $messageText = Mage::helper('M2ePro')->__('
                Fulfillment switching is in progress now but there are some warnings. Please check Listing Log
                for more details.');
        }

        if ($result == Ess_M2ePro_Helper_Data::STATUS_SUCCESS) {
            $messageType = 'success';
            $messageText = Mage::helper('M2ePro')->__('Fulfillment switching is in progress now. Please wait.');
        }

        return array(
            'type' => $messageType,
            'text' => $messageText
        );
    }

    // ---------------------------------------

    public function getAFNQtyBySkuAction()
    {
        $accountId = $this->getRequest()->getParam('account_id');
        $skus = $this->getRequest()->getParam('skus');

        if (empty($skus) || empty($accountId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if (!is_array($skus)) {
            $skus = explode(',', $skus);
        }

        /** @var $dispatcherObject Ess_M2ePro_Model_Connector_Amazon_Dispatcher */
        $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector('inventory','get','qtyAfnItems',
            array(
                'items' => $skus,
                'only_realtime' => true
            ),
            null,
            $accountId
        );

        $data = $dispatcherObject->process($connectorObj);

        return $this->getResponse()->setBody(json_encode($data));
    }

    //########################################

    public function getSearchAsinMenuAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('ERROR: No Product ID!');
        }

        $productSearchMenuBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_productSearch_menu');
        $productSearchMenuBlock->setListingProductId($productId);

        $this->getResponse()->setBody($productSearchMenuBlock->toHtml());
    }

    public function getSuggestedAsinGridAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('ERROR: No Product ID!');
        }

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product',$productId);

        $marketplaceId = $listingProduct->getListing()->getMarketplaceId();

        $searchSettingsData = $listingProduct->getSettings('search_settings_data');
        if (!empty($searchSettingsData['data'])) {
            Mage::helper('M2ePro/Data_Global')->setValue('product_id',$productId);
            Mage::helper('M2ePro/Data_Global')->setValue('is_suggestion',true);
            Mage::helper('M2ePro/Data_Global')->setValue('marketplace_id',$marketplaceId);
            Mage::helper('M2ePro/Data_Global')->setValue('temp_data',$searchSettingsData);

            $response = $this->loadLayout()->getLayout()
                ->createBlock('M2ePro/adminhtml_common_amazon_listing_productSearch_grid')->toHtml();
        } else {
            $response = Mage::helper('M2ePro')->__('NO DATA');
        }

        $this->getResponse()->setBody($response);
    }

    // ---------------------------------------

    public function searchAsinManualAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $query = trim($this->getRequest()->getParam('query'));

        if (empty($productId)) {
            return $this->getResponse()->setBody('No product_id!');
        }

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product',$productId);

        if ($listingProduct->isNotListed() &&
            !$listingProduct->getData('is_general_id_owner') &&
            !$listingProduct->getData('general_id')
        ) {

            $marketplaceObj = $listingProduct->getListing()->getMarketplace();

            /** @var $dispatcher Ess_M2ePro_Model_Amazon_Search_Dispatcher */
            $dispatcher = Mage::getModel('M2ePro/Amazon_Search_Dispatcher');
            $result = $dispatcher->runCustom($listingProduct,$query);

            $message = Mage::helper('M2ePro')->__('Server is currently unavailable. Please try again later.');
            if ($result === false || $result['data'] === false) {
                $response = array('result' => 'error','data' => $message);
                return $this->getResponse()->setBody(json_encode($response));
            }

            Mage::helper('M2ePro/Data_Global')->setValue('temp_data',$result);
            Mage::helper('M2ePro/Data_Global')->setValue('product_id',$productId);
            Mage::helper('M2ePro/Data_Global')->setValue('marketplace_id',$marketplaceObj->getId());
        } else {
            Mage::helper('M2ePro/Data_Global')->setValue('temp_data',array());
        }

        $data = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_productSearch_grid')->toHtml();

        $response = array(
            'result' => 'success',
            'data'   => $data
        );

        return $this->getResponse()->setBody(json_encode($response));
    }

    public function searchAsinAutoAction()
    {
        $productsIds = $this->getRequest()->getParam('products_ids');

        if (empty($productsIds)) {
            return $this->getResponse()->setBody('You should select one or more Products');
        }

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $productsToSearch = array();
        foreach ($productsIds as $productId) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product',$productId);

            $searchStatusInProgress = Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_IN_PROGRESS;
            if ($listingProduct->isNotListed() &&
                !$listingProduct->getData('general_id') &&
                !$listingProduct->getData('is_general_id_owner') &&
                $listingProduct->getData('search_settings_status') != $searchStatusInProgress
            ) {
                $productsToSearch[] = $listingProduct;
            }
        }

        if (!empty($productsToSearch)) {
            /** @var $dispatcher Ess_M2ePro_Model_Amazon_Search_Dispatcher */
            $dispatcher = Mage::getModel('M2ePro/Amazon_Search_Dispatcher');
            $result = $dispatcher->runSettings($productsToSearch);

            if ($result === false) {
                return $this->getResponse()->setBody('1');
            }
        }

        return $this->getResponse()->setBody('0');
    }

    // ---------------------------------------

    public function getCategoriesByAsinAction()
    {
        $asin = $this->getRequest()->getParam('asin');
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($asin)) {
            return $this->getResponse()->setBody('You should select one or more Products');
        }

        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);

        /** @var $dispatcherObject Ess_M2ePro_Model_Connector_Amazon_Dispatcher */
        $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector('product','search','categoriesByAsin',
                                                               array('item' => $asin,
                                                                     'only_realtime' => true),
                                                               null,
                                                               $listingProduct->getAccount()->getId());

        $categoriesData = $dispatcherObject->process($connectorObj);

        return $this->getResponse()->setBody(json_encode(array(
            'data' => empty($categoriesData['categories']) ? '' : $categoriesData['categories']
        )));
    }

    // ---------------------------------------

    public function getProductsSearchStatusAction()
    {
        $productsIds = $this->getRequest()->getParam('products_ids');

        if (empty($productsIds)) {
            return $this->getResponse()->setBody('You should select one or more Products');
        }

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $messages = array();

        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $tableListingProduct = Mage::getSingleton('core/resource')->getTableName('m2epro_listing_product');
        $tableAmazonListingProduct = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_listing_product');

        $selectWarnings = $connRead->select();
        $selectWarnings->from(array('lp' => $tableListingProduct), array('id'))
            ->join(
                array('alp' => $tableAmazonListingProduct),
                'lp.id = alp.listing_product_id',
                array()
            )
            ->where('lp.id IN (?)', $productsIds)
            ->where('lp.status = ?',(int)Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED)
            ->where('alp.general_id IS NULL')
            ->where('alp.is_general_id_owner = 0');

        $selectError = clone $selectWarnings;

        $searchStatusActionRequired = Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_ACTION_REQUIRED;
        $searchStatusInProgress = Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_IN_PROGRESS;
        $selectWarnings->where(
            'alp.search_settings_status = ' . $searchStatusActionRequired .
            ' OR alp.search_settings_status = ' . $searchStatusInProgress
        );

        $data = Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($selectWarnings);

        if (count($data) > 0) {
            $messages[] = array(
                'type' => 'warning',
                'text' => Mage::helper('M2ePro')->__(
                    'For %count% Items it is necessary to choose manually one of the found Amazon Products
                     or these Items are in process of Search and results for them will be available later.',
                    count($data)
                )
            );
        }

        $searchStatusNotFound = Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_NOT_FOUND;
        $selectError->where(
            'alp.search_settings_status = ' . $searchStatusNotFound
        );

        $data = Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($selectError);

        if (count($data) > 0) {
            $messages[] = array(
                'type' => 'error',
                'text' => Mage::helper('M2ePro')->__(
                    'For %count% Items no Amazon Products were found. Please use Manual Search
                     or create New ASIN/ISBN.',
                    count($data)
                )
            );
        }

        if (empty($messages)) {
            $messages[] = array(
                'type' => 'success',
                'text' => Mage::helper('M2ePro')->__(
                    'ASIN(s)/ISBN(s) were found and assigned for %count% selected Items.',
                    count($data)
                )
            );
        }

        return $this->getResponse()->setBody(json_encode(array(
            'messages' => $messages
        )));
    }

    // ---------------------------------------

    public function mapToAsinAction()
    {
        $productId   = $this->getRequest()->getParam('product_id');
        $generalId   = $this->getRequest()->getParam('general_id');
        $optionsData = $this->getRequest()->getParam('options_data');
        $searchType  = $this->getRequest()->getParam('search_type');
        $searchValue = $this->getRequest()->getParam('search_value');

        if (empty($productId) || empty($generalId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if (!Mage::helper('M2ePro/Component_Amazon')->isASIN($generalId) &&
            !Mage::helper('M2ePro')->isISBN($generalId)
        ) {
            return $this->getResponse()->setBody('General ID has invalid format.');
        }

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();

        $variationManager = $amazonListingProduct->getVariationManager();

        if ($variationManager->isRelationParentType() && empty($optionsData)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if (!$listingProduct->isNotListed() || $amazonListingProduct->isGeneralIdOwner()) {
            return $this->getResponse()->setBody('0');
        }

        $searchStatusInProgress = Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_IN_PROGRESS;

        if ($listingProduct->getData('search_settings_status') == $searchStatusInProgress) {
            return $this->getResponse()->setBody('0');
        }

        if (!empty($searchType) && !empty($searchValue)) {
            $generalIdSearchInfo = array(
                'is_set_automatic' => false,
                'type'  => $searchType,
                'value' => $searchValue,
            );

            $listingProduct->setSettings('general_id_search_info', $generalIdSearchInfo);
        }

        $listingProduct->setData('general_id',$generalId);
        $listingProduct->setData('search_settings_status',NULL);
        $listingProduct->setData('search_settings_data',NULL);

        $listingProduct->save();

        if (empty($optionsData)) {
            return $this->getResponse()->setBody('0');
        }

        $optionsData = json_decode($optionsData, true);

        if ($variationManager->isRelationParentType()) {
            if (empty($optionsData['virtual_matched_attributes'])) {
                $matchedAttributes = $optionsData['matched_attributes'];
            } else {
                $attributesData = $optionsData['virtual_matched_attributes'];

                $matchedAttributes = array();
                $virtualMagentoAttributes = array();
                $virtualAmazonAttributes = array();

                foreach ($attributesData as $key => $value) {
                    if (strpos($key, 'virtual_magento_attributes_') !== false) {
                        $amazonAttrKey = 'virtual_magento_option_' . str_replace('virtual_magento_attributes_','',$key);
                        $virtualMagentoAttributes[$value] = $attributesData[$amazonAttrKey];

                        unset($attributesData[$key]);
                        unset($attributesData[$amazonAttrKey]);
                        continue;
                    }

                    if (strpos($key, 'virtual_amazon_attributes_') !== false) {
                        $amazonAttrKey = 'virtual_amazon_option_' . str_replace('virtual_amazon_attributes_','',$key);
                        $virtualAmazonAttributes[$value] = $attributesData[$amazonAttrKey];

                        unset($attributesData[$key]);
                        unset($attributesData[$amazonAttrKey]);
                        continue;
                    }

                    if (strpos($key, 'magento_attributes_') !== false) {
                        $amazonAttrKey = 'amazon_attributes_' . str_replace('magento_attributes_','',$key);
                        $matchedAttributes[$value] = $attributesData[$amazonAttrKey];

                        unset($attributesData[$key]);
                        unset($attributesData[$amazonAttrKey]);
                        continue;
                    }
                }
            }

            $channelVariationsSet = array();
            foreach ($optionsData['variations']['set'] as $attribute => $options) {
                $channelVariationsSet[$attribute] = array_values($options);
            }

            $parentTypeModel = $variationManager->getTypeModel();

            if (!empty($virtualMagentoAttributes)) {
                $parentTypeModel->setVirtualProductAttributes($virtualMagentoAttributes);
            } else if (!empty($virtualAmazonAttributes)) {
                $parentTypeModel->setVirtualChannelAttributes($virtualAmazonAttributes);
            }

            $parentTypeModel->setMatchedAttributes($matchedAttributes, false);
            $parentTypeModel->setChannelAttributesSets($channelVariationsSet, false);

            $channelVariations = array();
            foreach ($optionsData['variations']['asins'] as $asin => $asinAttributes) {
                $channelVariations[$asin] = $asinAttributes['specifics'];
            }
            $parentTypeModel->setChannelVariations($channelVariations, false);

            $parentTypeModel->getProcessor()->process();

            if ($listingProduct->getMagentoProduct()->isGroupedType()) {
                return $this->getResponse()->setBody('0');
            }

            $vocabularyHelper = Mage::helper('M2ePro/Component_Amazon_Vocabulary');

            if ($vocabularyHelper->isAttributeAutoActionDisabled()) {
                return $this->getResponse()->setBody('0');
            }

            $attributesForAddingToVocabulary = array();

            foreach ($matchedAttributes as $productAttribute => $channelAttribute) {
                if ($productAttribute == $channelAttribute) {
                    continue;
                }

                if ($vocabularyHelper->isAttributeExistsInLocalStorage($productAttribute, $channelAttribute)) {
                    continue;
                }

                if ($vocabularyHelper->isAttributeExistsInServerStorage($productAttribute, $channelAttribute)) {
                    continue;
                }

                $attributesForAddingToVocabulary[$productAttribute] = $channelAttribute;
            }

            if ($vocabularyHelper->isAttributeAutoActionNotSet()) {
                $result = array('result' => '0');

                if (!empty($attributesForAddingToVocabulary)) {
                    $result['vocabulary_attributes'] = $attributesForAddingToVocabulary;
                }

                return $this->getResponse()->setBody(json_encode($result));
            }

            foreach ($attributesForAddingToVocabulary as $productAttribute => $channelAttribute) {
                $vocabularyHelper->addAttribute($productAttribute, $channelAttribute);
            }

            return $this->getResponse()->setBody('0');
        }

        if (!$variationManager->isIndividualType()) {
            return $this->getResponse()->setBody('0');
        }

        $individualTypeModel = $variationManager->getTypeModel();

        if (!$individualTypeModel->isVariationProductMatched()) {
            return $this->getResponse()->setBody('0');
        }

        $channelVariations = array();
        foreach ($optionsData as $asin => $asinAttributes) {
            $channelVariations[$asin] = $asinAttributes['specifics'];
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Attribute $attributesMatcher */
        $attributesMatcher = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Matcher_Attribute');
        $attributesMatcher->setMagentoProduct($listingProduct->getMagentoProduct());
        $attributesMatcher->setDestinationAttributes(array_keys($channelVariations[$generalId]));

        if (!$attributesMatcher->isAmountEqual() || !$attributesMatcher->isFullyMatched()) {
            return $this->getResponse()->setBody('0');
        }

        $matchedAttributes = $attributesMatcher->getMatchedAttributes();

        $productOptions = $variationManager->getTypeModel()->getProductOptions();
        $channelOptions = $channelVariations[$generalId];

        $vocabularyHelper = Mage::helper('M2ePro/Component_Amazon_Vocabulary');

        if ($vocabularyHelper->isOptionAutoActionDisabled()) {
            return $this->getResponse()->setBody('0');
        }

        $optionsForAddingToVocabulary = array();

        foreach ($matchedAttributes as $productAttribute => $channelAttribute) {
            $productOption = $productOptions[$productAttribute];
            $channelOption = $channelOptions[$channelAttribute];

            if ($productOption == $channelOption) {
                continue;
            }

            if ($vocabularyHelper->isOptionExistsInLocalStorage($productOption, $channelOption, $channelAttribute)) {
                continue;
            }

            if ($vocabularyHelper->isOptionExistsInServerStorage($productOption, $channelOption, $channelAttribute)) {
                continue;
            }

            $optionsForAddingToVocabulary[$channelAttribute] = array($productOption => $channelOption);
        }

        if ($vocabularyHelper->isOptionAutoActionNotSet()) {
            $result = array('result' => '0');

            if (!empty($optionsForAddingToVocabulary)) {
                $result['vocabulary_attribute_options'] = $optionsForAddingToVocabulary;
            }

            return $this->getResponse()->setBody(json_encode($result));
        }

        foreach ($optionsForAddingToVocabulary as $channelAttribute => $options) {
            foreach ($options as $productOption => $channelOption) {
                $vocabularyHelper->addOption($productOption, $channelOption, $channelAttribute);
            }
        }

        return $this->getResponse()->setBody('0');
    }

    public function unmapFromAsinAction()
    {
        $productsIds = $this->getRequest()->getParam('products_ids');

        if (empty($productsIds)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $message = Mage::helper('M2ePro')->__('ASIN(s)/ISBN(s) was successfully unassigned.');
        $type = 'success';

        foreach ($productsIds as $productId) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product',$productId);

            if (!$listingProduct->isNotListed() ||
                $listingProduct->isLockedObject('in_action') ||
                ($listingProduct->getChildObject()->getVariationManager()->isVariationParent() &&
                 $listingProduct->isLockedObject('child_products_in_action'))) {
                $type = 'error';
                $message = Mage::helper('M2ePro')->__(
                    'ASIN/ISBN or marker “New ASIN/ISBN” was not unassigned from some Items because those Items
                     have the Status different from “Not Listed” or they are now in the process of Listing.'
                );
                continue;
            }

            $runListingProductProcessor = false;
            if ($listingProduct->getChildObject()->getVariationManager()->isLogicalUnit()) {
                $parentType = $listingProduct->getChildObject()->getVariationManager()->getTypeModel();

                $parentType->setMatchedAttributes(array(), false);
                $parentType->setChannelAttributesSets(array(), false);
                $parentType->setChannelVariations(array(), false);
                $parentType->setVirtualProductAttributes(array(), false);
                $parentType->setVirtualChannelAttributes(array(), false);

                $runListingProductProcessor = true;
            }

            $listingProduct->setData('general_id',NULL);
            $listingProduct->setData('general_id_search_info',NULL);
            $listingProduct->setData(
                'is_general_id_owner',
                Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_NO
            );
            $listingProduct->setData('search_settings_status',NULL);
            $listingProduct->setData('search_settings_data',NULL);

            $listingProduct->save();

            if ($runListingProductProcessor) {
                $parentType->getProcessor()->process();
            }
        }

        return $this->getResponse()->setBody(json_encode(array(
            'type'    => $type,
            'message' => $message
        )));
    }

    public function mapToNewAsinAction()
    {
        $productsIds = $this->getRequest()->getParam('products_ids', '');

        if (empty($productsIds)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $messages = array();

        $badDescriptionProductsIds = array();
        $descriptionTemplatesBlock = '';

        $errorMsg = Mage::helper('M2ePro')->__(
            'The new ASIN/ISBN creation feature was not added to some Items because '
        );
        $errors = array();
        $errorMsgProductsCount = 0;

        $variationHelper = Mage::helper('M2ePro/Component_Amazon_Variation');

        $filteredByGeneralId = $variationHelper->filterProductsByGeneralId($productsIds);

        if (count($productsIds) != count($filteredByGeneralId)) {
            $tempCount = count($productsIds) - count($filteredByGeneralId);
            $errors[] = Mage::helper('M2ePro')->__('%count% Item(s) already have ASIN(s)/ISBN(s).', $tempCount);
            $errorMsgProductsCount += $tempCount;
        }

        $filteredByGeneralIdOwner = $variationHelper->filterProductsByGeneralIdOwner($filteredByGeneralId);

        if (count($filteredByGeneralId) != count($filteredByGeneralIdOwner)) {
            $tempCount = count($filteredByGeneralId) - count($filteredByGeneralIdOwner);
            $errors[] = Mage::helper('M2ePro')->__(
                '%count% Item(s) already have possibility to create ASIN(s)/ISBN(s).', $tempCount
            );
            $errorMsgProductsCount += $tempCount;
        }

        $filteredByStatus = $variationHelper->filterProductsByStatus($filteredByGeneralIdOwner);

        if (count($filteredByGeneralIdOwner) != count($filteredByStatus)) {
            $tempCount = count($filteredByGeneralIdOwner) - count($filteredByStatus);
            $errors[] = Mage::helper('M2ePro')->__(
                '%count% Items have the Status different from “Not Listed”.', $tempCount
            );
            $errorMsgProductsCount += $tempCount;
        }

        $filteredLockedProducts = $variationHelper->filterLockedProducts($filteredByStatus);

        if (count($filteredByStatus) != count($filteredLockedProducts)) {
            $tempCount = count($filteredByStatus) - count($filteredLockedProducts);
            $errors[] = Mage::helper('M2ePro')->__(
                'There are some other actions performed on %count% Items.', $tempCount
            );
            $errorMsgProductsCount += $tempCount;
        }

        $filteredProductsIdsByType = $variationHelper->filterProductsByMagentoProductType($filteredLockedProducts);

        if (count($filteredLockedProducts) != count($filteredProductsIdsByType)) {
            $tempCount = count($filteredLockedProducts) - count($filteredProductsIdsByType);
            $errors[] = Mage::helper('M2ePro')->__(
                '%count% Items are Simple with Custom Options or Bundle Magento Products.', $tempCount
            );
            $errorMsgProductsCount += $tempCount;
        }

        $filteredProductsIdsByTpl = $variationHelper->filterProductsByDescriptionTemplate($filteredProductsIdsByType);

        if (count($filteredProductsIdsByType) != count($filteredProductsIdsByTpl)) {
            $badDescriptionProductsIds = array_diff($filteredProductsIdsByType, $filteredProductsIdsByTpl);

            $tempCount = count($filteredProductsIdsByType) - count($filteredProductsIdsByTpl);
            $errors[] = Mage::helper('M2ePro')->__(
                '%count% Item(s) haven’t got the Description Policy assigned with enabled ability to create
                 new ASIN(s)/ISBN(s).', $tempCount
            );
            $errorMsgProductsCount += $tempCount;
        }

        $filteredProductsIdsByParent = $variationHelper->filterParentProductsByVariationTheme(
            $filteredProductsIdsByTpl
        );

        if (count($filteredProductsIdsByTpl) != count($filteredProductsIdsByParent)) {
            $badThemeProductsIds = array_diff($filteredProductsIdsByTpl, $filteredProductsIdsByParent);
            $badDescriptionProductsIds = array_merge(
                $badDescriptionProductsIds,
                $badThemeProductsIds
            );

            $tempCount = count($filteredProductsIdsByTpl) - count($filteredProductsIdsByParent);
            $errors[] = Mage::helper('M2ePro')->__(
                'The Category chosen in the Description Policies of %count% Items does not support creation of
                 Variational Products at all.',
                $tempCount
            );
            $errorMsgProductsCount += $tempCount;
        }

        if (!empty($errors)) {
            $messages[] = array (
                'type' => 'warning',
                'text' => $errorMsg . implode(', ', $errors) . '. ('. $errorMsgProductsCount . ')'
            );
        }

        if (!empty($filteredProductsIdsByParent)) {
            $this->mapToNewAsinByChunks($filteredProductsIdsByParent);
            $this->runProcessorForParents($filteredProductsIdsByParent);
            array_unshift(
                $messages,
                array(
                    'type' => 'success',
                    'text' => Mage::helper('M2ePro')->__(
                        'New ASIN/ISBN creation feature was successfully added to %count% Products.',
                        count($filteredProductsIdsByParent)
                    )
                )
            );
        }

        if (!empty($badDescriptionProductsIds)) {
            $badDescriptionProductsIds = $variationHelper
                ->filterProductsByMagentoProductType($badDescriptionProductsIds);

            $descriptionTemplatesBlock = $this->loadLayout()->getLayout()
                ->createBlock('M2ePro/adminhtml_common_amazon_listing_template_description_main');
            $descriptionTemplatesBlock->setNewAsin(true);
            $descriptionTemplatesBlock->setMessages($messages);
            $descriptionTemplatesBlock = $descriptionTemplatesBlock->toHtml();
        }

        return $this->getResponse()->setBody(json_encode(array(
            'messages' => $messages,
            'data' => $descriptionTemplatesBlock,
            'products_ids' => implode(',', $badDescriptionProductsIds)
        )));
    }

    //########################################

    public function mapToTemplateDescriptionAction()
    {
        $productsIds = $this->getRequest()->getParam('products_ids');
        $templateId = $this->getRequest()->getParam('template_id');

        if (empty($productsIds) || empty($templateId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $variationHelper = Mage::helper('M2ePro/Component_Amazon_Variation');

        $msgType = 'success';
        $messages = array();

        $productsIdsTemp = $this->filterProductsForMapOrUnmapDescriptionTemplateByChunks($productsIds);

        if (count($productsIdsTemp) != count($productsIds)) {
            $msgType = 'warning';
            $messages[] = Mage::helper('M2ePro')->__(
                'Description Policy cannot be assigned because %count% Item(s) are Ready or in Process
                of New ASIN(s)/ISBN(s) creation.', count($productsIds) - count($productsIdsTemp)
            );
        }

        $filteredProductsIdsByType = $variationHelper->filterProductsByMagentoProductType($productsIdsTemp);

        if (count($productsIdsTemp) != count($filteredProductsIdsByType)) {
            $msgType = 'warning';
            $messages[] = Mage::helper('M2ePro')->__(
                'Description Policy cannot be assigned because %count% Items are Simple
                 with Custom Options or Bundle Magento Products.',
                count($productsIdsTemp) - count($filteredProductsIdsByType));
        }

        if (empty($filteredProductsIdsByType)) {
            return $this->getResponse()->setBody(json_encode(array(
                'type' => $msgType,
                'messages' => $messages
            )));
        }

        $this->setDescriptionTemplateFroProductsByChunks($filteredProductsIdsByType, $templateId);
        $this->runProcessorForParents($filteredProductsIdsByType);

        /** @var Ess_M2ePro_Model_Amazon_Template_Description $template */
        $template = Mage::getModel('M2ePro/Amazon_Template_Description')->load($templateId);
        $template->setSynchStatusNeed($template->getDataSnapshot(),array());

        $messages[] = Mage::helper('M2ePro')->__(
            'Description Policy was successfully assigned to %count% Products',
            count($filteredProductsIdsByType));

        return $this->getResponse()->setBody(json_encode(array(
            'type' => $msgType,
            'messages' => $messages,
            'products_ids' => implode(',', $filteredProductsIdsByType)
        )));
    }

    public function unmapFromTemplateDescriptionAction()
    {
        $productsIds = $this->getRequest()->getParam('products_ids');

        if (empty($productsIds)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $productsIdsTemp = $this->filterProductsForMapOrUnmapDescriptionTemplateByChunks($productsIds);

        $messages = array();

        if (count($productsIdsTemp) == 0) {
            $messages[] = array(
                'type' => 'warning',
                'text' => '<p>' . Mage::helper('M2ePro')->__(
                    'Description Policy cannot be unassigned from some Products because they are
                     participating in the new ASIN(s)/ISBN(s) creation.') . '</p>'
            );
        } else {
            $productsIdsLocked = $this->filterLockedProducts($productsIdsTemp);

            if (count($productsIdsLocked) < count($productsIds)) {
                $messages[] = array(
                    'type' => 'warning',
                    'text' => '<p>' . Mage::helper('M2ePro')->__(
                        'Description Policy cannot be unassigned because the Products are in Action or
                         in the process of new ASIN(s)/ISBN(s) Creation.'). '</p>'
                );
            }
        }

        if (!empty($productsIdsLocked)) {
            $messages[] = array(
                'type' => 'success',
                'text' => Mage::helper('M2ePro')->__('Description Policy was successfully unassigned.')
            );

            $this->setDescriptionTemplateFroProductsByChunks($productsIdsLocked, NULL);
            $this->runProcessorForParents($productsIdsLocked);
        }

        return $this->getResponse()->setBody(json_encode(array(
            'messages' => $messages
        )));
    }

    // ---------------------------------------

    public function viewTemplateDescriptionsGridAction()
    {
        $productsIds = $this->getRequest()->getParam('products_ids');
        $checkNewAsinAccepted = $this->getRequest()->getParam('check_is_new_asin_accepted', 0);

        if (empty($productsIds)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $grid = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_template_description_grid');
        $grid->setCheckNewAsinAccepted($checkNewAsinAccepted);
        $grid->setProductsIds($productsIds);

        return $this->getResponse()->setBody($grid->toHtml());
    }

    // ---------------------------------------

    public function validateProductsForTemplateDescriptionAssignAction()
    {
        $productsIds = $this->getRequest()->getParam('products_ids');

        if (empty($productsIds)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $variationHelper = Mage::helper('M2ePro/Component_Amazon_Variation');

        $messages = array();

        $productsIdsTemp = $this->filterProductsForMapOrUnmapDescriptionTemplateByChunks($productsIds);

        if (count($productsIdsTemp) != count($productsIds)) {
            $messages[] = array(
                'type' => 'warning',
                'text' => Mage::helper('M2ePro')->__(
                    'Description Policy was not assigned because the Products are in the process
                     of new ASIN(s)/ISBN(s) creation'
                )
            );
        }

        $productsIdsLocked = $this->filterLockedProducts($productsIdsTemp);

        if (count($productsIdsTemp) != count($productsIdsLocked)) {
            $messages[] = array(
                'type' => 'warning',
                'text' => Mage::helper('M2ePro')->__(
                    'Description Policy cannot be assigned because the Products are in Action.'
                )
            );
        }

        $filteredProductsIdsByType = $variationHelper->filterProductsByMagentoProductType($productsIdsLocked);

        if (count($productsIdsLocked) != count($filteredProductsIdsByType)) {
            $messages[] = array(
                'type' => 'warning',
                'text' => Mage::helper('M2ePro')->__(
                    'Description Policy was not assigned because the Items are Simple
                    With Custom Options or Bundle Magento Products.'
                )
            );
        }

        if (empty($filteredProductsIdsByType)) {
            return $this->getResponse()->setBody(json_encode(array(
                'messages' => $messages
            )));
        }

        $mainBlock = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_template_description_main');
        if (!empty($messages)) {
            $mainBlock->setMessages($messages);
        }

        return $this->getResponse()->setBody(json_encode(array(
            'data' => $mainBlock->toHtml(),
            'messages' => $messages,
            'products_ids' => implode(',', $filteredProductsIdsByType)
        )));
    }

    // ---------------------------------------

    public function getDescriptionTemplatesListAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id', '');
        $isNewAsinAccepted = $this->getRequest()->getParam('is_new_asin_accepted', 0);

        /** @var Ess_M2ePro_Model_Mysql4_Amazon_Template_Description_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Template_Description');

        $marketplaceId != '' && $collection->addFieldToFilter('marketplace_id', $marketplaceId);

        $descriptionTemplates = $collection->getData();
        if ($isNewAsinAccepted == 1) {
            usort($descriptionTemplates, function($a, $b)
            {
                return $a["is_new_asin_accepted"] < $b["is_new_asin_accepted"];
            });
        }

        return $this->getResponse()->setBody(json_encode($descriptionTemplates));
    }

    //########################################

    public function viewTemplateShippingOverridePopupAction()
    {
        $productsIds = $this->getRequest()->getParam('products_ids');

        if (empty($productsIds)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $messages = array();
        $productsIdsLocked = $this->filterLockedProducts($productsIds);

        if (count($productsIdsLocked) < count($productsIds)) {
            $messages[] = array(
                'type' => 'warning',
                'text' => '<p>' . Mage::helper('M2ePro')->__(
                    'The Shipping Override Policy was not assigned because the Products have In Action Status.'
                    ). '</p>'
            );
        }

        if (empty($productsIdsLocked)) {
            return $this->getResponse()->setBody(json_encode(array(
                'messages' => $messages
            )));
        }

        $mainBlock = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_template_shippingOverride');
        if (!empty($messages)) {
            $mainBlock->setMessages($messages);
        }

        return $this->getResponse()->setBody(json_encode(array(
            'data' => $mainBlock->toHtml(),
            'messages' => $messages,
            'products_ids' => implode(',', $productsIdsLocked)
        )));
    }

    public function viewTemplateShippingOverrideGridAction()
    {
        $productsIds = $this->getRequest()->getParam('products_ids');
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');

        if (empty($productsIds) && empty($marketplaceId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if (empty($marketplaceId)) {
            if (!is_array($productsIds)) {
                $productsIds = explode(',', $productsIds);
            }

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productsIds[0]);
            $marketplaceId = $listingProduct->getListing()->getMarketplaceId();
        }

        $grid = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_template_shippingOverride_grid');
        $grid->setMarketplaceId($marketplaceId);

        return $this->getResponse()->setBody($grid->toHtml());
    }

    // ---------------------------------------

    public function assignShippingOverrideTemplateAction()
    {
        $productsIds = $this->getRequest()->getParam('products_ids');
        $templateId = $this->getRequest()->getParam('template_id');

        if (empty($productsIds) || empty($templateId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $messages = array();
        $productsIdsLocked = $this->filterLockedProducts($productsIds);

        if (count($productsIdsLocked) < count($productsIds)) {
            $messages[] = array(
                'type' => 'warning',
                'text' => '<p>' . Mage::helper('M2ePro')->__(
                        'Shipping Override Policy cannot be assigned from some Products
                         because the Products are in Action'). '</p>'
            );
        }

        if (!empty($productsIdsLocked)) {
            $messages[] = array(
                'type' => 'success',
                'text' => Mage::helper('M2ePro')->__('Shipping Override Policy was successfully assigned.')
            );

            $this->setShippingOverrideTemplateForProducts($productsIdsLocked, $templateId);
            $this->runProcessorForParents($productsIdsLocked);

            /** @var Ess_M2ePro_Model_Amazon_Template_ShippingOverride $template */
            $template = Mage::getModel('M2ePro/Amazon_Template_ShippingOverride')->load($templateId);
            $template->setSynchStatusNeed($template->getDataSnapshot(),array());
        }

        return $this->getResponse()->setBody(json_encode(array(
            'messages' => $messages
        )));
    }

    public function unassignShippingOverrideTemplateAction()
    {
        $productsIds = $this->getRequest()->getParam('products_ids');

        if (empty($productsIds)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $messages = array();
        $productsIdsLocked = $this->filterLockedProducts($productsIds);

        if (count($productsIdsLocked) < count($productsIds)) {
            $messages[] = array(
                'type' => 'warning',
                'text' => '<p>' . Mage::helper('M2ePro')->__(
                        'Shipping Override Policy cannot be unassigned from some Products
                         because the Products are in Action'). '</p>'
            );
        }

        if (!empty($productsIdsLocked)) {
            $messages[] = array(
                'type' => 'success',
                'text' => Mage::helper('M2ePro')->__('Shipping Override Policy was successfully unassigned.')
            );

            $this->setShippingOverrideTemplateForProducts($productsIdsLocked, NULL);
            $this->runProcessorForParents($productsIdsLocked);
        }

        return $this->getResponse()->setBody(json_encode(array(
            'messages' => $messages
        )));
    }

    //########################################

    protected function setRuleData($prefix)
    {
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $storeId = isset($listingData['store_id']) ? (int)$listingData['store_id'] : 0;
        $prefix .= isset($listingData['id']) ? '_'.$listingData['id'] : '';
        Mage::helper('M2ePro/Data_Global')->setValue('rule_prefix', $prefix);

        // ---------------------------------------
        $useCustomOptions = true;
        $magentoViewMode = Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_View::VIEW_MODE_MAGENTO;
        $sessionParamName = Mage::getBlockSingleton('M2ePro/Adminhtml_Common_Amazon_Listing_View')->getId()
                            . $listingData['id']
                            . 'view_mode';

        if (($this->getRequest()->getParam('view_mode') == $magentoViewMode) ||
            $magentoViewMode == Mage::helper('M2ePro/Data_Session')->getValue($sessionParamName)) {
            $useCustomOptions = false;
        }
        // ---------------------------------------

        /** @var $ruleModel Ess_M2ePro_Model_Magento_Product_Rule */
        $ruleModel = Mage::getModel('M2ePro/Amazon_Magento_Product_Rule')->setData(
            array(
                'prefix' => $prefix,
                'store_id' => $storeId,
                'use_custom_options' => $useCustomOptions
            )
        );

        $ruleParam = $this->getRequest()->getPost('rule');
        if (!empty($ruleParam)) {
            Mage::helper('M2ePro/Data_Session')->setValue(
                $prefix, $ruleModel->getSerializedFromPost($this->getRequest()->getPost())
            );
        } elseif (!is_null($ruleParam)) {
            Mage::helper('M2ePro/Data_Session')->setValue($prefix, array());
        }

        $sessionRuleData = Mage::helper('M2ePro/Data_Session')->getValue($prefix);
        if (!empty($sessionRuleData)) {
            $ruleModel->loadFromSerialized($sessionRuleData);
        }

        Mage::helper('M2ePro/Data_Global')->setValue('rule_model', $ruleModel);
    }

    protected function getHideProductsInOtherListingsPrefix()
    {
        $id = $this->getRequest()->getParam('id');

        $prefix = 'amazon_hide_products_others_listings_';
        $prefix .= is_null($id) ? 'add' : $id;
        $prefix .= '_listing_product';

        return $prefix;
    }

    // ---------------------------------------

    /**
     * @param $productsIdsParam
     * @param bool $checkChildren - include parents children in result if true
     * @return array
     */
    protected function filterProductsForMapOrUnmapDescriptionTemplateByChunks($productsIdsParam)
    {
        if (count($productsIdsParam) > 1000) {
            $productsIds = array();
            $productsIdsParam = array_chunk($productsIdsParam, 1000);
            foreach ($productsIdsParam as $productsIdsParamChunk) {
                $productsIds = array_merge($productsIds,
                    $this->filterProductsForMapOrUnmapDescriptionTemplate($productsIdsParamChunk));
            }
        } else {
            $productsIds = $this->filterProductsForMapOrUnmapDescriptionTemplate($productsIdsParam);
        }

        return $productsIds;
    }

    protected function filterProductsForMapOrUnmapDescriptionTemplate($productsIds)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableAmazonListingProduct = Mage::getSingleton('core/resource')
                                        ->getTableName('m2epro_amazon_listing_product');

        $select = $connRead->select();

        // selecting all except parents general_id owners or simple general_id owners without general_id
        $select->from($tableAmazonListingProduct, 'listing_product_id')
            ->where('is_general_id_owner = 0
                OR (is_general_id_owner = 1
                    AND is_variation_parent = 0 AND general_id IS NOT NULL)');

        $select->where('listing_product_id IN (?)', $productsIds);

        $result = Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($select);

        return $result;
    }

    public function filterLockedProducts($productsIdsParam)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('m2epro_locked_object');

        $productsIds = array();
        $productsIdsParam = array_chunk($productsIdsParam, 1000);
        foreach ($productsIdsParam as $productsIdsParamChunk) {

            $select = $connRead->select();
            $select->from(array('lo' => $table), array('object_id'))
                ->where('model_name = "M2ePro/Listing_Product"')
                ->where('object_id IN (?)', $productsIdsParamChunk)
                ->where('tag IS NOT NULL');

            $lockedProducts = Mage::getResourceModel('core/config')->getReadConnection()->fetchCol($select);

            foreach ($lockedProducts as $id) {
                $key = array_search($id, $productsIdsParamChunk);
                if ($key !== false) {
                    unset($productsIdsParamChunk[$key]);
                }
            }

            $productsIds = array_merge($productsIds, $productsIdsParamChunk);
        }

        return $productsIds;
    }

    protected function setDescriptionTemplateFroProductsByChunks($productsIds, $templateId)
    {
        if (count($productsIds) > 1000) {
            $productsIds = array_chunk($productsIds, 1000);
            foreach ($productsIds as $productsIdsChunk) {
                $this->setDescriptionTemplateForProducts($productsIdsChunk, $templateId);
            }
        } else {
            $this->setDescriptionTemplateForProducts($productsIds, $templateId);
        }
    }

    protected function setDescriptionTemplateForProducts($productsIds, $templateId)
    {
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableAmazonListingProduct = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_listing_product');

        return $connWrite->update($tableAmazonListingProduct, array(
                'template_description_id' => $templateId
            ), '`listing_product_id` IN ('.implode(',', $productsIds).')'
        );
    }

    //########################################

    protected function setShippingOverrideTemplateForProducts($productsIds, $templateId)
    {
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableAmazonListingProduct = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_listing_product');

        return $connWrite->update($tableAmazonListingProduct, array(
                'template_shipping_override_id' => $templateId
            ), '`listing_product_id` IN ('.implode(',', $productsIds).')'
        );
    }

    //########################################

    protected function mapToNewAsinByChunks($productsIds)
    {
        if (count($productsIds) > 1000) {
            $productsIds = array_chunk($productsIds, 1000);
            foreach ($productsIds as $productsIdsChunk) {
                $this->mapToNewAsin($productsIdsChunk);
            }
        } else {
            $this->mapToNewAsin($productsIds);
        }
    }

    protected function mapToNewAsin($productsIds)
    {
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableAmazonListingProduct = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_listing_product');

        $connWrite->update($tableAmazonListingProduct, array(
                'is_general_id_owner' => Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES
            ), '`listing_product_id` IN ('.implode(',', $productsIds).')'
        );
    }

    //########################################

    protected function runProcessorForParents($productsIds)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableAmazonListingProduct = Mage::getSingleton('core/resource')
            ->getTableName('m2epro_amazon_listing_product');

        $select = $connRead->select();
        $select->from(array('alp' => $tableAmazonListingProduct), array('listing_product_id'))
            ->where('listing_product_id IN (?)', $productsIds)
            ->where('is_variation_parent = ?', 1);

        $productsIds = Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($select);

        foreach ($productsIds as $productId) {
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);
            $listingProduct->getChildObject()->getVariationManager()->getTypeModel()->getProcessor()->process();
        }
    }

    //########################################
}