<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Walmart_ListingController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
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
            ->addJs('M2ePro/Walmart/Listing/AutoAction.js')

            ->addJs('M2ePro/Grid.js')
            ->addJs('M2ePro/Listing/EditListingTitle.js')
            ->addJs('M2ePro/Listing/Grid.js')
            ->addJs('M2ePro/Listing/Other.js')
            ->addJs('M2ePro/Listing/Other/Grid.js')

            ->addJs('M2ePro/Action.js')
            ->addJs('M2ePro/Listing/Action.js')
            ->addJs('M2ePro/Listing/Moving.js')
            ->addJs('M2ePro/Listing/Mapping.js')
            ->addJs('M2ePro/Walmart/Listing.js')
            ->addJs('M2ePro/Walmart/Listing/Grid.js')
            ->addJs('M2ePro/Walmart/Listing/Action.js')
            ->addJs('M2ePro/Walmart/Listing/Template/Category.js')
            ->addJs('M2ePro/Walmart/Listing/VariationProductManage.js')
            ->addJs('M2ePro/Walmart/Listing/Other/Grid.js')
            ->addJs('M2ePro/Walmart/Listing/Product/EditChannelData.js')

            ->addJs('M2ePro/TemplateManager.js')
            ->addJs('M2ePro/Walmart/Listing/Settings.js')
            ->addJs('M2ePro/Walmart/Listing/ProductsFilter.js')

            ->addJs('M2ePro/Walmart/Listing/Product/Variation.js')

            ->addJs('M2ePro/Listing/Other/AutoMapping.js')
            ->addJs('M2ePro/Listing/Other/Removing.js')
            ->addJs('M2ePro/Listing/Other/Unmapping.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "walmart-integration");

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Walmart::MENU_ROOT_NODE_NICK . '/listings'
        );
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_walmart_manageListings',
                    '',
                    array(
                        'tab' => $this->getRequest()->getParam(
                            'tab',
                            Ess_M2ePro_Block_Adminhtml_Walmart_ManageListings::TAB_ID_LISTING
                        )
                    )
                )
            )
            ->renderLayout();
    }

    public function listingGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function getListingTabAction()
    {
        /** @var $block Ess_M2ePro_Block_Adminhtml_Walmart_Listing */
        $block = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_manageListings',
            '',
            array('tab' => Ess_M2ePro_Block_Adminhtml_Walmart_ManageListings::TAB_ID_LISTING)
        );
        $this->_initAction()->_addContent($block)->renderLayout();
    }

    public function getListingOtherTabAction()
    {
        /** @var $block Ess_M2ePro_Block_Adminhtml_Walmart_Listing */
        $block = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_manageListings',
            '',
            array('tab' => Ess_M2ePro_Block_Adminhtml_Walmart_ManageListings::TAB_ID_LISTING_OTHER)
        );
        $this->_initAction()->_addContent($block)->renderLayout();
    }

    public function getSearchTabAction()
    {
        /** @var $block Ess_M2ePro_Block_Adminhtml_Walmart_Listing */
        $block = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_manageListings',
            '',
            array('tab' => Ess_M2ePro_Block_Adminhtml_Walmart_ManageListings::TAB_ID_SEARCH)
        );
        $this->_initAction()->_addContent($block)->renderLayout();
    }

    //########################################

    public function searchAction()
    {
        /** @var $block Ess_M2ePro_Block_Adminhtml_Walmart_Listing */
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_search');
        $this->_initAction()->_addContent($block)->renderLayout();
    }

    public function searchGridAction()
    {
        $listingType = $this->getRequest()->getParam('listing_type', false);
        $gridBlock = $listingType == Ess_M2ePro_Block_Adminhtml_Listing_Search_Switcher::LISTING_TYPE_LISTING_OTHER
            ? $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_search_other_grid')
            : $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_search_m2ePro_grid');

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
            $model = Mage::helper('M2ePro/Component_Walmart')->getCachedObject('Listing', $id);
        } catch (LogicException $e) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_walmart_listing/index');
        }

        $listingProductsIds = $model->getSetting('additional_data', 'adding_listing_products_ids');

        if (!empty($listingProductsIds)) {
            $this->_getSession()->addNotice(
                Mage::helper('M2ePro')->__(
                    'Please make sure you finish adding new Products before moving to the next step.'
                )
            );

            return $this->_redirect('*/adminhtml_walmart_listing_productAdd/index', array('id' => $id, 'step' => 3));
        }

        // Check listing lock object
        // ---------------------------------------
        if ($model->isSetProcessingLock('products_in_action')) {
            $this->_getSession()->addNotice(
                Mage::helper('M2ePro')->__('Some Walmart request(s) are being processed now.')
            );
        }

        // ---------------------------------------

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

        // Set rule model
        // ---------------------------------------
        $this->setRuleData('walmart_rule_listing_view');
        // ---------------------------------------

        $this->_initAction();
        $this->setPageHelpLink(null, null, "walmart-integration");

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_view'))
            ->renderLayout();
    }

    public function viewGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Walmart')->getCachedObject('Listing', $id);

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

        // Set rule model
        // ---------------------------------------
        $this->setRuleData('walmart_rule_listing_view');
        // ---------------------------------------

        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_walmart_listing_view')->getGridHtml();
        $this->getResponse()->setBody($response);
    }

    //########################################

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $listing = Mage::helper('M2ePro/Component_Walmart')->getCachedObject('Listing', $id);

        if (!$listing->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_walmart_listing/index');
        }

        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_edit'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            $this->_redirect('*/adminhtml_walmart_listing/index');
        }

        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Walmart')->getModel('Listing')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_walmart_listing/index');
        }

        $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Listing_SnapshotBuilder');
        $snapshotBuilder->setModel($model);

        $oldData = $snapshotBuilder->getSnapshot();

        $data = array();
        $keys = array(
            'template_selling_format_id',
            'template_description_id',
            'template_synchronization_id',
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $model->addData($data)->save();

        $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Listing_SnapshotBuilder');
        $snapshotBuilder->setModel($model);

        $newData = $snapshotBuilder->getSnapshot();

        $affectedListingsProducts = Mage::getModel('M2ePro/Walmart_Listing_AffectedListingsProducts');
        $affectedListingsProducts->setModel($model);

        $affectedListingsProductsData = $affectedListingsProducts->getData(
            array('id', 'status'), array('only_physical_units' => true)
        );

        $this->processDescriptionTemplateChange($oldData, $newData, $affectedListingsProductsData);
        $this->processSellingFormatTemplateChange($oldData, $newData, $affectedListingsProductsData);
        $this->processSynchronizationTemplateChange($oldData, $newData, $affectedListingsProductsData);

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
            $listing = Mage::helper('M2ePro/Component_Walmart')->getCachedObject('Listing', $id);
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
            return $this->getResponse()->setBody('You should select Products');
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $productsCollection */
        $productsCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $productsCollection->addFieldToFilter('id', explode(',', $listingsProductsIds));

        /** @var Ess_M2ePro_Model_Listing_Product[] $listingsProducts */
        $listingsProducts = $productsCollection->getItems();

        $childListingsProducts = array();

        foreach ($listingsProducts as $index => $listingProduct) {
            /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
            $walmartListingProduct = $listingProduct->getChildObject();

            if (!$walmartListingProduct->getVariationManager()->isRelationParentType()) {
                continue;
            }

            $tempChildListingsProducts = $walmartListingProduct->getVariationManager()
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
        $logsActionId = Mage::getResourceModel('M2ePro/Listing_Log')->getNextActionId();

        $this->checkLocking($listingsProducts, $logsActionId, $action);
        if (empty($listingsProducts)) {
            return Mage::helper('M2ePro')->jsonEncode(array('result' => 'error', 'action_id' => $logsActionId));
        }

        $this->createUpdateScheduledActions(
            $listingsProducts,
            $action,
            $params
        );

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

    protected function createUpdateScheduledActionsDataCallback($listingProduct, $action, array $params)
    {
        $tag = null;
        $params['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER;
        $additionalData = array('params' => $params,);

        if ($action == Ess_M2ePro_Model_Listing_Product::ACTION_REVISE) {
            /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_Configurator $configurator */
            $configurator = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Configurator');
            $configurator->enableAll();
            $tag = '/qty/lag_time/price/promotions/details/';

            $additionalData['configurator'] = $configurator->getData();
        }

        return array(
            'listing_product_id' => $listingProduct->getId(),
            'component'          => Ess_M2ePro_Helper_Component_Walmart::NICK,
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
        $productsCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
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
                /** @var Ess_M2ePro_Model_Walmart_Listing_Product_RemoveHandler $removeHandler */
                $removeHandler = Mage::getModel(
                    'M2ePro/Walmart_Listing_Product_RemoveHandler', array('listing_product' => $listingProduct)
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
            return $this->getResponse()->setBody('You should select Products');
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $productsCollection */
        $productsCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $productsCollection->addFieldToFilter('id', explode(',', $listingsProductsIds));

        /** @var Ess_M2ePro_Model_Listing_Product[] $listingsProducts */
        $listingsProducts = $productsCollection->getItems();
        $logsActionId = Mage::getResourceModel('M2ePro/Listing_Log')->getNextActionId();

        /** @var Ess_M2ePro_Model_Listing_Product[] $parentListingsProducts */
        $parentListingsProducts = array();
        /** @var Ess_M2ePro_Model_Listing_Product[] $childListingsProducts */
        $childListingsProducts  = array();

        foreach ($listingsProducts as $index => $listingProduct) {
            /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
            $walmartListingProduct = $listingProduct->getChildObject();

            if (!$walmartListingProduct->getVariationManager()->isRelationParentType()) {
                $lockManager = Mage::getModel(
                    'M2ePro/Listing_Product_LockManager', array('listing_product' => $listingProduct)
                );
                $lockManager->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);
                $lockManager->setLogsActionId($logsActionId);
                $lockManager->setLogsAction(Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_COMPONENT);

                if ($lockManager->checkLocking()) {
                    unset($listingsProducts[$index]);
                }

                continue;
            }

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent $typeModel */
            $typeModel = $walmartListingProduct->getVariationManager()->getTypeModel();

            $tempChildListingsProducts = $typeModel->getChildListingsProducts();

            $isParentLocked = false;

            foreach ($tempChildListingsProducts as $tempChildListingProduct) {
                $lockManager = Mage::getModel(
                    'M2ePro/Listing_Product_LockManager', array('listing_product' => $tempChildListingProduct)
                );
                $lockManager->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);
                $lockManager->setLogsActionId($logsActionId);
                $lockManager->setLogsAction(Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_COMPONENT);

                if ($lockManager->checkLocking()) {
                    $isParentLocked = true;
                    break;
                }
            }

            unset($listingsProducts[$index]);

            if (!$isParentLocked) {
                $childListingsProducts = array_merge($childListingsProducts, $tempChildListingsProducts);
                $parentListingsProducts[$index] = $listingProduct;
            }
        }

        $listingsProducts = array_merge($listingsProducts, $childListingsProducts);

        if (empty($listingsProducts) && empty($parentListingsProducts)) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(array('result'=>'error', 'action_id' => $logsActionId))
            );
        }

        $dispatcher = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');

        foreach ($listingsProducts as $listingProduct) {
            /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
            $walmartListingProduct = $listingProduct->getChildObject();

            if (!$listingProduct->isNotListed()) {
                $connector = $dispatcher->getVirtualConnector(
                    'product', 'retire', 'entity',
                    array('sku' => $walmartListingProduct->getSku()),
                    null, $listingProduct->getAccount()
                );

                try {
                    $dispatcher->process($connector);
                } catch (Exception $exception) {
                    Mage::helper('M2ePro/Module_Exception')->process($exception);
                }
            }

            $removeHandler = Mage::getModel(
                'M2ePro/Walmart_Listing_Product_RemoveHandler', array('listing_product' => $listingProduct)
            );
            $removeHandler->process();
        }

        foreach ($parentListingsProducts as $parentListingProduct) {
            $removeHandler = Mage::getModel(
                'M2ePro/Walmart_Listing_Product_RemoveHandler', array('listing_product' => $parentListingProduct)
            );
            $removeHandler->process();
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array('result'  => 'success')
            )
        );
    }

    //########################################

    public function runResetProductsAction()
    {
        if ((!$productsIds = $this->getRequest()->getParam('selected_products'))) {
            return $this->getResponse()->setBody('You should select Products');
        }

        $listingsProductsIds = explode(',', $productsIds);

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $childProducts */
        $childProducts = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $childProducts->addFieldToFilter('variation_parent_id', $listingsProductsIds);
        $childProducts->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $childProducts->getSelect()->columns(array('second_table.listing_product_id'));

        $childProductsIds = $childProducts->getColumnValues('listing_product_id');

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingsProducts */
        $listingsProducts = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $listingsProducts->addFieldToFilter('listing_product_id', array_merge($childProductsIds, $listingsProductsIds));

        if (!$listingsProducts->getSize()) {
            return $this->getResponse()->setBody('No products provided.');
        }

        $result = 'success';
        $logsActionId = Mage::getResourceModel('M2ePro/Listing_Log')->getNextActionId();

        $logger = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Logger');
        $logger->setActionId($logsActionId);
        $logger->setAction(Ess_M2ePro_Model_Listing_Log::ACTION_RESET_BLOCKED_PRODUCT);
        $logger->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);

        $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');

        $instructionsData = array();
        foreach ($listingsProducts->getItems() as $index => $listingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */

            $isVariationParent = (bool)$listingProduct->getChildObject()->getData('is_variation_parent');
            if (!$isVariationParent && ($listingProduct->getChildObject()->isOnlinePriceInvalid() ||
                    $listingProduct->getStatus() != Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED)) {
                $result = 'error';

                $message->initFromPreparedData(
                    'Item cannot be reset. Most probably it is not blocked or requires a price adjusting.',
                    Ess_M2ePro_Model_Response_Message::TYPE_ERROR
                );

                $logger->logListingProductMessage($listingProduct, $message);
                continue;
            }

            $lockManager = Mage::getModel(
                'M2ePro/Listing_Product_LockManager', array('listing_product' => $listingProduct)
            );

            $lockManager->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);
            $lockManager->setLogsActionId($logsActionId);
            $lockManager->setLogsAction(Ess_M2ePro_Model_Listing_Log::ACTION_RESET_BLOCKED_PRODUCT);

            if ($lockManager->checkLocking()) {
                $result = 'warning';
                continue;
            }

            $instructionsData[] = array(
                'listing_product_id' => $index,
                'component'          => Ess_M2ePro_Helper_Component_Walmart::NICK,
                'type'               => Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_ADDED,
                'initiator'          => Ess_M2ePro_Model_Listing::INSTRUCTION_INITIATOR_ADDING_PRODUCT,
                'priority'           => 30,
            );

            $listingProduct->addData(
                array(
                'status'                  => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED,
                'online_qty'              => null,
                'online_price'            => null,
                'online_promotions'       => null,
                'online_details'          => null,
                'is_online_price_invalid' => 0,
                'status_change_reasons'   => null,
                'is_missed_on_channel'    => 0,
                )
            );

            $message->initFromPreparedData(
                'Item has been reset and is no longer blocked.',
                Ess_M2ePro_Model_Response_Message::TYPE_SUCCESS
            );
            $logger->logListingProductMessage($listingProduct, $message);

            $listingProduct->save();

            if ($listingProduct->getChildObject()->getVariationManager()->isRelationChildType()) {
                /** @var Ess_M2ePro_Model_Listing_Product $parentListingProduct */
                $parentListingProduct = $listingProduct->getChildObject()->getVariationManager()->getTypeModel()
                    ->getParentListingProduct();

                /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent $parentType */
                $parentType = $parentListingProduct->getChildObject()->getVariationManager()->getTypeModel();
                $parentType->getProcessor()->process();
            }
        }

        Mage::getResourceModel('M2ePro/Listing_Product_Instruction')->add($instructionsData);

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'result'    => $result,
                'action_id' => $logsActionId
                )
            )
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
            'M2ePro/adminhtml_walmart_listing_product_variation_edit', '',
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
            'M2ePro/adminhtml_walmart_listing_product_variation_manage', '',
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
            Ess_M2ePro_Helper_Component_Walmart::NICK, 'Listing_Product', $listingProductId
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
            Ess_M2ePro_Helper_Component_Walmart::NICK, 'Listing_Product', $listingProductId
        );

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager $variationManager */
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

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager $listingProductManager */
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
            Ess_M2ePro_Helper_Component_Walmart::NICK, 'Listing_Product', $listingProductId
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
                        'Mode of working with Magento Variational Product 
                        has been switched to work with Parent-Child Product.'
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
            Ess_M2ePro_Helper_Component_Walmart::NICK, 'Listing_Product', $listingProductId
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
            ->getComponentCollection(Ess_M2ePro_Helper_Component_Walmart::NICK, 'Listing_Product')
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
                Ess_M2ePro_Helper_Component_Walmart::NICK, 'Listing_Product', $listingProductId
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
        $duplicatedListingProduct->setData(
            'template_category_id', $listingProduct->getChildObject()->getTemplateCategoryId()
        );
        $duplicatedListingProduct->save();

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

    public function mapToTemplateCategoryAction()
    {
        $productsIds = $this->getRequest()->getParam('products_ids');
        $templateId = $this->getRequest()->getParam('template_id');

        if (empty($productsIds) || empty($templateId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $msgType = 'success';
        $messages = array();

        $this->setCategoryTemplateFroProductsByChunks($productsIds, $templateId);

        $messages[] = Mage::helper('M2ePro')->__(
            'Category Policy was assigned to %count% Products',
            count($productsIds)
        );

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'type' => $msgType,
                'messages' => $messages,
                'products_ids' => implode(',', $productsIds)
                )
            )
        );
    }

    public function unmapFromTemplateCategoryAction()
    {
        $productsIds = $this->getRequest()->getParam('products_ids');

        if (empty($productsIds)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $productsIdsTemp = $this->filterProductsForMapOrUnmapCategoryTemplateByChunks($productsIds);

        $messages = array();

        if (empty($productsIdsTemp)) {
            $messages[] = array(
                'type' => 'warning',
                'text' => '<p>' . Mage::helper('M2ePro')->__(
                    'Category Policy cannot be unassigned from some Products because they are
                     participating in the new Product Type(s) creation.'
                ) . '</p>'
            );
        } else {
            $productsIdsLocked = $this->filterLockedProducts($productsIdsTemp);

            if (count($productsIdsLocked) < count($productsIds)) {
                $messages[] = array(
                    'type' => 'warning',
                    'text' => '<p>' . Mage::helper('M2ePro')->__(
                        'Category Policy cannot be unassigned because the Products are in Action or
                         in the process of new Product Type(s) Creation.'
                    ). '</p>'
                );
            }
        }

        if (!empty($productsIdsLocked)) {
            $messages[] = array(
                'type' => 'success',
                'text' => Mage::helper('M2ePro')->__('Category Policy was unassigned.')
            );

            $this->setCategoryTemplateFroProductsByChunks($productsIdsLocked, null);
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

    public function viewTemplateCategoriesGridAction()
    {
        $productsIds = $this->getRequest()->getParam('products_ids');

        if (empty($productsIds)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $grid = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_walmart_listing_template_category_grid');
        $grid->setProductsIds($productsIds);

        return $this->getResponse()->setBody($grid->toHtml());
    }

    // ---------------------------------------

    public function validateProductsForTemplateCategoryAssignAction()
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

        if (count($productsIds) != count($productsIdsLocked)) {
            $messages[] = array(
                'type' => 'warning',
                'text' => Mage::helper('M2ePro')->__(
                    'Category Policy cannot be assigned because the Products are in Action.'
                )
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
            ->createBlock('M2ePro/adminhtml_walmart_listing_template_category_main');
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

    // ---------------------------------------

    public function getCategoryTemplatesListAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id', '');

        /** @var Ess_M2ePro_Model_Resource_Walmart_Template_Category_Collection $collection */
        $collection = Mage::getModel('M2ePro/Walmart_Template_Category')->getCollection();

        $marketplaceId != '' && $collection->addFieldToFilter('marketplace_id', $marketplaceId);

        $categoryTemplates = $collection->getData();

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($categoryTemplates));
    }

    //########################################

    public function getEditSkuPopupAction()
    {
        $mainBlock = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_walmart_listing_view_walmart_sku_main');

        return $this->getResponse()->setBody($mainBlock->toHtml());
    }

    public function editSkuAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $value = $this->getRequest()->getParam('value');

        if (empty($productId) || empty($value)) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'result' => false,
                        'message' => Mage::helper('M2ePro')->__('Wrong parameters.')
                    )
                )
            );
        }

        $listingProduct = Mage::getModel('M2ePro/Walmart_Listing_Product')->load($productId);

        if (!$listingProduct->getId()) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'result' => false,
                        'message' => Mage::helper('M2ePro')->__('Listing product does not exist.')
                    )
                )
            );
        }

        $lockManager = Mage::getModel(
            'M2ePro/Listing_Product_LockManager', array('listing_product' => $listingProduct)
        );
        $lockManager->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);
        $lockManager->setLogsAction($this->getLogsAction(Ess_M2ePro_Model_Listing_Product::ACTION_REVISE));

        if ($lockManager->checkLocking()) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'result'  => false,
                        'message' => Mage::helper('M2ePro')->__(
                            'Another Action is being processed. Try again when the Action is completed.'
                        )
                    )
                )
            );
        }

        $oldSku = $listingProduct->getData('sku');
        if ($oldSku === $value) {
            $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'result' => true,
                        'message' => ''
                    )
                )
            );
        }

        $configurator = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Configurator');
        $configurator->disableAll();
        $configurator->allowDetails();

        $scheduledAction = Mage::getModel('M2ePro/Listing_Product_ScheduledAction');
        $scheduledAction->setData(
            array(
                'listing_product_id' => $listingProduct->getId(),
                'component'          => Ess_M2ePro_Helper_Component_Walmart::NICK,
                'action_type'        => Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                'is_force'           => true,
                'tag'                => '/details/',
                'additional_data'    => Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'params' => array(
                            'changed_sku'    => $value,
                            'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER
                        ),
                        'configurator' => $configurator->getData(),
                    )
                ),
            )
        );

        /** @var Ess_M2ePro_Model_Listing_Product_ScheduledAction $existedScheduledAction */
        $existedScheduledAction = Mage::getModel('M2ePro/Listing_Product_ScheduledAction')->load(
            $listingProduct->getId(), 'listing_product_id'
        );

        $scheduledActionManager = Mage::getModel('M2ePro/Listing_Product_ScheduledAction_Manager');
        if ($existedScheduledAction->getId()) {
            $scheduledActionManager->updateAction($scheduledAction);
        } else {
            $scheduledActionManager->addAction($scheduledAction);
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                    'result' => true,
                    'message' => ''
                )
            )
        );
    }

    // ---------------------------------------

    public function getEditIdentifiersPopupAction()
    {
        $mainBlock = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_walmart_listing_view_walmart_identifiers_main');

        return $this->getResponse()->setBody($mainBlock->toHtml());
    }

    public function editIdentifierAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $type = $this->getRequest()->getParam('type');
        $value = $this->getRequest()->getParam('value');

        $allowedTypes = array('gtin', 'upc', 'ean', 'isbn');

        if (empty($productId) || empty($type) || empty($value) || !in_array($type, $allowedTypes)) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'result' => false,
                        'message' => Mage::helper('M2ePro')->__('Wrong parameters.')
                    )
                )
            );
        }

        if (!Mage::helper('M2ePro')->isValidIdentifier($value, strtoupper($type))) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'result' => false,
                        'message' => Mage::helper('M2ePro')->__('The product Identifier has incorrect format.')
                    )
                )
            );
        }

        $listingProduct = Mage::getModel('M2ePro/Walmart_Listing_Product')->load($productId);

        if (!$listingProduct->getId()) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'result' => false,
                        'message' => Mage::helper('M2ePro')->__('Listing product does not exist.')
                    )
                )
            );
        }

        $lockManager = Mage::getModel(
            'M2ePro/Listing_Product_LockManager', array('listing_product' => $listingProduct)
        );
        $lockManager->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);
        $lockManager->setLogsAction($this->getLogsAction(Ess_M2ePro_Model_Listing_Product::ACTION_REVISE));

        if ($lockManager->checkLocking()) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'result'  => false,
                        'message' => Mage::helper('M2ePro')->__(
                            'Another Action is being processed. Try again when the Action is completed.'
                        )
                    )
                )
            );
        }

        $oldIdentifier = $listingProduct->getData($type);
        if ($oldIdentifier === $value) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'result' => true,
                        'message' => ''
                    )
                )
            );
        }

        $configurator = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Configurator');
        $configurator->disableAll();
        $configurator->allowDetails();

        $scheduledActionManager = Mage::getModel('M2ePro/Listing_Product_ScheduledAction_Manager');

        $scheduledAction = Mage::getModel('M2ePro/Listing_Product_ScheduledAction');
        $scheduledAction->setData(
            array(
                'listing_product_id' => $listingProduct->getId(),
                'component'          => Ess_M2ePro_Helper_Component_Walmart::NICK,
                'action_type'        => Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                'is_force'           => true,
                'tag'                => '/details/',
                'additional_data'    => Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'params' => array(
                            'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER,
                            'changed_identifier' => array(
                                'type'  => $type,
                                'value' => $value,
                            )
                        ),
                        'configurator' => $configurator->getData(),
                    )
                ),
            )
        );

        /** @var Ess_M2ePro_Model_Listing_Product_ScheduledAction $existedScheduledAction */
        $existedScheduledAction = Mage::getModel('M2ePro/Listing_Product_ScheduledAction')->load(
            $listingProduct->getId(), 'listing_product_id'
        );

        if ($existedScheduledAction->getId()) {
            $scheduledActionManager->updateAction($scheduledAction);
        } else {
            $scheduledActionManager->addAction($scheduledAction);
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                    'result' => true,
                    'message' => ''
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
        $magentoViewMode = Ess_M2ePro_Block_Adminhtml_Walmart_Listing_View::VIEW_MODE_MAGENTO;
        $sessionParamName = Mage::getBlockSingleton('M2ePro/Adminhtml_Walmart_Listing_View')->getId()
                            . $listingData['id']
                            . 'view_mode';

        if (($this->getRequest()->getParam('view_mode') == $magentoViewMode) ||
            $magentoViewMode == Mage::helper('M2ePro/Data_Session')->getValue($sessionParamName)) {
            $useCustomOptions = false;
        }

        // ---------------------------------------

        /** @var $ruleModel Ess_M2ePro_Model_Magento_Product_Rule */
        $ruleModel = Mage::getModel('M2ePro/Walmart_Magento_Product_Rule')->setData(
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

        $prefix = 'walmart_hide_products_others_listings_';
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
    protected function filterProductsForMapOrUnmapCategoryTemplateByChunks($productsIdsParam)
    {
        if (count($productsIdsParam) > 1000) {
            $productsIds = array();
            $productsIdsParam = array_chunk($productsIdsParam, 1000);
            foreach ($productsIdsParam as $productsIdsParamChunk) {
                $productsIds = array_merge(
                    $productsIds,
                    $this->filterProductsForMapOrUnmapCategoryTemplate($productsIdsParamChunk)
                );
            }
        } else {
            $productsIds = $this->filterProductsForMapOrUnmapCategoryTemplate($productsIdsParam);
        }

        return $productsIds;
    }

    protected function filterProductsForMapOrUnmapCategoryTemplate($productsIds)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableWalmartListingProduct = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_walmart_listing_product');

        $select = $connRead->select();

        // selecting all except parents general_id owners or simple general_id owners without general_id
        $select->from($tableWalmartListingProduct, 'listing_product_id')
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

    protected function setCategoryTemplateFroProductsByChunks($productsIds, $templateId)
    {
        if (count($productsIds) > 1000) {
            $productsIds = array_chunk($productsIds, 1000);
            foreach ($productsIds as $productsIdsChunk) {
                $this->setCategoryTemplateForProducts($productsIdsChunk, $templateId);
            }
        } else {
            $this->setCategoryTemplateForProducts($productsIds, $templateId);
        }
    }

    protected function setCategoryTemplateForProducts($productsIds, $templateId)
    {
        if (empty($productsIds)) {
            return;
        }

        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
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

                $oldTemplateIds[$listingProduct->getId()] = $listingProduct->getData('template_category_id');

                $listingProduct->setData('template_category_id', $templateId);
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

        $newTemplate = Mage::getModel('M2ePro/Walmart_Template_Category')->load($templateId);

        if ($newTemplate->getId()) {
            $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Template_Category_SnapshotBuilder');
            $snapshotBuilder->setModel($newTemplate);
            $newSnapshot = $snapshotBuilder->getSnapshot();
        } else {
            $newSnapshot = array();
        }

        foreach ($collection->getItems() as $listingProduct) {
            /**@var Ess_M2ePro_Model_Listing_Product $listingProduct */

            $oldTemplate = Mage::getModel('M2ePro/Walmart_Template_Category')->load(
                $oldTemplateIds[$listingProduct->getId()]
            );

            if ($oldTemplate->getId()) {
                $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Template_Category_SnapshotBuilder');
                $snapshotBuilder->setModel($oldTemplate);
                $oldSnapshot = $snapshotBuilder->getSnapshot();
            } else {
                $oldSnapshot = array();
            }

            if (empty($newSnapshot) && empty($oldSnapshot)) {
                continue;
            }

            $diff = Mage::getModel('M2ePro/Walmart_Template_Category_Diff');
            $diff->setOldSnapshot($oldSnapshot);
            $diff->setNewSnapshot($newSnapshot);

            $changeProcessor = Mage::getModel('M2ePro/Walmart_Template_Category_ChangeProcessor');
            $changeProcessor->process(
                $diff, array(array('id' => $listingProduct->getId(), 'status' => $listingProduct->getStatus()))
            );
        }
    }

    //########################################

    protected function processDescriptionTemplateChange(
        array $oldData,
        array $newData,
        array $affectedListingsProductsData
    ) {
        if (empty($affectedListingsProductsData) ||
            empty($oldData['template_description_id']) || empty($newData['template_description_id'])) {
            return;
        }

        $oldTemplate = Mage::helper('M2ePro/Component_Walmart')->getObject(
            'Template_Description', $oldData['template_description_id']
        );
        $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Template_Description_SnapshotBuilder');
        $snapshotBuilder->setModel($oldTemplate);
        $oldSnapshot = $snapshotBuilder->getSnapshot();

        $newTemplate = Mage::helper('M2ePro/Component_Walmart')->getObject(
            'Template_Description', $newData['template_description_id']
        );
        $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Template_Description_SnapshotBuilder');
        $snapshotBuilder->setModel($newTemplate);
        $newSnapshot = $snapshotBuilder->getSnapshot();

        $diff = Mage::getModel('M2ePro/Walmart_Template_Description_Diff');
        $diff->setNewSnapshot($newSnapshot);
        $diff->setOldSnapshot($oldSnapshot);

        $changeProcessor = Mage::getModel('M2ePro/Walmart_Template_Description_ChangeProcessor');
        $changeProcessor->process($diff, $affectedListingsProductsData);
    }

    protected function processSellingFormatTemplateChange(
        array $oldData,
        array $newData,
        array $affectedListingsProductsData
    ) {
        if (empty($affectedListingsProductsData) ||
            empty($oldData['template_selling_format_id']) || empty($newData['template_selling_format_id'])) {
            return;
        }

        $oldTemplate = Mage::helper('M2ePro/Component_Walmart')->getObject(
            'Template_SellingFormat', $oldData['template_selling_format_id']
        );
        $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_SnapshotBuilder');
        $snapshotBuilder->setModel($oldTemplate);
        $oldSnapshot = $snapshotBuilder->getSnapshot();

        $newTemplate = Mage::helper('M2ePro/Component_Walmart')->getObject(
            'Template_SellingFormat', $newData['template_selling_format_id']
        );
        $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_SnapshotBuilder');
        $snapshotBuilder->setModel($newTemplate);
        $newSnapshot = $snapshotBuilder->getSnapshot();

        $diff = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_Diff');
        $diff->setNewSnapshot($newSnapshot);
        $diff->setOldSnapshot($oldSnapshot);

        $changeProcessor = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_ChangeProcessor');
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

        $oldTemplate = Mage::helper('M2ePro/Component_Walmart')->getObject(
            'Template_Synchronization', $oldData['template_synchronization_id']
        );
        $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Template_Synchronization_SnapshotBuilder');
        $snapshotBuilder->setModel($oldTemplate);
        $oldSnapshot = $snapshotBuilder->getSnapshot();

        $newTemplate = Mage::helper('M2ePro/Component_Walmart')->getObject(
            'Template_Synchronization', $newData['template_synchronization_id']
        );
        $snapshotBuilder = Mage::getModel('M2ePro/Walmart_Template_Synchronization_SnapshotBuilder');
        $snapshotBuilder->setModel($newTemplate);
        $newSnapshot = $snapshotBuilder->getSnapshot();

        $diff = Mage::getModel('M2ePro/Walmart_Template_Synchronization_Diff');
        $diff->setNewSnapshot($newSnapshot);
        $diff->setOldSnapshot($oldSnapshot);

        $changeProcessor = Mage::getModel('M2ePro/Walmart_Template_Synchronization_ChangeProcessor');
        $changeProcessor->process($diff, $affectedListingsProductsData);
    }

    //########################################

    protected function runProcessorForParents($productsIds)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableWalmartListingProduct = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_walmart_listing_product');

        $select = $connRead->select();
        $select->from(array('alp' => $tableWalmartListingProduct), array('listing_product_id'))
            ->where('listing_product_id IN (?)', $productsIds)
            ->where('is_variation_parent = ?', 1);

        $productsIds = Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($select);

        foreach ($productsIds as $productId) {
            $listingProduct = Mage::helper('M2ePro/Component_Walmart')->getObject('Listing_Product', $productId);
            $listingProduct->getChildObject()->getVariationManager()->getTypeModel()->getProcessor()->process();
        }
    }

    //########################################
}
