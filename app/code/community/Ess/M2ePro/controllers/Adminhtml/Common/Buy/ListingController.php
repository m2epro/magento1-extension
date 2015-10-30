<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Common_Buy_ListingController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
            ->_title(Mage::helper('M2ePro')->__('Rakuten.com Listings'));

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/DropDown.css')
            ->addCss('M2ePro/css/Plugin/AutoComplete.css')
            ->addJs('mage/adminhtml/rules.js')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Plugin/AutoComplete.js')
            ->addJs('M2ePro/Listing/ProductGridHandler.js')
            ->addJs('M2ePro/Listing/Category/TreeHandler.js')
            ->addJs('M2ePro/Listing/AutoActionHandler.js')

            ->addJs('M2ePro/GridHandler.js')
            ->addJs('M2ePro/Listing/GridHandler.js')
            ->addJs('M2ePro/Common/Listing/GridHandler.js')
            ->addJs('M2ePro/Common/Buy/Listing/GridHandler.js')

            ->addJs('M2ePro/ActionHandler.js')
            ->addJs('M2ePro/Listing/ActionHandler.js')
            ->addJs('M2ePro/Listing/MovingHandler.js')
            ->addJs('M2ePro/Common/Buy/Listing/ProductSearchHandler.js')

            ->addJs('M2ePro/TemplateHandler.js')
            ->addJs('M2ePro/Common/Listing/SettingsHandler.js')
            ->addJs('M2ePro/Common/Buy/Listing/ChannelSettingsHandler.js')
            ->addJs('M2ePro/Common/Buy/Listing/ProductsFilterHandler.js')

            ->addJs('M2ePro/Common/Listing/Product/VariationHandler.js');

        $this->_initPopUp();

        $this->setPageHelpLink(Ess_M2ePro_Helper_Component_Buy::NICK, 'Edit+M2E+Pro+Listing+Settings');

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
        $block->enableBuyTab();

        $this->getResponse()->setBody($block->getBuyTabHtml());
    }

    public function listingGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_buy_listing_grid');
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
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_buy_listing_search_grid');
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

        try {
            $model = Mage::helper('M2ePro/Component_Buy')->getCachedObject('Listing',$id);
        } catch (LogicException $e) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_common_listing/index');
        }

        // Check listing lock object
        // ---------------------------------------
        if ($model->isLockedObject('products_in_action')) {
            $this->_getSession()->addNotice(
                Mage::helper('M2ePro')->__('Some Rakuten.com request(s) are being processed now.')
            );
        }
        // ---------------------------------------

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model->getData());
        Mage::helper('M2ePro/Data_Global')->setValue('marketplace_id', $model->getMarketplaceId());

        // Set rule model
        // ---------------------------------------
        $this->setRuleData('buy_rule_listing_view');
        // ---------------------------------------

        $this->_initAction();
        $this->setPageHelpLink(Ess_M2ePro_Helper_Component_Buy::NICK, 'Manage+M2E+Pro+Listings');

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_listing_view'))
            ->renderLayout();
    }

    public function viewGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Buy')->getCachedObject('Listing',$id);

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model->getData());

        // Set rule model
        // ---------------------------------------
        $this->setRuleData('buy_rule_listing_view');
        // ---------------------------------------

        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_buy_listing_view_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //########################################

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $listing = Mage::helper('M2ePro/Component_Buy')->getCachedObject('Listing',$id);

        if (!$listing->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_common_listing/index');
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $listing->getData());

        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_listing_edit'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            $this->_redirect('*/adminhtml_common_listing/index');
        }

        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Buy')->getModel('Listing')->load($id);

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

            'sku_mode',
            'sku_custom_attribute',
            'sku_modification_mode',
            'sku_modification_custom_value',
            'generate_sku_mode',

            'general_id_mode',
            'general_id_custom_attribute',

            'search_by_magento_title_mode',

            'condition_mode',
            'condition_value',
            'condition_custom_attribute',

            'condition_note_mode',
            'condition_note_value',

            'shipping_standard_mode',
            'shipping_standard_value',
            'shipping_standard_custom_attribute',

            'shipping_expedited_mode',
            'shipping_expedited_value',
            'shipping_expedited_custom_attribute',

            'shipping_one_day_mode',
            'shipping_one_day_value',
            'shipping_one_day_custom_attribute',

            'shipping_two_day_mode',
            'shipping_two_day_value',
            'shipping_two_day_custom_attribute'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $templateData[$key] = $post[$key];
            }
        }

        $templateData['marketplace_id'] = Mage::helper('M2ePro/Component_Buy')->getMarketplaceId();
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
            $listing = Mage::helper('M2ePro/Component_Buy')->getCachedObject('Listing',$id);
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

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Product_Dispatcher');
        $result = (int)$dispatcherObject->process($action, $listingsProductsIds, $params);
        $actionId = (int)$dispatcherObject->getLogsActionId();

        $listingProductObject = Mage::helper('M2ePro/Component_Buy')
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

    //########################################

    public function getSuggestedBuyComSkuGridAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('ERROR: No Product id!');
        }

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component_Buy')->getObject('Listing_Product',$productId);
        $marketplaceId = Mage::helper('M2ePro/Component_Buy')->getMarketplaceId();

        $searchSettingsData = @json_decode($listingProduct->getData('search_settings_data'), true);
        if (!empty($searchSettingsData['data'])) {
            Mage::helper('M2ePro/Data_Global')->setValue('product_id',$productId);
            Mage::helper('M2ePro/Data_Global')->setValue('is_suggestion',true);
            Mage::helper('M2ePro/Data_Global')->setValue('marketplace_id',$marketplaceId);
            Mage::helper('M2ePro/Data_Global')->setValue('temp_data',$searchSettingsData);
            $response = $this->loadLayout()->getLayout()
                ->createBlock('M2ePro/adminhtml_common_buy_listing_productSearch_grid')->toHtml();
        } else {
            $response = Mage::helper('M2ePro')->__('NO DATA');
        }

        $this->getResponse()->setBody($response);
    }

    // ---------------------------------------

    public function searchBuyComSkuManualAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $query = $this->getRequest()->getParam('query');

        if (empty($productId)) {
            return $this->getResponse()->setBody('No product_id!');
        }

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component_Buy')->getObject('Listing_Product',$productId);

        $tempCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Product');
        $tempCollection->getSelect()->join(
            array('l'=>Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            '`main_table`.`listing_id` = `l`.`id`',
            null
        );

        if ($listingProduct->isNotListed() &&
            !$listingProduct->isLockedObject('in_action') &&
            !$listingProduct->getData('category_id') && !$listingProduct->getData('general_id')) {

            $marketplaceObj = $listingProduct->getListing()->getMarketplace();

            /** @var $dispatcher Ess_M2ePro_Model_Buy_Search_Dispatcher */
            $dispatcher = Mage::getModel('M2ePro/Buy_Search_Dispatcher');
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
            ->createBlock('M2ePro/adminhtml_common_buy_listing_productSearch_grid')->toHtml();

        $response = array(
            'result' => 'success',
            'data' => $data
        );

        return $this->getResponse()->setBody(json_encode($response));
    }

    public function searchBuyComSkuAutoAction()
    {
        $productIds = $this->getRequest()->getParam('product_ids');

        if (empty($productIds)) {
            return $this->getResponse()->setBody('You should select one or more Products');
        }

        $productIds = explode(',', $productIds);

        $productsToSearch = array();
        foreach ($productIds as $productId) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $listingProduct = Mage::helper('M2ePro/Component_Buy')->getObject('Listing_Product',$productId);

            if ($listingProduct->isNotListed() &&
                !$listingProduct->isLockedObject('in_action') &&
                !$listingProduct->getData('category_id') && !$listingProduct->getData('general_id')) {

                $productsToSearch[] = $listingProduct;
            }
        }

        if (!empty($productsToSearch)) {
            /** @var $dispatcher Ess_M2ePro_Model_Buy_Search_Dispatcher */
            $dispatcher = Mage::getModel('M2ePro/Buy_Search_Dispatcher');
            $result = $dispatcher->runSettings($productsToSearch);

            if ($result === false) {
                return $this->getResponse()->setBody('1');
            }
        }

        return $this->getResponse()->setBody('0');
    }

    // ---------------------------------------

    public function mapToBuyComSkuAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $generalId = $this->getRequest()->getParam('general_id');
        $searchType  = $this->getRequest()->getParam('search_type');
        $searchValue = $this->getRequest()->getParam('search_value');

        if (empty($productId) || empty($generalId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component_Buy')->getObject('Listing_Product',$productId);

        if ($listingProduct->isNotListed() &&
            !$listingProduct->getData('template_new_product_id')
        ) {
            if (!empty($searchType) && !empty($searchValue)) {
                $generalIdSearchInfo = array(
                    'is_set_automatic' => false,
                    'type'  => $searchType,
                    'value' => $searchValue,
                );

                $listingProduct->setSettings('general_id_search_info', $generalIdSearchInfo);
            }

            $listingProduct->setData('general_id',$generalId);
            $listingProduct->setData('template_new_product_id',NULL);
            $listingProduct->setData('search_settings_status',NULL);
            $listingProduct->setData('search_settings_data',NULL);

            $listingProduct->save();
        }

        return $this->getResponse()->setBody('0');
    }

    public function unmapFromBuyComSkuAction()
    {
        $productIds = $this->getRequest()->getParam('product_ids');

        if (empty($productIds)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $productIds = explode(',', $productIds);

        $type = 'success';
        $message = Mage::helper('M2ePro')->__(
            'Rakuten.com SKU(s) was successfully unassigned.'
        );

        foreach ($productIds as $productId) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $listingProduct = Mage::helper('M2ePro/Component_Buy')->getObject('Listing_Product',$productId);

            if (!$listingProduct->isNotListed() ||
                $listingProduct->isLockedObject('in_action')) {
                $type = 'error';
                $message = Mage::helper('M2ePro')->__(
                    'Some Rakuten.com SKU(s) were not unassigned as their Listing Status is other than "Not Listed".'
                );
                continue;
            }

            $listingProduct->setData('general_id',NULL);
            $listingProduct->setData('template_new_product_id',NULL);
            $listingProduct->setData('general_id_search_info',NULL);
            $listingProduct->setData('search_settings_data',NULL);
            $listingProduct->setData('search_settings_status',NULL);

            $listingProduct->save();

        }

        return $this->getResponse()->setBody(json_encode(array(
            'type' => $type,
            'message' => $message
        )));
    }

    //########################################

    protected function setRuleData($prefix)
    {
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $storeId = isset($listingData['store_id']) ? (int)$listingData['store_id'] : 0;
        $prefix .= isset($listingData['id']) ? '_'.$listingData['id'] : '';
        Mage::helper('M2ePro/Data_Global')->setValue('rule_prefix', $prefix);

        $ruleModel = Mage::getModel('M2ePro/Buy_Magento_Product_Rule')->setData(
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

        $prefix = 'buy_hide_products_others_listings_';
        $prefix .= is_null($id) ? 'add' : $id;
        $prefix .= '_listing_product';

        return $prefix;
    }

    //########################################
}
