<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Helper_Component_Ebay_Category as eBayCategory;

class Ess_M2ePro_Adminhtml_Ebay_ListingController extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    protected $_muteNotifications = false;

    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Listings'));

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
            ->addJs('M2ePro/TemplateManager.js')
            ->addJs('M2ePro/Attribute.js')
            ->addJs('M2ePro/Listing/ProductGrid.js')
            ->addJs('M2ePro/Action.js')
            ->addJs('M2ePro/Listing/Action.js')
            ->addJs('M2ePro/Ebay/Listing/Action.js')
            ->addJs('M2ePro/Listing/Moving.js')
            ->addJs('M2ePro/Listing/Mapping.js')
            ->addJs('M2ePro/Grid.js')
            ->addJs('M2ePro/Listing/Category/Tree.js')
            ->addJs('M2ePro/Listing/Grid.js')
            ->addJs('M2ePro/Listing/EditListingTitle.js')
            ->addJs('M2ePro/Listing/Other.js')
            ->addJs('M2ePro/Ebay/Listing/Grid.js')
            ->addJs('M2ePro/Ebay/Listing/ViewGrid.js')
            ->addJs('M2ePro/Ebay/Listing/Bids.js')
            ->addJs('M2ePro/Grid/Frame.js')
            ->addJs('M2ePro/Listing/AutoAction.js')
            ->addJs('M2ePro/Ebay/Listing/AutoAction.js')
            ->addJs('M2ePro/Ebay/Listing/VariationProductManage.js')
            ->addJs('M2ePro/Ebay/Listing/Ebay/Grid.js')
            ->addJs('M2ePro/Ebay/Listing/Settings.js')
            ->addJs('M2ePro/Ebay/Listing/Settings/Grid.js')
            ->addJs('M2ePro/Ebay/Listing/EditCompatibilityMode.js')
            ->addJs('M2ePro/Ebay/Motors.js')
            ->addJs('M2ePro/Ebay/Motor/Add/Item/Grid.js')
            ->addJs('M2ePro/Ebay/Motor/Add/Filter/Grid.js')
            ->addJs('M2ePro/Ebay/Motor/Add/Group/Grid.js')
            ->addJs('M2ePro/Ebay/Motor/View/Item/Grid.js')
            ->addJs('M2ePro/Ebay/Motor/View/Filter/Grid.js')
            ->addJs('M2ePro/Ebay/Motor/View/Group/Grid.js')
            ->addJs('M2ePro/Ebay/Template/Category/Chooser.js')
            ->addJs('M2ePro/Ebay/Template/Category/Chooser/Browse.js')
            ->addJs('M2ePro/Ebay/Template/Category/Specifics.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "m2e-pro-listing");

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK . '/listings'
        );
    }

    protected function _setActiveMenu($menuPath)
    {
        if (!$this->getLayout()->getBlock('menu')) {
            return $this;
        }

        return parent::_setActiveMenu($menuPath);
    }

    // ---------------------------------------

    protected function addNotificationMessages()
    {
        if ($this->_muteNotifications) {
            return;
        }

        parent::addNotificationMessages();
    }

    protected function beforeAddContentEvent()
    {
        if ($this->_muteNotifications) {
            return;
        }

        parent::beforeAddContentEvent();
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_ebay_manageListings',
                    '',
                    array(
                        'tab' => $this->getRequest()->getParam(
                            'tab',
                            Ess_M2ePro_Block_Adminhtml_Ebay_ManageListings::TAB_ID_LISTING
                        )
                    )
                )
            )
            ->renderLayout();
    }

    public function listingGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function getListingTabAction()
    {
        /** @var $block Ess_M2ePro_Block_Adminhtml_Ebay_Listing */
        $block = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_manageListings',
            '',
            array('tab' => Ess_M2ePro_Block_Adminhtml_Ebay_ManageListings::TAB_ID_LISTING)
        );
        $this->_initAction()->_addContent($block)->renderLayout();
    }

    public function getListingOtherTabAction()
    {
        /** @var $block Ess_M2ePro_Block_Adminhtml_Ebay_Listing */
        $block = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_manageListings',
            '',
            array('tab' => Ess_M2ePro_Block_Adminhtml_Ebay_ManageListings::TAB_ID_LISTING_OTHER)
        );
        $this->_initAction()->_addContent($block)->renderLayout();
    }

    public function getSearchTabAction()
    {
        /** @var $block Ess_M2ePro_Block_Adminhtml_Ebay_Listing */
        $block = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_manageListings',
            '',
            array('tab' => Ess_M2ePro_Block_Adminhtml_Ebay_ManageListings::TAB_ID_SEARCH)
        );
        $this->_initAction()->_addContent($block)->renderLayout();
    }

    public function goToEbayAction()
    {
        $itemId = $this->getRequest()->getParam('item_id');
        $accountId = $this->getRequest()->getParam('account_id');
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');

        if ($itemId === null || $accountId === null || $marketplaceId === null) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Requested eBay Item ID is not found.'));
            $this->_redirect('*/*/index');
            return;
        }

        $accountMode = Mage::helper('M2ePro/Component_Ebay')->getObject('Account', $accountId)
            ->getChildObject()
            ->getMode();

        $url = Mage::helper('M2ePro/Component_Ebay')->getItemUrl(
            $itemId, $accountMode, $marketplaceId
        );

        $this->_redirectUrl($url);
    }

    //########################################

    public function searchAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_search'))
            ->renderLayout();
    }

    public function searchGridAction()
    {
        $listingType = $this->getRequest()->getParam('listing_type', false);
        $gridBlock = $listingType == Ess_M2ePro_Block_Adminhtml_Listing_Search_Switcher::LISTING_TYPE_LISTING_OTHER
            ? $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_search_other_grid')
            : $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_search_m2ePro_grid');

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
                    'do_list'   => null,
                    'view_mode' => Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View::VIEW_MODE_EBAY
                )
            );
        }

        $id = $this->getRequest()->getParam('id');

        try {
            $model = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $id);
        } catch (Ess_M2ePro_Model_Exception_Logic $e) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_ebay_listing/index');
        }

        $productAddIds = $model->getData('product_add_ids');
        $productAddIds = array_filter((array)Mage::helper('M2ePro')->jsonDecode($productAddIds));

        if (!empty($productAddIds)) {
            $this->_getSession()->addNotice(
                Mage::helper('M2ePro')->__(
                    'Please make sure you finish adding new Products before moving to the next step.'
                )
            );

            return $this->_redirect(
                '*/adminhtml_ebay_listing_categorySettings',
                array('listing_id' => $id, 'step' => 1)
            );
        }

        $this->_initAction();

        $this->setPageHelpLink(null, null, "m2e-pro-listing");

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addJs('mage/adminhtml/rules.js')
            ->addJs('M2ePro/Ebay/Listing/Category.js')
            ->addJs('M2ePro/Ebay/Listing/Template/Switcher.js')
            ->addJs('M2ePro/Ebay/Template/Category/Chooser.js')
            ->addJs('M2ePro/Ebay/Template/Category/Chooser/Browse.js')
            ->addJs('M2ePro/Ebay/Template/Category/Specifics.js')
            ->addJs('M2ePro/Ebay/Template/Return.js')
            ->addJs('M2ePro/Ebay/Template/Shipping.js')
            ->addJs('M2ePro/Ebay/Template/Shipping/ExcludedLocations.js')
            ->addJs('M2ePro/Ebay/Template/SellingFormat.js')
            ->addJs('M2ePro/Ebay/Template/Description.js')
            ->addJs('M2ePro/Ebay/Template/Synchronization.js')
            ->addJs('M2ePro/VideoTutorial.js')
            ->addJs('M2ePro/SynchProgress.js')
            ->addJs('M2ePro/Ebay/Marketplace/SynchProgress.js')
            ->addJs('M2ePro/Marketplace.js')
            ->addJs('M2ePro/Ebay/Listing/Transferring.js');

        if (Mage::helper('M2ePro/Magento')->isTinyMceAvailable()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

        $this->setRuleData('ebay_rule_view_listing');

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view'))
            ->renderLayout();
    }

    public function viewGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $id);

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

        $this->setRuleData('ebay_rule_view_listing');

        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view')->getGridHtml();
        $this->getResponse()->setBody($response);
    }

    //########################################

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $id);

        if (!$listing->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_ebay_listing/index');
        }

        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_edit'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            $this->_redirect('*/adminhtml_ebay_listing/index');
        }

        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Ebay')->getModel('Listing')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_ebay_listing/index');
        }

        $snapshotBuilder = Mage::getModel('M2ePro/Ebay_Listing_SnapshotBuilder');
        $snapshotBuilder->setModel($model);

        $oldData = $snapshotBuilder->getSnapshot();

        $data = array();
        $keys = array(
            'template_payment_id',
            'template_shipping_id',
            'template_return_policy_id',
            'template_selling_format_id',
            'template_description_id',
            'template_synchronization_id',
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $model->addData($data);
        $model->getChildObject()->setEstimatedFeesObtainAttemptCount(0);
        $model->getChildObject()->setEstimatedFeesObtainRequired(true);
        $model->save();

        $snapshotBuilder = Mage::getModel('M2ePro/Ebay_Listing_SnapshotBuilder');
        $snapshotBuilder->setModel($model);

        $newData = $snapshotBuilder->getSnapshot();

        /** @var Ess_M2ePro_Model_Ebay_Template_Manager $templateManager */
        $templateManager = Mage::getSingleton('M2ePro/Ebay_Template_Manager');

        $affectedListingsProducts = Mage::getModel('M2ePro/Ebay_Listing_AffectedListingsProducts');
        $affectedListingsProducts->setModel($model);

        foreach ($templateManager->getAllTemplates() as $template) {
            $templateManager->setTemplate($template);
            $templateModelName = $templateManager->getTemplateModelName();

            /** @var Ess_M2ePro_Model_ActiveRecord_SnapshotBuilder $snapshotBuilder */
            /** @var Ess_M2ePro_Model_ActiveRecord_Diff $diff */
            /** @var Ess_M2ePro_Model_Template_ChangeProcessorAbstract $changeProcessor */
            if ($templateManager->isHorizontalTemplate()) {
                $newTemplate = Mage::helper('M2ePro/Component_Ebay')
                    ->getCachedObject(
                        $templateModelName,
                        $newData[$templateManager->getTemplateIdColumnName()],
                        null,
                        array('template')
                    )
                    ->getChildObject();
                $oldTemplate = Mage::helper('M2ePro/Component_Ebay')
                    ->getCachedObject(
                        $templateModelName,
                        $oldData[$templateManager->getTemplateIdColumnName()],
                        null,
                        array('template')
                    )
                    ->getChildObject();
                $snapshotBuilder = Mage::getModel(
                    'M2ePro/Ebay_' . $templateModelName . '_SnapshotBuilder'
                );
                $diff = Mage::getModel('M2ePro/Ebay_' . $templateModelName . '_Diff');
                $changeProcessor = Mage::getModel(
                    'M2ePro/Ebay_' . $templateModelName . '_ChangeProcessor'
                );
            } else {
                $newTemplate = Mage::helper('M2ePro')->getCachedObject(
                    $templateModelName,
                    $newData[$templateManager->getTemplateIdColumnName()],
                    null,
                    array('template')
                );
                $oldTemplate = Mage::helper('M2ePro')->getCachedObject(
                    $templateModelName,
                    $oldData[$templateManager->getTemplateIdColumnName()],
                    null,
                    array('template')
                );
                $snapshotBuilder = Mage::getModel(
                    'M2ePro/' . $templateModelName . '_SnapshotBuilder'
                );
                $diff = Mage::getModel('M2ePro/' . $templateModelName . '_Diff');
                $changeProcessor = Mage::getModel(
                    'M2ePro/' . $templateModelName . '_ChangeProcessor'
                );
            }

            $snapshotBuilder->setModel($newTemplate);
            $newTemplateData = $snapshotBuilder->getSnapshot();

            $snapshotBuilder->setModel($oldTemplate);
            $oldTemplateData = $snapshotBuilder->getSnapshot();

            $diff->setNewSnapshot($newTemplateData);
            $diff->setOldSnapshot($oldTemplateData);

            $changeProcessor->process(
                $diff, $affectedListingsProducts->getData(array('id', 'status'), array('template' => $template))
            );
        }

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('The Listing was saved.'));

        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list', array(), array('edit' => array('id' => $id))));
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
            $template = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $id);
            if ($template->isLocked()) {
                $locked++;
            } else {
                $template->deleteInstance();
                $deleted++;
            }
        }

        $tempString = Mage::helper('M2ePro')->__('%amount% Listing(s) were deleted', $deleted);
        $deleted && $this->_getSession()->addSuccess($tempString);

        $tempString = Mage::helper('M2ePro')->__(
            '%amount% Listing(s) cannot be deleted because they have Items with Status "In Progress".', $locked
        );
        $locked && $this->_getSession()->addError($tempString);

        $this->_redirect('*/adminhtml_ebay_listing/index');
    }

    // ---------------------------------------

    public function getChangePartsCompatibilityModePopupHtmlAction()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Grid_Motor_EditMode $block */
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_grid_motor_editMode');
        $block->setListingId($this->getRequest()->getParam('listing_id'));

        $this->getResponse()->setBody($block->toHtml());
    }

    public function savePartsCompatibilityModeAction()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        $mode = $this->getRequest()->getParam('mode');

        if ($listingId === null) {
            return;
        }

        $model = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        $model->setData('parts_compatibility_mode', $mode)->save();
    }

    //########################################

    public function getEstimatedFeesAction()
    {
        // @codingStandardsIgnoreLine
        session_write_close();

        // ---------------------------------------
        $listingId = $this->getRequest()->getParam('listing_id');
        $listingProductId = $this->getRequest()->getParam('listing_product_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        // ---------------------------------------

        // ---------------------------------------
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('listing_id', $listingId);
        $listingProductCollection->addFieldToFilter(
            'status', array(
                'in' => array(
                    Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED,
                    Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE,
                    Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED,
                )
            )
        );
        $listingProductCollection->setPageSize(3);

        if ($listingProductId) {
            $listingProductCollection->addFieldToFilter('id', $listingProductId);
        }

        // ---------------------------------------

        // ---------------------------------------
        if ($listingProductCollection->getSize() == 0) {
            $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('error' => true)));
            return;
        }

        // ---------------------------------------

        $this->loadLayout();

        $fees = $errors = array();
        $sourceProduct = null;

        foreach ($listingProductCollection->getItems() as $product) {
            $fees = array();

            $params = array(
                'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN,
                'logs_action_id' => Mage::getModel('M2ePro/Listing_Log')->getResource()->getNextActionId()
            );

            $dispatcher = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');

            /** @var Ess_M2ePro_Model_Ebay_Connector_Item_Verify_Requester $connector */
            $connector = $dispatcher->getCustomConnector('Ebay_Connector_Item_Verify_Requester', $params);
            $connector->setListingProduct($product);

            try {
                $connector->process();
                $fees = $connector->getPreparedResponseData();
            } catch (Exception $exception) {
                Mage::helper('M2ePro/Module_Exception')->process($exception);
            }

            $listing->getChildObject()->increaseEstimatedFeesObtainAttemptCount();

            if (!empty($fees)) {
                $sourceProduct = $product;
                break;
            }

            if (!$listingProductId) {
                // this is obtaining of estimated fee for random product from listing
                $listing->getChildObject()->increaseEstimatedFeesObtainAttemptCount();
            } else {
                $listingProduct = Mage::helper('M2ePro/Component_Ebay')
                    ->getObject('Listing_Product', $listingProductId);

                if ($connector->getResponse() !== null) {
                    foreach ($connector->getResponse()->getMessages()->getErrorEntities() as $errorMessage) {
                        $connector->getLogger()->logListingProductMessage(
                            $listingProduct,
                            $errorMessage
                        );
                    }
                }

                $currentErrors = $connector->getLogger()->getStoredMessages();
                if (!empty($currentErrors)) {
                    $errors = $currentErrors;
                }
            }
        }

        // ---------------------------------------
        if (empty($fees)) {
            if (empty($errors)) {
                $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('error' => true)));
            } else {
                $errorsBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view_fee_errors');
                $errorsBlock->setData('errors', $errors);

                $this->getResponse()->setBody(
                    Mage::helper('M2ePro')->jsonEncode(
                        array('html' => $errorsBlock->toHtml())
                    )
                );
            }

            return;
        }

        // ---------------------------------------

        // ---------------------------------------
        if ($listingProductId) {
            $details = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view_fee_details');
            $details->setData('fees', $fees);
            $details->setData('product_name', $sourceProduct->getMagentoProduct()->getName());

            $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('html' => $details->toHtml())));
            return;
        }

        // ---------------------------------------

        // ---------------------------------------
        $listing->getChildObject()->setEstimatedFeesObtainAttemptCount(0);
        $listing->getChildObject()->setEstimatedFeesObtainRequired(false);
        $listing->getChildObject()->setEstimatedFees($fees);
        $listing->getChildObject()->setEstimatedFeesSourceProductName($sourceProduct->getMagentoProduct()->getName());
        $listing->save();
        // ---------------------------------------

        $preview = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view_fee_preview');
        $preview->setData('fees', $fees);
        $preview->setData('product_name', $sourceProduct->getMagentoProduct()->getName());

        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('html' => $preview->toHtml())));
    }

    //########################################

    public function getItemDuplicatePopUpAction()
    {
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProductId = $this->getRequest()->getParam('listing_product_id');
        $listingProduct = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product', $listingProductId);

        if (!$listingProduct->getId()) {
            $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array('error' => Mage::helper('M2ePro')->__("Unable to load product ID [{$listingProductId}]."))
                )
            );
        }

        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View_Ebay_ItemDuplicate $block */
        $block = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_view_ebay_ItemDuplicate', '', array(
                'listing_product' => $listingProduct
            )
        );

        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array('html' => $block->toHtml())
            )
        );
    }

    // ---------------------------------------

    public function solveEbayItemDuplicateAction()
    {
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProductId = $this->getRequest()->getParam('listing_product_id');
        $listingProduct = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product', $listingProductId);

        $result = true;
        $messages = array();

        if (!$listingProduct->getId()) {
            $result = false;
            $messages[] = Mage::helper('M2ePro')->__("Unable to load product ID [{$listingProductId}].");
        }

        if ($result && $this->getRequest()->getParam('stop_duplicated_item') == 1) {
            $result = $this->solveEbayItemDuplicateStop($listingProduct, $messages);
        }

        if ($result) {
            $additionalData = $listingProduct->getAdditionalData();
            unset($additionalData['item_duplicate_action_required']);

            $listingProduct->setData('item_uuid', $listingProduct->getChildObject()->generateItemUUID());
            $listingProduct->setData('is_duplicate', 0);
            $listingProduct->setData('additional_data', Mage::helper('M2ePro')->jsonEncode($additionalData));
            $listingProduct->save();
        }

        if ($result && $this->getRequest()->getParam('list_current_item') == 1) {
            $result = $this->solveEbayItemDuplicateList($listingProduct);
        }

        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                    'result'  => $result,
                    'message' => implode(' ', $messages)
                )
            )
        );
    }

    protected function solveEbayItemDuplicateStop(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$messages)
    {
        $duplicateMark = $listingProduct->getSetting('additional_data', 'item_duplicate_action_required');
        $itemId = $duplicateMark['item_id'];

        if (!$itemId) {
            $messages[] = Mage::helper('M2ePro')->__("Item ID is not presented.");
            return false;
        }

        /** @var $dispatcherObject Ess_M2ePro_Model_Ebay_Connector_Dispatcher */
        $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector(
            'item', 'update', 'ends',
            array('items' => array($itemId)), null,
            $listingProduct->getMarketplace()->getId(),
            $listingProduct->getAccount()->getId()
        );

        try {
            $dispatcherObject->process($connectorObj);
            $response = $connectorObj->getResponseData();
        } catch (Exception $e) {
            Mage::helper('M2ePro/Module_Exception')->process($e);
        }

        if (!isset($response['result'][0]['ebay_end_date_raw'])) {
            $messages[] = Mage::helper('M2ePro')->__("Unable to stop eBay item ID.");
            return false;
        }

        return true;
    }

    protected function solveEbayItemDuplicateList(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');

        $listingProduct = clone $listingProduct;
        $listingProduct->setActionConfigurator($configurator);

        try {
            $dispatcher = Mage::getModel('M2ePro/Ebay_Connector_Item_Dispatcher');
            $dispatcher->process(
                Ess_M2ePro_Model_Listing_Product::ACTION_LIST, array($listingProduct), array(
                    'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER,
                    'is_realtime'    => true,
                )
            );
        } catch (Exception $e) {
            Mage::helper('M2ePro/Module_Exception')->process($e);
            return false;
        }

        return true;
    }

    //########################################

    public function getCategoryChooserHtmlAction()
    {
        $lPIds = $this->getRequestIds();
        $accountId = $this->getRequest()->getParam('account_id');
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');

        $productResource = Mage::getResourceModel('M2ePro/Ebay_Listing_Product');

        /** @var Ess_M2ePro_Model_Ebay_Template_Category_Chooser_Converter $converter */
        $converter = Mage::getModel('M2ePro/Ebay_Template_Category_Chooser_Converter');
        $accountId && $converter->setAccountId($accountId);
        $marketplaceId && $converter->setMarketplaceId($marketplaceId);

        $ids = $productResource->getTemplateCategoryIds($lPIds, 'template_category_id', true);
        $template = $this->tryToLoadCategoryTemplate($ids);
        if ($template && $template->getId()) {
            $converter->setCategoryDataFromTemplate($template->getData(), eBayCategory::TYPE_EBAY_MAIN);
        }

        $ids = $productResource->getTemplateCategoryIds($lPIds, 'template_category_secondary_id', true);
        $template = $this->tryToLoadCategoryTemplate($ids);
        if ($template && $template->getId()) {
            $converter->setCategoryDataFromTemplate($template->getData(), eBayCategory::TYPE_EBAY_SECONDARY);
        }

        $ids = $productResource->getTemplateCategoryIds($lPIds, 'template_store_category_id', true);
        $template = $this->tryToLoadStoreCategoryTemplate($ids);
        if ($template && $template->getId()) {
            $converter->setCategoryDataFromTemplate($template->getData(), eBayCategory::TYPE_STORE_MAIN);
        }

        $ids = $productResource->getTemplateCategoryIds($lPIds, 'template_store_category_secondary_id', true);
        $template = $this->tryToLoadStoreCategoryTemplate($ids);
        if ($template && $template->getId()) {
            $converter->setCategoryDataFromTemplate($template->getData(), eBayCategory::TYPE_STORE_SECONDARY);
        }

        $this->loadLayout();

        /** @var $chooserBlock Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Chooser */
        $chooserBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser');
        $accountId && $chooserBlock->setAccountId($accountId);
        $marketplaceId && $chooserBlock->setMarketplaceId($marketplaceId);
        $chooserBlock->setCategoryMode($this->getRequest()->getParam('category_mode'));
        $chooserBlock->setCategoriesData($converter->getCategoryDataForChooser());

        $this->getResponse()->setBody($chooserBlock->toHtml());
    }

    protected function tryToLoadCategoryTemplate($ids)
    {
        /** @var Ess_M2ePro_Model_Ebay_Template_Category $template */
        $template = Mage::getModel('M2ePro/Ebay_Template_Category');

        if (empty($ids)) {
            return $template;
        }

        /** @var Ess_M2ePro_Model_Resource_Ebay_Template_Category_Collection $collection */
        $collection = $template->getCollection();
        $collection->addFieldToFilter('id', array('in' => $ids));

        if (count($ids) !== $collection->getSize()) {
            // @codingStandardsIgnoreLine
            return $template;
        }

        if (count($ids) === 1) {
            // @codingStandardsIgnoreLine
            return $collection->getFirstItem();
        }

        $differentCategories = array();
        $isCustomTemplate = 0;
        foreach ($collection->getItems() as $item) {
            /**@var Ess_M2ePro_Model_Ebay_Template_Category $item */
            $differentCategories[] = $item->getCategoryValue();

            $item->getIsCustomTemplate() && $isCustomTemplate = $item->getIsCustomTemplate();
        }

        if (count(array_unique($differentCategories)) > 1) {
            return $template;
        }

        if ($isCustomTemplate) {
            $collection->addFieldToFilter('is_custom_template', $isCustomTemplate);
        }

        /** @var Ess_M2ePro_Model_Ebay_Template_Category $tempTemplate */
        // @codingStandardsIgnoreLine
        $tempTemplate = $collection->getFirstItem();

        $template = Mage::getModel('M2ePro/Ebay_Template_Category');
        $template->loadByCategoryValue(
            $tempTemplate->getCategoryValue(),
            $tempTemplate->getCategoryMode(),
            $tempTemplate->getMarketplaceId(),
            $isCustomTemplate
        );

        return $template;
    }

    protected function tryToLoadStoreCategoryTemplate($ids)
    {
        /** @var Ess_M2ePro_Model_Ebay_Template_StoreCategory $template */
        $template = Mage::getModel('M2ePro/Ebay_Template_StoreCategory');

        if (empty($ids)) {
            return $template;
        }

        /** @var Ess_M2ePro_Model_Resource_Ebay_Template_StoreCategory_Collection $collection */
        $collection = $template->getCollection();
        $collection->addFieldToFilter('id', array('in' => $ids));

        if (count($ids) !== $collection->getSize()) {
            // @codingStandardsIgnoreLine
            return $template;
        }

        if (count($ids) === 1) {
            // @codingStandardsIgnoreLine
            return $collection->getFirstItem();
        }

        $differentCategories = array();
        foreach ($collection->getItems() as $item) {
            /**@var Ess_M2ePro_Model_Ebay_Template_StoreCategory $item */
            $differentCategories[] = $item->getCategoryValue();
        }

        if (count(array_unique($differentCategories)) > 1) {
            return $template;
        }

        /** @var Ess_M2ePro_Model_Ebay_Template_StoreCategory $tempTemplate */
        // @codingStandardsIgnoreLine
        $tempTemplate = $collection->getFirstItem();

        $template = Mage::getModel('M2ePro/Ebay_Template_StoreCategory');
        $template->loadByCategoryValue(
            $tempTemplate->getCategoryValue(),
            $tempTemplate->getCategoryMode(),
            $tempTemplate->getAccountId()
        );

        return $template;
    }

    //----------------------------------------

    public function saveCategoryTemplateAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return;
        }

        if (!isset($post['template_category_data'])) {
            return;
        }

        $categoryTemplatesData = $post['template_category_data'];
        $categoryTemplatesData = Mage::helper('M2ePro')->jsonDecode($categoryTemplatesData);

        $accountId = $this->getRequest()->getParam('account_id');
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');

        /** @var Ess_M2ePro_Model_Ebay_Template_Category_Chooser_Converter $converter */
        $converter = Mage::getModel('M2ePro/Ebay_Template_Category_Chooser_Converter');
        $accountId && $converter->setAccountId($accountId);
        $marketplaceId && $converter->setMarketplaceId($marketplaceId);
        foreach ($categoryTemplatesData as $type => $templateData) {
            $converter->setCategoryDataFromChooser($templateData, $type);
        }

        $categoryTmpl = Mage::getModel('M2ePro/Ebay_Template_Category_Builder')->build(
            Mage::getModel('M2ePro/Ebay_Template_Category'),
            $converter->getCategoryDataForTemplate(eBayCategory::TYPE_EBAY_MAIN)
        );
        $categorySecondaryTmpl = Mage::getModel('M2ePro/Ebay_Template_Category_Builder')->build(
            Mage::getModel('M2ePro/Ebay_Template_Category'),
            $converter->getCategoryDataForTemplate(eBayCategory::TYPE_EBAY_SECONDARY)
        );
        $storeCategoryTmpl = Mage::getModel('M2ePro/Ebay_Template_StoreCategory_Builder')->build(
            Mage::getModel('M2ePro/Ebay_Template_StoreCategory'),
            $converter->getCategoryDataForTemplate(eBayCategory::TYPE_STORE_MAIN)
        );
        $storeCategorySecondaryTmpl = Mage::getModel('M2ePro/Ebay_Template_StoreCategory_Builder')->build(
            Mage::getModel('M2ePro/Ebay_Template_StoreCategory'),
            $converter->getCategoryDataForTemplate(eBayCategory::TYPE_STORE_SECONDARY)
        );

        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('id', array('in' => $this->getRequestIds()));

        $snapshots = array();
        $transaction = Mage::getModel('core/resource_transaction');

        try {
            foreach ($collection->getItems() as $listingProduct) {
                $snapshotBuilder = Mage::getModel('M2ePro/Ebay_Listing_Product_SnapshotBuilder');
                $snapshotBuilder->setModel($listingProduct);

                $snapshots[$listingProduct->getId()] = $snapshotBuilder->getSnapshot();

                $listingProduct->setData('template_category_id', $categoryTmpl->getId());
                $listingProduct->setData('template_category_secondary_id', $categorySecondaryTmpl->getId());
                $listingProduct->setData('template_store_category_id', $storeCategoryTmpl->getId());
                $listingProduct->setData('template_store_category_secondary_id', $storeCategorySecondaryTmpl->getId());

                $transaction->addObject($listingProduct);
            }

            $transaction->save();
        } catch (Exception $e) {
            Mage::helper('M2ePro/Module_Exception')->process($e);
            $transaction->rollback();

            return;
        }

        $this->updateProcessChanges($collection->getItems(), $snapshots);
    }

    public function resetSpecificsToDefaultAction()
    {
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('id', array('in' => $this->getRequestIds()));

        $transaction = Mage::getModel('core/resource_transaction');
        try {
            foreach ($collection->getItems() as $listingProduct) {
                $snapshotBuilder = Mage::getModel('M2ePro/Ebay_Listing_Product_SnapshotBuilder');
                $snapshotBuilder->setModel($listingProduct);

                $snapshots[$listingProduct->getId()] = $snapshotBuilder->getSnapshot();
                $listingProduct->setData('template_category_id', (int)$this->getRequest()->getParam('template_id'));
                $transaction->addObject($listingProduct);
            }

            $transaction->save();
        } catch (Exception $e) {
            Mage::helper('M2ePro/Module_Exception')->process($e);
            $transaction->rollback();

            return;
        }

        $this->updateProcessChanges($collection->getItems(), $snapshots);
    }

    //########################################

    public function getListingProductBidsAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('You should provide correct parameters');
        }

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product', $productId);

        /** @var $dispatcherObject Ess_M2ePro_Model_Ebay_Connector_Dispatcher */
        $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector(
            'item', 'get', 'bids',
            array('item_id' => $listingProduct->getChildObject()->getEbayItem()->getItemId()),
            null,
            null,
            $listingProduct->getAccount()->getId()
        );

        try {
            $dispatcherObject->process($connectorObj);
        } catch (Exception $e) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->__($e->getMessage()));
        }

        $bidsData = $connectorObj->getResponseData();

        $grid = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_bids_grid');

        if (empty($bidsData['items'])) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->__('Bids not found.'));
        }

        $grid->setBidsData($bidsData['items']);
        $grid->setListingProductId($productId);

        return $this->getResponse()->setBody($grid->toHtml());
    }

    //########################################

    protected function processConnector($action, array $params = array())
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            $message = 'The action is temporarily unavailable. M2E Pro Server is';
            $message .= '  under maintenance. Please try again later.';

            return Mage::helper('M2ePro')->__($message);
        }

        if (!$listingsProductsIds = $this->getRequest()->getParam('selected_products')) {
            return Mage::helper('M2ePro')->__('You should select Products');
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $productsCollection */
        $productsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $productsCollection->addFieldToFilter('id', explode(',', $listingsProductsIds));

        /** @var Ess_M2ePro_Model_Listing_Product[] $listingsProducts */
        $listingsProducts = $productsCollection->getItems();
        $logsActionId = Mage::getModel('M2ePro/Listing_Log')->getResource()->getNextActionId();

        $this->checkLocking($listingsProducts, $logsActionId, $action);
        if (empty($listingsProducts)) {
            return Mage::helper('M2ePro')->jsonEncode(array('result' => 'error', 'action_id' => $logsActionId));
        }

        return $this->runConnector($listingsProducts, $action, $params, $logsActionId);
    }

    protected function scheduleAction($action, array $params = array())
    {
        if (!$listingsProductsIds = $this->getRequest()->getParam('selected_products')) {
            return Mage::helper('M2ePro')->__('You should select Products');
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $productsCollection */
        $productsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $productsCollection->addFieldToFilter('id', explode(',', $listingsProductsIds));

        /** @var Ess_M2ePro_Model_Listing_Product[] $listingsProducts */
        $listingsProducts = $productsCollection->getItems();
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

        return Mage::helper('M2ePro')->jsonEncode(array('result' => 'success', 'action_id' => $logsActionId));
    }

    //########################################

    protected function checkLocking(&$listingsProducts, $logsActionId, $action)
    {
        foreach ($listingsProducts as $index => $listingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product_LockManager $lockManager */
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
            /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator $configurator */
            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
            $configurator->enableAll();
            $tag = '/qty/price/title/subtitle/description/images/categories/payment/shipping/return/other/';

            $additionalData['configurator'] = $configurator->getData();
        }

        return array(
            'listing_product_id' => $listingProduct->getId(),
            'component'          => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'action_type'        => $action,
            'is_force'           => true,
            'tag'                => $tag,
            'additional_data'    => Mage::helper('M2ePro')->jsonEncode($additionalData)
        );
    }

    // ---------------------------------------

    protected function runConnector($listingsProducts, $action, array $params, $logsActionId)
    {
        $listingsProductsIds = array();
        foreach ($listingsProducts as $listingProduct) {
            $listingsProductsIds[] = $listingProduct->getId();
        }

        $params['logs_action_id'] = $logsActionId;
        $params['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER;
        $params['is_realtime'] = true;

        /** @var Ess_M2ePro_Model_Ebay_Connector_Item_Dispatcher $dispatcherObject */
        $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Item_Dispatcher');
        $result = (int)$dispatcherObject->process($action, $listingsProductsIds, $params);

        if ($result == Ess_M2ePro_Helper_Data::STATUS_ERROR) {
            return Mage::helper('M2ePro')->jsonEncode(array('result' => 'error', 'action_id' => $logsActionId));
        }

        if ($result == Ess_M2ePro_Helper_Data::STATUS_WARNING) {
            return Mage::helper('M2ePro')->jsonEncode(array('result' => 'warning', 'action_id' => $logsActionId));
        }

        if ($result == Ess_M2ePro_Helper_Data::STATUS_SUCCESS) {
            return Mage::helper('M2ePro')->jsonEncode(array('result' => 'success', 'action_id' => $logsActionId));
        }

        return Mage::helper('M2ePro')->jsonEncode(array('result' => 'error', 'action_id' => $logsActionId));
    }

    // ---------------------------------------

    public function runListProductsAction()
    {
        if (Mage::helper('M2ePro')->jsonDecode($this->getRequest()->getParam('is_realtime'))) {
            return $this->getResponse()->setBody(
                $this->processConnector(Ess_M2ePro_Model_Listing_Product::ACTION_LIST)
            );
        }

        return $this->getResponse()->setBody($this->scheduleAction(Ess_M2ePro_Model_Listing_Product::ACTION_LIST));
    }

    public function runReviseProductsAction()
    {
        if (Mage::helper('M2ePro')->jsonDecode($this->getRequest()->getParam('is_realtime'))) {
            return $this->getResponse()->setBody(
                $this->processConnector(Ess_M2ePro_Model_Listing_Product::ACTION_REVISE)
            );
        }

        return $this->getResponse()->setBody($this->scheduleAction(Ess_M2ePro_Model_Listing_Product::ACTION_REVISE));
    }

    public function runRelistProductsAction()
    {
        if (Mage::helper('M2ePro')->jsonDecode($this->getRequest()->getParam('is_realtime'))) {
            return $this->getResponse()->setBody(
                $this->processConnector(Ess_M2ePro_Model_Listing_Product::ACTION_RELIST)
            );
        }

        return $this->getResponse()->setBody($this->scheduleAction(Ess_M2ePro_Model_Listing_Product::ACTION_RELIST));
    }

    public function runStopProductsAction()
    {
        if (Mage::helper('M2ePro')->jsonDecode($this->getRequest()->getParam('is_realtime'))) {
            return $this->getResponse()->setBody(
                $this->processConnector(Ess_M2ePro_Model_Listing_Product::ACTION_STOP)
            );
        }

        return $this->getResponse()->setBody($this->scheduleAction(Ess_M2ePro_Model_Listing_Product::ACTION_STOP));
    }

    public function runStopAndRemoveProductsAction()
    {
        if (!$listingsProductsIds = $this->getRequest()->getParam('selected_products')) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->__('You should select Products'));
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $productsCollection */
        $productsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
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
                /** @var Ess_M2ePro_Model_Listing_Product_RemoveHandler $removeHandler */
                $removeHandler = Mage::getModel(
                    'M2ePro/Listing_Product_RemoveHandler', array('listing_product' => $listingProduct)
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

        if (Mage::helper('M2ePro')->jsonDecode($this->getRequest()->getParam('is_realtime'))) {
            return $this->getResponse()->setBody(
                $this->runConnector(
                    $listingsProducts,
                    Ess_M2ePro_Model_Listing_Product::ACTION_STOP,
                    array('remove' => true),
                    $logsActionId
                )
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
        $prefix .= isset($listingData['id']) ? '_' . $listingData['id'] : '';
        Mage::helper('M2ePro/Data_Global')->setValue('rule_prefix', $prefix);

        $ruleModel = Mage::getModel('M2ePro/Ebay_Magento_Product_Rule')->setData(
            array(
                'prefix'   => $prefix,
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

    public function previewItemsAction()
    {
        $this->_muteNotifications = true;

        $this->_initAction()->_addContent(
            $this->getLayout()
                ->createBlock('M2ePro/adminhtml_ebay_listing_preview')
        )->renderLayout();

        $this->_muteNotifications = false;
    }

    //########################################

    protected function updateProcessChanges($listingProducts, $oldSnapshot)
    {
        /** @var Ess_M2ePro_Model_Ebay_Template_AffectedListingsProducts_Processor $changesProcessor */
        $changesProcessor = Mage::getModel('M2ePro/Ebay_Template_AffectedListingsProducts_Processor');

        foreach ($listingProducts as $listingProduct) {
            $snapshotBuilder = Mage::getModel('M2ePro/Ebay_Listing_Product_SnapshotBuilder');
            $snapshotBuilder->setModel($listingProduct);

            $changesProcessor->setListingProduct($listingProduct);
            $changesProcessor->processChanges(
                $snapshotBuilder->getSnapshot(),
                $oldSnapshot[$listingProduct->getId()]
            );
        }
    }

    //########################################
}
