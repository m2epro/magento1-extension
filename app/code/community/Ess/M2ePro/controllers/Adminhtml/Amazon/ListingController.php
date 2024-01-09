<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Amazon_ListingController
    extends Ess_M2ePro_Controller_Adminhtml_Amazon_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
            ->_title(Mage::helper('M2ePro')->__('Listings'));

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/DropDown.css')
            ->addJs('mage/adminhtml/rules.js')
            ->addJs('M2ePro/Plugin/ActionColumn.js')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Listing/ProductGrid.js')
            ->addJs('M2ePro/Listing/Category/Tree.js')
            ->addJs('M2ePro/Listing/AutoAction.js')
            ->addJs('M2ePro/Amazon/Listing/AutoAction.js')

            ->addJs('M2ePro/Grid.js')
            ->addJs('M2ePro/Listing/EditListingTitle.js')
            ->addJs('M2ePro/Listing/Grid.js')
            ->addJs('M2ePro/Listing/Other.js')
            ->addJs('M2ePro/Listing/Other/Grid.js')

            ->addJs('M2ePro/Action.js')
            ->addJs('M2ePro/Listing/Action.js')
            ->addJs('M2ePro/Listing/Moving.js')
            ->addJs('M2ePro/Listing/Mapping.js')
            ->addJs('M2ePro/Amazon/Listing.js')
            ->addJs('M2ePro/Amazon/Listing/Grid.js')
            ->addJs('M2ePro/Amazon/Listing/Action.js')
            ->addJs('M2ePro/Amazon/Listing/ProductSearch.js')
            ->addJs('M2ePro/Amazon/Listing/Template/Description.js')
            ->addJs('M2ePro/Amazon/Listing/Template/Shipping.js')
            ->addJs('M2ePro/Amazon/Listing/Template/ProductTaxCode.js')
            ->addJs('M2ePro/Amazon/Listing/VariationProductManage.js')
            ->addJs('M2ePro/Amazon/Listing/Fulfillment.js')
            ->addJs('M2ePro/Amazon/Listing/RepricingPrice.js')
            ->addJs('M2ePro/Amazon/Listing/Other/Grid.js')

            ->addJs('M2ePro/TemplateManager.js')
            ->addJs('M2ePro/Amazon/Listing/Create/Selling.js')
            ->addJs('M2ePro/Amazon/Listing/Create/Search.js')
            ->addJs('M2ePro/Amazon/Listing/Settings.js')
            ->addJs('M2ePro/Amazon/Listing/ProductsFilter.js')

            ->addJs('M2ePro/Amazon/Listing/Product/Variation.js')

            ->addJs('M2ePro/Listing/Other/AutoMapping.js')
            ->addJs('M2ePro/Listing/Other/Removing.js')
            ->addJs('M2ePro/Listing/Other/Unmapping.js')

            ->addJs('M2ePro/Amazon/Listing/Transferring.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "m2e-pro-listings");

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Amazon::MENU_ROOT_NODE_NICK . '/listings'
        );
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_amazon_manageListings',
                    '',
                    array(
                        'tab' => $this->getRequest()->getParam(
                            'tab',
                            Ess_M2ePro_Block_Adminhtml_Amazon_ManageListings::TAB_ID_LISTING
                        )
                    )
                )
            )
            ->renderLayout();
    }

    public function listingGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function getListingTabAction()
    {
        /** @var $block Ess_M2ePro_Block_Adminhtml_Amazon_Listing */
        $block = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_manageListings',
            '',
            array('tab' => Ess_M2ePro_Block_Adminhtml_Amazon_ManageListings::TAB_ID_LISTING)
        );
        $this->_initAction()->_addContent($block)->renderLayout();
    }

    public function getListingOtherTabAction()
    {
        /** @var $block Ess_M2ePro_Block_Adminhtml_Amazon_Listing */
        $block = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_manageListings',
            '',
            array('tab' => Ess_M2ePro_Block_Adminhtml_Amazon_ManageListings::TAB_ID_LISTING_OTHER)
        );
        $this->_initAction()->_addContent($block)->renderLayout();
    }

    public function getSearchTabAction()
    {
        /** @var $block Ess_M2ePro_Block_Adminhtml_Amazon_Listing */
        $block = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_manageListings',
            '',
            array('tab' => Ess_M2ePro_Block_Adminhtml_Amazon_ManageListings::TAB_ID_SEARCH)
        );
        $this->_initAction()->_addContent($block)->renderLayout();
    }

    //########################################

    public function searchAction()
    {
        /** @var $block Ess_M2ePro_Block_Adminhtml_Amazon_Listing */
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_search');
        $this->_initAction()->_addContent($block)->renderLayout();
    }

    public function searchGridAction()
    {
        $listingType = $this->getRequest()->getParam('listing_type', false);
        $gridBlock = $listingType == Ess_M2ePro_Block_Adminhtml_Listing_Search_Switcher::LISTING_TYPE_LISTING_OTHER
            ? $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_search_other_grid')
            : $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_search_m2ePro_grid');

        $this->getResponse()->setBody($gridBlock->toHtml());
    }

    //########################################

    public function viewAction()
    {
        if ((bool)$this->getRequest()->getParam('do_list', false)) {
            Mage::helper('M2ePro/Data_Session')->setValue(
                'products_ids_for_list',
                implode(',', Mage::helper('M2ePro/Data_Session')->getValue('added_products_ids'))
            );

            return $this->_redirect(
                '*/*/*', array(
                '_current'  => true,
                'do_list'   => null
                )
            );
        }

        $id = $this->getRequest()->getParam('id');
        /** @var $model Ess_M2ePro_Model_Listing */

        try {
            $model = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Listing', $id);
        } catch (Ess_M2ePro_Model_Exception_Logic $e) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_amazon_listing/index');
        }

        $listingProductsIds = $model->getSetting('additional_data', 'adding_listing_products_ids');

        if (!empty($listingProductsIds)) {
            $this->_redirect(
                '*/adminhtml_amazon_listing_productAdd/index', array(
                'id' => $id,
                'step' => 3,
                'not_completed' => 1
                )
            );
            return;
        }

        // Check listing lock object
        // ---------------------------------------
        if ($model->isSetProcessingLock('products_in_action')) {
            $this->_getSession()->addNotice(
                Mage::helper('M2ePro')->__('Some Amazon request(s) are being processed now.')
            );
        }

        // ---------------------------------------

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

        // Set rule model
        // ---------------------------------------
        $this->setRuleData('amazon_rule_listing_view');
        // ---------------------------------------

        $this->_initAction();
        $this->setPageHelpLink(null, null, "m2e-pro-listings");

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_view'))
            ->renderLayout();
    }

    public function viewGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Listing', $id);

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

        // Set rule model
        // ---------------------------------------
        $this->setRuleData('amazon_rule_listing_view');
        // ---------------------------------------

        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_listing_view')->getGridHtml();
        $this->getResponse()->setBody($response);
    }

    //########################################

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $listing = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Listing', $id);

        if (!$listing->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_amazon_listing/index');
        }

        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_edit'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            $this->_redirect('*/adminhtml_amazon_listing/index');
        }

        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Listing')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_amazon_listing/index');
        }

        $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Listing_SnapshotBuilder');
        $snapshotBuilder->setModel($model);

        $oldData = $snapshotBuilder->getSnapshot();

        // Base prepare
        // ---------------------------------------
        $data = array();
        // ---------------------------------------

        // tab: settings
        // ---------------------------------------
        $keys = array(
            'template_selling_format_id',
            'template_synchronization_id',
            'template_shipping_id'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = (!empty($post[$key])) ? $post[$key] : null;
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

        $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Listing_SnapshotBuilder');
        $snapshotBuilder->setModel($model);

        $newData = $snapshotBuilder->getSnapshot();

        $diff = Mage::getModel('M2ePro/Amazon_Listing_Diff');
        $diff->setNewSnapshot($newData);
        $diff->setOldSnapshot($oldData);

        $affectedListingsProducts = Mage::getModel('M2ePro/Amazon_Listing_AffectedListingsProducts');
        $affectedListingsProducts->setModel($model);

        $affectedListingsProductsData = $affectedListingsProducts->getData(
            array('id', 'status'), array('only_physical_units' => true)
        );

        $changeProcessor = Mage::getModel('M2ePro/Amazon_Listing_ChangeProcessor');
        $changeProcessor->process($diff, $affectedListingsProductsData);

        $this->processSellingFormatTemplateChange($oldData, $newData, $affectedListingsProductsData);
        $this->processSynchronizationTemplateChange($oldData, $newData, $affectedListingsProductsData);

        $affectedListingsProductsData = $affectedListingsProducts->getData(
            array('id', 'status'),
            array('only_physical_units' => true, 'template_shipping_id' => true)
        );
        $this->processShippingTemplateChange($oldData, $newData, $affectedListingsProductsData);

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('The Listing was saved.'));

        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list', array(), array('edit'=>array('id'=>$id))));
    }

    public function deleteAction()
    {
        $ids = $this->getRequestIds();

        if (empty($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select Item(s) to remove.'));
            $this->_redirect('*/*/index');
            return;
        }

        $deleted = $locked = 0;
        foreach ($ids as $id) {
            $listing = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Listing', $id);
            if ($listing->isLocked()) {
                $locked++;
            } else {
                $listing->deleteInstance();
                $deleted++;
            }
        }

        $tempString = Mage::helper('M2ePro')->__('%amount% Listing(s) were deleted', $deleted);
        $deleted && $this->_getSession()->addSuccess($tempString);

        $tempString = Mage::helper('M2ePro')->__(
            '%amount% Listing(s) have Listed Items and can not be deleted', $locked
        );
        $locked && $this->_getSession()->addError($tempString);

        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl());
    }

    //########################################

    protected function scheduleAction($action, array $params = array())
    {
        if (!$listingsProductsIds = $this->getRequest()->getParam('selected_products')) {
            return Mage::helper('M2ePro')->__('You should select Products');
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $productsCollection */
        $productsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $productsCollection->addFieldToFilter('id', explode(',', $listingsProductsIds));

        /** @var Ess_M2ePro_Model_Listing_Product[] $listingsProducts */
        $listingsProducts = $productsCollection->getItems();

        $childListingsProducts = array();

        foreach ($listingsProducts as $index => $listingProduct) {
            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            if (!$amazonListingProduct->getVariationManager()->isRelationParentType()) {
                continue;
            }

            $tempChildListingsProducts = $amazonListingProduct->getVariationManager()
                ->getTypeModel()
                ->getChildListingsProducts();

            if (empty($tempChildListingsProducts)) {
                continue;
            }

            if ($action != Ess_M2ePro_Model_Listing_Product::ACTION_REVISE) {
                unset($listingsProducts[$index]);
            }

            $childListingsProducts = array_merge($childListingsProducts, $tempChildListingsProducts);
        }

        $listingsProducts = array_merge($listingsProducts, $childListingsProducts);
        $logsActionId = Mage::getModel('M2ePro/Listing_Log')->getResource()->getNextActionId();

        $this->checkLocking($listingsProducts, $logsActionId, $action);
        if (empty($listingsProducts)) {
            return Mage::helper('M2ePro')->jsonEncode(array('result' => 'error', 'action_id' => $logsActionId));
        }

        $this->createUpdateScheduledActions(
            $listingsProducts,
            $action,
            $params
        );

        if (isset($params['switch_to'])) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'messages' => array(array(
                            'type' => 'success',
                            'text' => Mage::helper('M2ePro')->__(
                                'Fulfillment switching is in progress now. Please wait.'
                            )
                        ))
                    )
                )
            );
        }

        return Mage::helper('M2ePro')->jsonEncode(array('result' => 'success', 'action_id' => $logsActionId));
    }

    //########################################

    protected function checkLocking(&$listingsProducts, $logsActionId, $action)
    {
        foreach ($listingsProducts as $index => $listingProduct) {
            $lockManager = Mage::getModel(
                'M2ePro/Listing_Product_LockManager', array('listing_product' => $listingProduct)
            );
            $lockManager->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);
            $lockManager->setLogsActionId($logsActionId);
            $lockManager->setLogsAction($this->getLogsAction($action));

            if ($lockManager->checkLocking()) {
                unset($listingsProducts[$index]);
            }
        }
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product[] $listingsProducts
     * @param $action
     * @param array $params
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function createUpdateScheduledActions(&$listingsProducts, $action, array $params)
    {
        $listingsProductsIds = array();
        foreach ($listingsProducts as $listingProduct) {
            $listingsProductsIds[] = $listingProduct->getId();
        }

        $existedScheduled = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $existedScheduled->addFieldToFilter('listing_product_id', $listingsProductsIds);

        $scheduledActionManager = Mage::getModel('M2ePro/Listing_Product_ScheduledAction_Manager');

        foreach ($listingsProducts as $listingProduct) {
            $scheduledAction = Mage::getModel('M2ePro/Listing_Product_ScheduledAction');
            $scheduledAction->setData(
                $this->createUpdateScheduledActionsDataCallback($listingProduct, $action, $params)
            );

            if ($existedScheduled->getItemByColumnValue('listing_product_id', $listingProduct->getId())) {
                $scheduledActionManager->updateAction($scheduledAction);
            } else {
                $scheduledActionManager->addAction($scheduledAction);
            }
        }
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @param int $action
     * @param array $params
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function createUpdateScheduledActionsDataCallback($listingProduct, $action, array $params)
    {
        $tag = null;
        $params['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER;
        $additionalData = array('params' => $params);

        if ($action == Ess_M2ePro_Model_Listing_Product::ACTION_REVISE) {
            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator $configurator */
            $configurator = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Configurator');
            $configurator->enableAll();
            $tag = '/qty/price/details/images/';

            if (isset($params['switch_to'])) {
                $configurator->disableAll();
                $configurator->allowQty();
                $tag = '/qty/';
            } elseif ($listingProduct->getChildObject()->getVariationManager()->isRelationParentType()) {
                $configurator->disableAll();
                $configurator->allowImages();
                $configurator->allowDetails();
                $tag = '/details/images/';
            }

            $additionalData['configurator'] = $configurator->getData();
        }

        return array(
            'listing_product_id' => $listingProduct->getId(),
            'component'          => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'action_type'        => $action,
            'is_force'           => true,
            'tag'                => $tag,
            'additional_data'    => Mage::helper('M2ePro')->jsonEncode($additionalData)
        );
    }

    // ---------------------------------------

    public function runListProductsAction()
    {
        return $this->getResponse()->setBody(
            $this->scheduleAction(Ess_M2ePro_Model_Listing_Product::ACTION_LIST)
        );
    }

    public function runReviseProductsAction()
    {
        return $this->getResponse()->setBody(
            $this->scheduleAction(Ess_M2ePro_Model_Listing_Product::ACTION_REVISE)
        );
    }

    public function runRelistProductsAction()
    {
        return $this->getResponse()->setBody(
            $this->scheduleAction(Ess_M2ePro_Model_Listing_Product::ACTION_RELIST)
        );
    }

    public function runStopProductsAction()
    {
        return $this->getResponse()->setBody(
            $this->scheduleAction(Ess_M2ePro_Model_Listing_Product::ACTION_STOP)
        );
    }

    public function runStopAndRemoveProductsAction()
    {
        if (!$listingsProductsIds = $this->getRequest()->getParam('selected_products')) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->__('You should select Products'));
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $productsCollection */
        $productsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $productsCollection->addFieldToFilter('id', explode(',', $listingsProductsIds));

        /** @var Ess_M2ePro_Model_Listing_Product[] $listingsProducts */
        $listingsProducts = $productsCollection->getItems();
        $logsActionId = Mage::getModel('M2ePro/Listing_Log')->getResource()->getNextActionId();

        $this->checkLocking($listingsProducts, $logsActionId, Ess_M2ePro_Model_Listing_Product::ACTION_STOP);
        if (empty($listingsProducts)) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(array('result' => 'error', 'action_id' => $logsActionId))
            );
        }

        foreach ($listingsProducts as $index => $listingProduct) {
            if (!$listingProduct->isStoppable()) {
                /** @var Ess_M2ePro_Model_Amazon_Listing_Product_RemoveHandler $removeHandler */
                $removeHandler = Mage::getModel(
                    'M2ePro/Amazon_Listing_Product_RemoveHandler', array('listing_product' => $listingProduct)
                );
                $removeHandler->process();

                unset($listingsProducts[$index]);
            }
        }

        if (empty($listingsProducts)) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(array('result' => 'success', 'action_id' => $logsActionId))
            );
        }

        $this->createUpdateScheduledActions(
            $listingsProducts,
            Ess_M2ePro_Model_Listing_Product::ACTION_STOP,
            array('remove' => true)
        );

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(array('result' => 'success', 'action_id' => $logsActionId))
        );
    }

    public function runDeleteAndRemoveProductsAction()
    {
        if (!$listingsProductsIds = $this->getRequest()->getParam('selected_products')) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->__('You should select Products'));
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $productsCollection */
        $productsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $productsCollection->addFieldToFilter('id', explode(',', $listingsProductsIds));

        /** @var Ess_M2ePro_Model_Listing_Product[] $listingsProducts */
        $listingsProducts = $productsCollection->getItems();
        $logsActionId = Mage::getModel('M2ePro/Listing_Log')->getResource()->getNextActionId();

        $this->checkLocking($listingsProducts, $logsActionId, Ess_M2ePro_Model_Listing_Product::ACTION_DELETE);
        if (empty($listingsProducts)) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(array('result' => 'error', 'action_id' => $logsActionId))
            );
        }

        foreach ($listingsProducts as $index => $listingProduct) {
            if ($listingProduct->isNotListed()) {
                /** @var Ess_M2ePro_Model_Amazon_Listing_Product_RemoveHandler $removeHandler */
                $removeHandler = Mage::getModel(
                    'M2ePro/Amazon_Listing_Product_RemoveHandler', array('listing_product' => $listingProduct)
                );
                $removeHandler->process();

                unset($listingsProducts[$index]);
            }
        }

        if (empty($listingsProducts)) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(array('result' => 'success', 'action_id' => $logsActionId))
            );
        }

        $this->createUpdateScheduledActions(
            $listingsProducts,
            Ess_M2ePro_Model_Listing_Product::ACTION_DELETE,
            array('remove' => true)
        );

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(array('result' => 'success', 'action_id' => $logsActionId))
        );
    }

    //########################################

    public function getVariationEditPopupAction()
    {
        $listingProductId = (int)$this->getRequest()->getParam('listing_product_id');

        if (!$listingProductId) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                    'type' => 'error',
                    'message' => Mage::helper('M2ePro')->__('Listing Product must be specified.')
                    )
                )
            );
        }

        $variationEditBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_listing_product_variation_edit', '',
            array(
                'listing_product_id' => $listingProductId,
            )
        );

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'type' => 'success',
                'text' => $variationEditBlock->toHtml()
                )
            )
        );
    }

    // ---------------------------------------

    public function getVariationManagePopupAction()
    {
        $listingProductId = (int)$this->getRequest()->getParam('listing_product_id');

        if (!$listingProductId) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                    'type' => 'error',
                    'message' => Mage::helper('M2ePro')->__('Listing Product must be specified.')
                    )
                )
            );
        }

        $variationManageBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_listing_product_variation_manage', '',
            array(
                'listing_product_id' => $listingProductId,
            )
        );

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'type' => 'success',
                'text' => $variationManageBlock->toHtml()
                )
            )
        );
    }

    //########################################

    public function variationEditAction()
    {
        $listingProductId = (int)$this->getRequest()->getParam('listing_product_id');
        $variationData = $this->getRequest()->getParam('variation_data');

        if (!$listingProductId || !$variationData) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                    'type' => 'error',
                    'message' => Mage::helper('M2ePro')->__(
                        'Listing Product and Variation Data must be specified.'
                    )
                    )
                )
            );
        }

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component')->getComponentObject(
            Ess_M2ePro_Helper_Component_Amazon::NICK, 'Listing_Product', $listingProductId
        );

        $magentoVariations = $listingProduct->getMagentoProduct()->getVariationInstance()->getVariationsTypeStandard();
        $magentoVariations = $magentoVariations['variations'];
        foreach ($magentoVariations as $key => $magentoVariation) {
            foreach ($magentoVariation as $option) {
                $value = $option['option'];
                $attribute = $option['attribute'];

                if ($variationData[$attribute] != $value) {
                    unset($magentoVariations[$key]);
                }
            }
        }

        if (count($magentoVariations) != 1) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                    'type' => 'error',
                    'message' => Mage::helper('M2ePro')->__('Only 1 Variation must leave.')
                    )
                )
            );
        }

        $individualModel = $listingProduct->getChildObject()->getVariationManager()->getTypeModel();
        $individualModel->setProductVariation(reset($magentoVariations));

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'type' => 'success',
                'message' => Mage::helper('M2ePro')->__('Variation has been edited.')
                )
            )
        );
    }

    public function variationManageAction()
    {
        $listingProductId = (int)$this->getRequest()->getParam('listing_product_id');
        $variationsData = $this->getRequest()->getParam('variation_data');

        if (!$listingProductId || !$variationsData) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                    'type' => 'error',
                    'message' => Mage::helper('M2ePro')->__(
                        'Listing Product and Variation Data must be specified.'
                    )
                    )
                )
            );
        }

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component')->getComponentObject(
            Ess_M2ePro_Helper_Component_Amazon::NICK, 'Listing_Product', $listingProductId
        );

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();

        $isVariationProductMatched = (
            $variationManager->isIndividualType() &&
            $variationManager->getTypeModel()->isVariationProductMatched()
        );

        if ($isVariationProductMatched) {
            $listingProduct = $this->duplicateListingProduct($listingProduct);
        } else {
            $listingProduct->setData('search_settings_status', null);
            $listingProduct->setData('search_settings_data', null);
            $listingProduct->save();
        }

        $magentoVariations = $listingProduct->getMagentoProduct()->getVariationInstance()->getVariationsTypeStandard();
        $magentoVariations = $magentoVariations['variations'];

        $isFirst = true;
        foreach ($variationsData as $variationData) {
            !$isFirst && $listingProduct = $this->duplicateListingProduct($listingProduct);
            $isFirst = false;

            $tempMagentoVariations = $magentoVariations;

            foreach ($tempMagentoVariations as $key => $magentoVariation) {
                foreach ($magentoVariation as $option) {
                    $value = $option['option'];
                    $attribute = $option['attribute'];

                    if ($variationData[$attribute] != $value) {
                        unset($tempMagentoVariations[$key]);
                    }
                }
            }

            if (count($tempMagentoVariations) != 1) {
                return $this->getResponse()->setBody(
                    Mage::helper('M2ePro')->jsonEncode(
                        array(
                        'type' => 'error',
                        'message' => Mage::helper('M2ePro')->__('Only 1 Variation must leave.')
                        )
                    )
                );
            }

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $listingProductManager */
            $listingProductManager = $listingProduct->getChildObject()->getVariationManager();

            if ($listingProductManager->isRelationParentType() && $listingProductManager->modeCanBeSwitched()) {
                $listingProductManager->switchModeToAnother();
            }

            $individualModel = $listingProductManager->getTypeModel();
            $individualModel->setProductVariation(reset($tempMagentoVariations));
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'type' => 'success',
                'message' => Mage::helper('M2ePro')->__('Variation(s) has been saved.')
                )
            )
        );
    }

    public function variationResetAction()
    {
        $listingProductId = (int)$this->getRequest()->getParam('listing_product_id');

        if (!$listingProductId) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                    'type' => 'error',
                    'message' => Mage::helper('M2ePro')->__(
                        'For changing the Mode of working with Magento Variational Product
                     you have to choose the Specific Product.'
                    )
                    )
                )
            );
        }

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component')->getComponentObject(
            Ess_M2ePro_Helper_Component_Amazon::NICK, 'Listing_Product', $listingProductId
        );

        $listingProduct->setData('search_settings_status', null);
        $listingProduct->setData('search_settings_data', null);
        $listingProduct->save();

        $listingProductManager = $listingProduct->getChildObject()->getVariationManager();
        if ($listingProductManager->isIndividualType() && $listingProductManager->modeCanBeSwitched()) {
            $listingProductManager->switchModeToAnother();
        }

        $listingProductManager->getTypeModel()->getProcessor()->process();

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                    'type' => 'success',
                    'message' => Mage::helper('M2ePro')->__(
                        'Mode of working with Magento Variational Product has 
                        been switched to work with Parent-Child Product.'
                    )
                )
            )
        );

    }

    // ---------------------------------------

    public function variationManageGenerateAction()
    {
        $listingProductId = (int)$this->getRequest()->getParam('listing_product_id');

        if (!$listingProductId) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                    'type' => 'error',
                    'message' => Mage::helper('M2ePro')->__(
                        'Listing Product must be specified.'
                    )
                    )
                )
            );
        }

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component')->getComponentObject(
            Ess_M2ePro_Helper_Component_Amazon::NICK, 'Listing_Product', $listingProductId
        );

        $magentoVariations = $listingProduct->getMagentoProduct()->getVariationInstance()->getVariationsTypeStandard();
        $magentoVariations = $magentoVariations['variations'];

        if (!$this->getRequest()->getParam('unique', false)) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                    'type' => 'success',
                    'text' => $magentoVariations
                    )
                )
            );
        }

        $listingProducts = Mage::helper('M2ePro/Component')
            ->getComponentCollection(Ess_M2ePro_Helper_Component_Amazon::NICK, 'Listing_Product')
            ->addFieldToFilter('listing_id', $listingProduct->getListingId())
            ->addFieldToFilter('product_id', $listingProduct->getProductId())
            ->getItems();

        foreach ($listingProducts as $listingProduct) {
            $variationManager = $listingProduct->getChildObject()->getVariationManager();

            if (!($variationManager->isIndividualType() &&
                $variationManager->getTypeModel()->isVariationProductMatched())) {
                continue;
            }

            $variations = $listingProduct->getVariations(true);
            if (empty($variations)) {
                throw new Ess_M2ePro_Model_Exception(
                    'There are no variations for a variation product.',
                    array(
                        'listing_product_id' => $listingProduct->getId()
                    )
                );
            }

            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);

            $options = $variation->getOptions();
            foreach ($options as &$option) {
                $option = array(
                    'product_id' => $option['product_id'],
                    'product_type' => $option['product_type'],
                    'attribute' => $option['attribute'],
                    'option' => $option['option']
                );
            }

            unset($option);

            foreach ($magentoVariations as $key => $variation) {
                if ($variation != $options) {
                    continue;
                }

                unset($magentoVariations[$key]);
            }
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'type' => 'success',
                'text' => array_values($magentoVariations)
                )
            )
        );

    }

    //########################################

    public function duplicateProductsAction()
    {
        $listingProductsIds = $this->getRequest()->getParam('ids');
        $listingProductsIds = explode(',', $listingProductsIds);
        $listingProductsIds = array_filter($listingProductsIds);

        if (empty($listingProductsIds)) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                    'type' => 'error',
                    'message' => Mage::helper('M2ePro')->__('Listing Products must be specified.')
                    )
                )
            );
        }

        foreach ($listingProductsIds as $listingProductId) {
            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $listingProduct = Mage::helper('M2ePro/Component')->getComponentObject(
                Ess_M2ePro_Helper_Component_Amazon::NICK, 'Listing_Product', $listingProductId
            );

            $this->duplicateListingProduct($listingProduct);
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'type' => 'success',
                'message' => Mage::helper('M2ePro')->__('The Items were duplicated.')
                )
            )
        );
    }

    // ---------------------------------------

    protected function duplicateListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $duplicatedListingProduct = $listingProduct->getListing()->addProduct(
            $listingProduct->getProductId(), Ess_M2ePro_Helper_Data::INITIATOR_USER, false, false
        );

        $variationManager = $listingProduct->getChildObject()->getVariationManager();
        if (!$variationManager->isVariationProduct()) {
            return $duplicatedListingProduct;
        }

        $duplicatedListingProductManager = $duplicatedListingProduct->getChildObject()->getVariationManager();

        if ($variationManager->isIndividualType() && $duplicatedListingProductManager->modeCanBeSwitched()) {
            $duplicatedListingProductManager->switchModeToAnother();
        }

        return $duplicatedListingProduct;
    }

    //########################################

    public function switchToAFNAction()
    {
        return $this->scheduleAction(
            Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, array(
            'switch_to' => Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Qty::FULFILLMENT_MODE_AFN,
            )
        );
    }

    public function switchToMFNAction()
    {
        return $this->scheduleAction(
            Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, array(
            'switch_to' => Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Qty::FULFILLMENT_MODE_MFN,
            )
        );
    }

    //########################################

    public function getSearchAsinMenuAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('ERROR: No Product ID!');
        }

        $productSearchMenuBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_listing_productSearch_menu');
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
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);

        $marketplaceId = $listingProduct->getListing()->getMarketplaceId();

        $searchSettingsData = $listingProduct->getSettings('search_settings_data');
        if (!empty($searchSettingsData['data'])) {
            Mage::helper('M2ePro/Data_Global')->setValue('product_id', $productId);
            Mage::helper('M2ePro/Data_Global')->setValue('is_suggestion', true);
            Mage::helper('M2ePro/Data_Global')->setValue('marketplace_id', $marketplaceId);
            Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $searchSettingsData);

            $response = $this->loadLayout()->getLayout()
                ->createBlock('M2ePro/adminhtml_amazon_listing_productSearch_grid')->toHtml();
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

        if (empty($productId) || empty($query)) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'result' => 'error',
                        'data'   => Mage::helper('M2ePro')->__('Required search parameters are not provided.')
                    )
                )
            );
        }

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);

        if ($listingProduct->isNotListed() &&
            !$listingProduct->getData('is_general_id_owner') &&
            !$listingProduct->getData('general_id')
        ) {
            /** @var $dispatcher Ess_M2ePro_Model_Amazon_Search_Dispatcher */
            $dispatcher = Mage::getModel('M2ePro/Amazon_Search_Dispatcher');

            if ($dispatcher->createCustomSearchHandler()->resolveIdentifierType($query) === null) {
                return $this->getResponse()->setBody(
                    Mage::helper('M2ePro')->jsonEncode(
                        array(
                            'result' => 'error',
                            'data'   => Mage::helper('M2ePro')->__('Invalid Product ID format.')
                        )
                    )
                );
            }

            $result = $dispatcher->runCustom($listingProduct, $query);

            if ($result === false || $result['data'] === false) {
                return $this->getResponse()->setBody(
                    Mage::helper('M2ePro')->jsonEncode(
                        array(
                            'result' => 'error',
                            'data'   => Mage::helper('M2ePro')->__(
                                'Server is currently unavailable. Please try again later.'
                            )
                        )
                    )
                );
            }

            $marketplaceObj = $listingProduct->getListing()->getMarketplace();
            Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $result);
            Mage::helper('M2ePro/Data_Global')->setValue('product_id', $productId);
            Mage::helper('M2ePro/Data_Global')->setValue('marketplace_id', $marketplaceObj->getId());
        } else {
            Mage::helper('M2ePro/Data_Global')->setValue('temp_data', array());
        }

        $data = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_listing_productSearch_grid')->toHtml();

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                    'result' => 'success',
                    'data'   => $data
                )
            )
        );
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
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);

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

    public function getProductsSearchStatusAction()
    {
        $productsIds = $this->getRequest()->getParam('products_ids');

        if (empty($productsIds)) {
            return $this->getResponse()->setBody('You should select one or more Products');
        }

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $tableListingProduct = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_listing_product');
        $tableAmazonListingProduct = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_amazon_listing_product');

        $itemsForSearchSelect = $connRead->select();
        $itemsForSearchSelect->from(array('lp' => $tableListingProduct), array('id'))
            ->join(
                array('alp' => $tableAmazonListingProduct),
                'lp.id = alp.listing_product_id',
                array()
            )
            ->where('lp.id IN (?)', $productsIds)
            ->where('lp.status = ?', (int)Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED)
            ->where('alp.general_id IS NULL')
            ->where('alp.is_general_id_owner = 0');

        $selectWarnings = clone $itemsForSearchSelect;
        $selectError    = clone $itemsForSearchSelect;

        $searchStatusActionRequired = Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_ACTION_REQUIRED;
        $searchStatusInProgress = Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_IN_PROGRESS;
        $selectWarnings->where(
            'alp.search_settings_status = ' . $searchStatusActionRequired .
            ' OR alp.search_settings_status = ' . $searchStatusInProgress
        );

        $warningsCount = Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($selectWarnings);

        $messages = array();

        if (!empty($warningsCount)) {
            $messages[] = array(
                'type' => 'warning',
                'text' => Mage::helper('M2ePro')->__(
                    'For %count% Items it is necessary to choose manually one of the found Amazon Products
                     or these Items are in process of Search and results for them will be available later.',
                    count($warningsCount)
                )
            );
        }

        $searchStatusNotFound = Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_NOT_FOUND;
        $searchStatusIdentifierInvalid = Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_IDENTIFIER_INVALID;
        $selectError->where(
            'alp.search_settings_status = ' . $searchStatusNotFound
            . ' OR alp.search_settings_status = ' . $searchStatusIdentifierInvalid
        );

        $errorsCount = Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($selectError);

        if (!empty($errorsCount)) {
            $messages[] = array(
                'type' => 'error',
                'text' => Mage::helper('M2ePro')->__(
                    'For %count% Items no Amazon Products were found. Please use Manual Search
                     or create New ASIN/ISBN.',
                    count($errorsCount)
                )
            );
        }

        if (empty($messages)) {
            $messages[] = array(
                'type' => 'success',
                'text' => Mage::helper('M2ePro')->__(
                    'ASIN(s)/ISBN(s) were found and assigned for selected Items.'
                )
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

        $listingProduct->setData('general_id', $generalId);
        $listingProduct->setData('search_settings_status', null);
        $listingProduct->setData('search_settings_data', null);

        $listingProduct->save();

        if (empty($optionsData)) {
            return $this->getResponse()->setBody('0');
        }

        $optionsData = Mage::helper('M2ePro')->jsonDecode($optionsData);

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
                        $amazonAttrKey = 'virtual_magento_option_' .
                            str_replace('virtual_magento_attributes_', '', $key);
                        $virtualMagentoAttributes[$value] = $attributesData[$amazonAttrKey];

                        unset($attributesData[$key]);
                        unset($attributesData[$amazonAttrKey]);
                        continue;
                    }

                    if (strpos($key, 'virtual_amazon_attributes_') !== false) {
                        $amazonAttrKey = 'virtual_amazon_option_' . str_replace('virtual_amazon_attributes_', '', $key);
                        $virtualAmazonAttributes[$value] = $attributesData[$amazonAttrKey];

                        unset($attributesData[$key]);
                        unset($attributesData[$amazonAttrKey]);
                        continue;
                    }

                    if (strpos($key, 'magento_attributes_') !== false) {
                        $amazonAttrKey = 'amazon_attributes_' . str_replace('magento_attributes_', '', $key);
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

                return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
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

            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
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

        $message = Mage::helper('M2ePro')->__('ASIN(s)/ISBN(s) was unassigned.');
        $type = 'success';

        foreach ($productsIds as $productId) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);

            if (!$listingProduct->isNotListed() ||
                $listingProduct->isSetProcessingLock('in_action') ||
                ($listingProduct->getChildObject()->getVariationManager()->isVariationParent() &&
                 $listingProduct->isSetProcessingLock('child_products_in_action'))) {
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

            $listingProduct->setData('general_id', null);
            $listingProduct->setData('general_id_search_info', null);
            $listingProduct->setData(
                'is_general_id_owner',
                Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_NO
            );
            $listingProduct->setData('search_settings_status', null);
            $listingProduct->setData('search_settings_data', null);

            $listingProduct->save();

            if ($runListingProductProcessor) {
                $parentType->getProcessor()->process();
            }
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'type'    => $type,
                'message' => $message
                )
            )
        );
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
                '%count% Items are Simple with Custom Options,
                 Bundle or Downloadable with Separated Links Magento Products.',
                $tempCount
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
                        'New ASIN/ISBN creation feature was added to %count% Products.',
                        count($filteredProductsIdsByParent)
                    )
                )
            );
        }

        if (!empty($badDescriptionProductsIds)) {
            $badDescriptionProductsIds = $variationHelper
                ->filterProductsByMagentoProductType($badDescriptionProductsIds);

            $descriptionTemplatesBlock = $this->loadLayout()->getLayout()
                ->createBlock('M2ePro/adminhtml_amazon_listing_template_description_main');
            $descriptionTemplatesBlock->setNewAsin(true);
            $descriptionTemplatesBlock->setMessages($messages);
            $descriptionTemplatesBlock = $descriptionTemplatesBlock->toHtml();
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'messages' => $messages,
                'data' => $descriptionTemplatesBlock,
                'products_ids' => implode(',', $badDescriptionProductsIds)
                )
            )
        );
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
                count($productsIdsTemp) - count($filteredProductsIdsByType)
            );
        }

        if (empty($filteredProductsIdsByType)) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                    'type' => $msgType,
                    'messages' => $messages
                    )
                )
            );
        }

        $this->setDescriptionTemplateFroProductsByChunks($filteredProductsIdsByType, $templateId);
        $this->runProcessorForParents($filteredProductsIdsByType);

        $messages[] = Mage::helper('M2ePro')->__(
            'Description Policy was assigned to %count% Products',
            count($filteredProductsIdsByType)
        );

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'type' => $msgType,
                'messages' => $messages,
                'products_ids' => implode(',', $filteredProductsIdsByType)
                )
            )
        );
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

        if (empty($productsIdsTemp)) {
            $messages[] = array(
                'type' => 'warning',
                'text' => '<p>' . Mage::helper('M2ePro')->__(
                    'Description Policy cannot be unassigned from some Products because they are
                     participating in the new ASIN(s)/ISBN(s) creation.'
                ) . '</p>'
            );
        } else {
            $productsIdsLocked = $this->filterLockedProducts($productsIdsTemp);

            if (count($productsIdsLocked) < count($productsIds)) {
                $messages[] = array(
                    'type' => 'warning',
                    'text' => '<p>' . Mage::helper('M2ePro')->__(
                        'Description Policy cannot be unassigned because the Products are in Action or
                         in the process of new ASIN(s)/ISBN(s) Creation.'
                    ). '</p>'
                );
            }
        }

        if (!empty($productsIdsLocked)) {
            $messages[] = array(
                'type' => 'success',
                'text' => Mage::helper('M2ePro')->__('Description Policy was unassigned.')
            );

            $this->setDescriptionTemplateFroProductsByChunks($productsIdsLocked, null);
            $this->runProcessorForParents($productsIdsLocked);
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'messages' => $messages
                )
            )
        );
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
            ->createBlock('M2ePro/adminhtml_amazon_listing_template_description_grid');
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
                    'Selected action was not completed for one or more Items. Description Policy cannot be assigned
                    to Simple with Custom Options, Bundle and Downloadable with Separated Links Magento Products.'
                )
            );
        }

        if (empty($filteredProductsIdsByType)) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                    'messages' => $messages
                    )
                )
            );
        }

        $mainBlock = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_listing_template_description_main');
        if (!empty($messages)) {
            $mainBlock->setMessages($messages);
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'data' => $mainBlock->toHtml(),
                'messages' => $messages,
                'products_ids' => implode(',', $filteredProductsIdsByType)
                )
            )
        );
    }

    // ---------------------------------------

    public function getDescriptionTemplatesListAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id', '');
        $isNewAsinAccepted = $this->getRequest()->getParam('is_new_asin_accepted', 0);

        /** @var Ess_M2ePro_Model_Resource_Amazon_Template_Description_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Template_Description');

        $marketplaceId != '' && $collection->addFieldToFilter('marketplace_id', $marketplaceId);

        $descriptionTemplates = $collection->getData();
        if ($isNewAsinAccepted == 1) {
            usort(
                $descriptionTemplates, function($a, $b)
                {
                return $a["is_new_asin_accepted"] < $b["is_new_asin_accepted"];
                }
            );
        }

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($descriptionTemplates));
    }

    //########################################

    public function viewTemplateShippingPopupAction()
    {
        $productsIds  = $this->getRequest()->getParam('products_ids');

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
                    'The Shipping Policy was not assigned because the Products have In Action Status.'
                ). '</p>'
            );
        }

        if (empty($productsIdsLocked)) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                    'messages' => $messages
                    )
                )
            );
        }

        $mainBlock = $this->loadLayout()->getLayout()
                          ->createBlock('M2ePro/adminhtml_amazon_listing_template_shipping');

        if (!empty($messages)) {
            $mainBlock->setMessages($messages);
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'data' => $mainBlock->toHtml(),
                'messages' => $messages,
                'products_ids' => implode(',', $productsIdsLocked)
                )
            )
        );
    }

    public function viewTemplateShippingGridAction()
    {
        $productsIds   = $this->getRequest()->getParam('products_ids');
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

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $grid = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_template_shipping_grid');
        $grid->setMarketplaceId($marketplaceId);
        $grid->setProductsIds($productsIds);

        return $this->getResponse()->setBody($grid->toHtml());
    }

    // ---------------------------------------

    public function assignShippingTemplateAction()
    {
        $productsIds  = $this->getRequest()->getParam('products_ids');
        $templateId   = $this->getRequest()->getParam('template_id');

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
                    'Shipping Policy cannot be assigned to some Products
                         because the Products are in Action'
                ). '</p>'
            );
        }

        if (!empty($productsIdsLocked)) {
            $messages[] = array(
                'type' => 'success',
                'text' => Mage::helper('M2ePro')->__('Shipping Policy was assigned.')
            );

            $this->setShippingTemplateForProducts($productsIdsLocked, $templateId);
            $this->runProcessorForParents($productsIdsLocked);
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'messages' => $messages
                )
            )
        );
    }

    public function unassignShippingTemplateAction()
    {
        $productsIds  = $this->getRequest()->getParam('products_ids');

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
                    'Shipping Policy cannot be unassigned from some Products
                         because the Products are in Action'
                ). '</p>'
            );
        }

        if (!empty($productsIdsLocked)) {
            $messages[] = array(
                'type' => 'success',
                'text' => Mage::helper('M2ePro')->__('Shipping Policy was unassigned.')
            );

            $this->setShippingTemplateForProducts($productsIdsLocked, null);
            $this->runProcessorForParents($productsIdsLocked);
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'messages' => $messages
                )
            )
        );
    }

    //########################################

    protected function getLogsAction($action)
    {
        switch ($action) {
            case Ess_M2ePro_Model_Listing_Product::ACTION_LIST:
                return Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT;

            case Ess_M2ePro_Model_Listing_Product::ACTION_RELIST:
                return Ess_M2ePro_Model_Listing_Log::ACTION_RELIST_PRODUCT_ON_COMPONENT;

            case Ess_M2ePro_Model_Listing_Product::ACTION_REVISE:
                return Ess_M2ePro_Model_Listing_Log::ACTION_REVISE_PRODUCT_ON_COMPONENT;

            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:
                return Ess_M2ePro_Model_Listing_Log::ACTION_STOP_PRODUCT_ON_COMPONENT;

            case Ess_M2ePro_Model_Listing_Product::ACTION_DELETE:
                return Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_COMPONENT;
        }

        throw new Ess_M2ePro_Model_Exception_Logic('Unknown action.');
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
        $magentoViewMode = Ess_M2ePro_Block_Adminhtml_Amazon_Listing_View::VIEW_MODE_MAGENTO;
        $sessionParamName = Mage::getBlockSingleton('M2ePro/Adminhtml_Amazon_Listing_View')->getId()
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
        } elseif ($ruleParam !== null) {
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
        $prefix .= $id === null ? 'add' : $id;
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
                $productsIds = array_merge(
                    $productsIds,
                    $this->filterProductsForMapOrUnmapDescriptionTemplate($productsIdsParamChunk)
                );
            }
        } else {
            $productsIds = $this->filterProductsForMapOrUnmapDescriptionTemplate($productsIdsParam);
        }

        return $productsIds;
    }

    protected function filterProductsForMapOrUnmapDescriptionTemplate($productsIds)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableAmazonListingProduct = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_amazon_listing_product');

        $select = $connRead->select();

        // selecting all except parents general_id owners or simple general_id owners without general_id
        $select->from($tableAmazonListingProduct, 'listing_product_id')
            ->where(
                'is_general_id_owner = 0
                OR (is_general_id_owner = 1
                    AND is_variation_parent = 0 AND general_id IS NOT NULL)'
            );

        $select->where('listing_product_id IN (?)', $productsIds);

        $result = Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($select);

        return $result;
    }

    public function filterLockedProducts($productsIdsParam)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_processing_lock');

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
        if (empty($productsIds)) {
            return;
        }

        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->addFieldToFilter('id', array('in' => $productsIds));
        // ---------------------------------------

        if ($collection->getSize() == 0) {
            return;
        }

        $transaction = Mage::getModel('core/resource_transaction');
        $oldTemplateIds = array();

        try {
            foreach ($collection->getItems() as $listingProduct) {
                /**@var Ess_M2ePro_Model_Listing_Product $listingProduct */

                $oldTemplateIds[$listingProduct->getId()] = $listingProduct->getData('template_description_id');

                $listingProduct->setData('template_description_id', $templateId);
                $transaction->addObject($listingProduct);
            }

            $transaction->save();
        } catch (Exception $e) {
            $oldTemplateIds = false;
            $transaction->rollback();
        }

        if (!$oldTemplateIds) {
            return;
        }

        $newTemplate = Mage::getModel('M2ePro/Amazon_Template_Description')->load($templateId);

        if ($newTemplate->getId()) {
            $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Template_Description_SnapshotBuilder');
            $snapshotBuilder->setModel($newTemplate->getParentObject());
            $newSnapshot = $snapshotBuilder->getSnapshot();
        } else {
            $newSnapshot = array();
        }

        foreach ($collection->getItems() as $listingProduct) {
            /**@var Ess_M2ePro_Model_Listing_Product $listingProduct */

            $oldTemplate = Mage::getModel('M2ePro/Amazon_Template_Description')->load(
                $oldTemplateIds[$listingProduct->getId()]
            );

            if ($oldTemplate->getId()) {
                $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Template_Description_SnapshotBuilder');
                $snapshotBuilder->setModel($oldTemplate->getParentObject());
                $oldSnapshot = $snapshotBuilder->getSnapshot();
            } else {
                $oldSnapshot = array();
            }

            if (empty($newSnapshot) && empty($oldSnapshot)) {
                continue;
            }

            $diff = Mage::getModel('M2ePro/Amazon_Template_Description_Diff');
            $diff->setOldSnapshot($oldSnapshot);
            $diff->setNewSnapshot($newSnapshot);

            $changeProcessor = Mage::getModel('M2ePro/Amazon_Template_Description_ChangeProcessor');
            $changeProcessor->process(
                $diff, array(array('id' => $listingProduct->getId(), 'status' => $listingProduct->getStatus()))
            );
        }
    }

    //########################################

    protected function setShippingTemplateForProducts($productsIds, $templateId)
    {
        if (empty($productsIds)) {
            return;
        }

        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->addFieldToFilter('id', array('in' => $productsIds));
        // ---------------------------------------

        if ($collection->getSize() == 0) {
            return;
        }

        $transaction = Mage::getModel('core/resource_transaction');
        $oldTemplateIds = array();

        try {
            foreach ($collection->getItems() as $listingProduct) {
                /**@var Ess_M2ePro_Model_Listing_Product $listingProduct */

                $oldTemplateIds[$listingProduct->getId()] = $listingProduct->getData('template_shipping_id');

                $listingProduct->setData('template_shipping_id', $templateId);
                $transaction->addObject($listingProduct);
            }

            $transaction->save();
        } catch (Exception $e) {
            $oldTemplateIds = false;
            $transaction->rollback();
        }

        if (!$oldTemplateIds) {
            return;
        }

        $newTemplate = Mage::getModel('M2ePro/Amazon_Template_Shipping')->load($templateId);

        if ($newTemplate->getId()) {
            $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Template_Shipping_SnapshotBuilder');
            $snapshotBuilder->setModel($newTemplate);
            $newSnapshot = $snapshotBuilder->getSnapshot();
        } else {
            $newSnapshot = array();
        }

        foreach ($collection->getItems() as $listingProduct) {
            /**@var Ess_M2ePro_Model_Listing_Product $listingProduct */

            $oldTemplate = Mage::getModel('M2ePro/Amazon_Template_Shipping')->load(
                $oldTemplateIds[$listingProduct->getId()]
            );

            if ($oldTemplate->getId()) {
                $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Template_Shipping_SnapshotBuilder');
                $snapshotBuilder->setModel($oldTemplate);
                $oldSnapshot = $snapshotBuilder->getSnapshot();
            } else {
                $oldSnapshot = array();
            }

            if (empty($newSnapshot) && empty($oldSnapshot)) {
                continue;
            }

            $diff = Mage::getModel('M2ePro/Amazon_Template_Shipping_Diff');
            $diff->setOldSnapshot($oldSnapshot);
            $diff->setNewSnapshot($newSnapshot);

            $changeProcessor = Mage::getModel('M2ePro/Amazon_Template_Shipping_ChangeProcessor');
            $changeProcessor->process(
                $diff, array(array('id' => $listingProduct->getId(), 'status' => $listingProduct->getStatus()))
            );
        }
    }

    //########################################

    protected function processSellingFormatTemplateChange(
        array $oldData,
        array $newData,
        array $affectedListingsProductsData
    ) {
        if (empty($affectedListingsProductsData) ||
            empty($oldData['template_selling_format_id']) || empty($newData['template_selling_format_id'])) {
            return;
        }

        $oldTemplate = Mage::helper('M2ePro/Component_Amazon')->getObject(
            'Template_SellingFormat', $oldData['template_selling_format_id']
        );
        $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Template_SellingFormat_SnapshotBuilder');
        $snapshotBuilder->setModel($oldTemplate);
        $oldSnapshot = $snapshotBuilder->getSnapshot();

        $newTemplate = Mage::helper('M2ePro/Component_Amazon')->getObject(
            'Template_SellingFormat', $newData['template_selling_format_id']
        );
        $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Template_SellingFormat_SnapshotBuilder');
        $snapshotBuilder->setModel($newTemplate);
        $newSnapshot = $snapshotBuilder->getSnapshot();

        $diff = Mage::getModel('M2ePro/Amazon_Template_SellingFormat_Diff');
        $diff->setNewSnapshot($newSnapshot);
        $diff->setOldSnapshot($oldSnapshot);

        $changeProcessor = Mage::getModel('M2ePro/Amazon_Template_SellingFormat_ChangeProcessor');
        $changeProcessor->process($diff, $affectedListingsProductsData);
    }

    protected function processSynchronizationTemplateChange(
        array $oldData,
        array $newData,
        array $affectedListingsProductsData
    ) {
        if (empty($affectedListingsProductsData) ||
            empty($oldData['template_synchronization_id']) || empty($newData['template_synchronization_id'])) {
            return;
        }

        $oldTemplate = Mage::helper('M2ePro/Component_Amazon')->getObject(
            'Template_Synchronization', $oldData['template_synchronization_id']
        );
        $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Template_Synchronization_SnapshotBuilder');
        $snapshotBuilder->setModel($oldTemplate);
        $oldSnapshot = $snapshotBuilder->getSnapshot();

        $newTemplate = Mage::helper('M2ePro/Component_Amazon')->getObject(
            'Template_Synchronization', $newData['template_synchronization_id']
        );
        $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Template_Synchronization_SnapshotBuilder');
        $snapshotBuilder->setModel($newTemplate);
        $newSnapshot = $snapshotBuilder->getSnapshot();

        $diff = Mage::getModel('M2ePro/Amazon_Template_Synchronization_Diff');
        $diff->setNewSnapshot($newSnapshot);
        $diff->setOldSnapshot($oldSnapshot);

        $changeProcessor = Mage::getModel('M2ePro/Amazon_Template_Synchronization_ChangeProcessor');
        $changeProcessor->process($diff, $affectedListingsProductsData);
    }

    protected function processShippingTemplateChange(
        array $oldData,
        array $newData,
        array $affectedListingsProductsData
    ) {
        if (empty($affectedListingsProductsData) ||
            empty($oldData['template_shipping_id']) &&
            empty($newData['template_shipping_id'])) {
            return;
        }

        $oldTemplate = Mage::getModel('M2ePro/Amazon_Template_Shipping');
        if (!empty($oldData['template_shipping_id'])) {
            $oldTemplate = $oldTemplate->load($oldData['template_shipping_id']);
        }

        $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Template_Shipping_SnapshotBuilder');
        $snapshotBuilder->setModel($oldTemplate);
        $oldSnapshot = $snapshotBuilder->getSnapshot();

        $newTemplate = Mage::getModel('M2ePro/Amazon_Template_Shipping');
        if (!empty($newData['template_shipping_id'])) {
            $newTemplate = $oldTemplate->load($newData['template_shipping_id']);
        }

        $snapshotBuilder = Mage::getModel('M2ePro/Amazon_Template_Shipping_SnapshotBuilder');
        $snapshotBuilder->setModel($newTemplate);
        $newSnapshot = $snapshotBuilder->getSnapshot();

        $diff = Mage::getModel('M2ePro/Amazon_Template_Shipping_Diff');
        $diff->setNewSnapshot($newSnapshot);
        $diff->setOldSnapshot($oldSnapshot);

        $changeProcessor = Mage::getModel('M2ePro/Amazon_Template_Shipping_ChangeProcessor');
        $changeProcessor->process($diff, $affectedListingsProductsData);
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
        $tableAmazonListingProduct = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_amazon_listing_product');

        $connWrite->update(
            $tableAmazonListingProduct, array(
                'is_general_id_owner' => Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES
            ), '`listing_product_id` IN ('.implode(',', $productsIds).')'
        );
    }

    //########################################

    protected function runProcessorForParents($productsIds)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableAmazonListingProduct = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_amazon_listing_product');

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
