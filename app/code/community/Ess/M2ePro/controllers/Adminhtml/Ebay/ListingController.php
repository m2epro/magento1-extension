<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

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
            ->addJs('M2ePro/Grid/FrameHandler.js')
            ->addJs('M2ePro/Ebay/Listing/VariationProductManageHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Ebay/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Settings/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Translation/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Transferring/PaymentHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Transferring/InfoHandler.js')
            ->addJs('M2ePro/Ebay/Listing/EditCompatibilityMode.js')
            ->addJs('M2ePro/Ebay/MotorsHandler.js')
            ->addJs('M2ePro/Ebay/Motor/Add/Item/GridHandler.js')
            ->addJs('M2ePro/Ebay/Motor/Add/Filter/GridHandler.js')
            ->addJs('M2ePro/Ebay/Motor/Add/Group/GridHandler.js')
            ->addJs('M2ePro/Ebay/Motor/View/Item/GridHandler.js')
            ->addJs('M2ePro/Ebay/Motor/View/Filter/GridHandler.js')
            ->addJs('M2ePro/Ebay/Motor/View/Group/GridHandler.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "x/agAJAQ");

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

    public function listingGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_grid');
        $this->getResponse()->setBody($block->toHtml());
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

            return $this->_redirect('*/adminhtml_ebay_listing_productAdd', array('listing_id' => $id, 'step' => 2));
        }

        $this->_initAction();

        $this->setPageHelpLink(null, null, "x/agAJAQ");

        // ---------------------------------------
        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addJs('mage/adminhtml/rules.js')
            ->addJs('M2ePro/Listing/Category/TreeHandler.js')
            ->addJs('M2ePro/Listing/AutoActionHandler.js')
            ->addJs('M2ePro/Ebay/Listing/AutoActionHandler.js')
            ->addJs('M2ePro/TemplateHandler.js')
            ->addJs('M2ePro/AttributeHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Template/SwitcherHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/ChooserHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/SpecificHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Chooser/BrowseHandler.js')
            ->addJs('M2ePro/Ebay/Template/PaymentHandler.js')
            ->addJs('M2ePro/Ebay/Template/ReturnHandler.js')
            ->addJs('M2ePro/Ebay/Template/ShippingHandler.js')
            ->addJs('M2ePro/Ebay/Template/Shipping/ExcludedLocationsHandler.js')
            ->addJs('M2ePro/Ebay/Template/SellingFormatHandler.js')
            ->addJs('M2ePro/Ebay/Template/DescriptionHandler.js')
            ->addJs('M2ePro/Ebay/Template/SynchronizationHandler.js')
            ->addJs('M2ePro/VideoTutorialHandler.js')
            ->addJs('M2ePro/SynchProgressHandler.js')
            ->addJs('M2ePro/Ebay/MarketplaceSynchProgressHandler.js')
            ->addJs('M2ePro/Ebay/Marketplace/SynchProgressHandler.js')
            ->addJs('M2ePro/MarketplaceHandler.js')
            ->addJs('M2ePro/Ebay/Listing/TransferringHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Transferring/ActionHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Transferring/BreadcrumbHandler.js');

        if (Mage::helper('M2ePro/Magento')->isTinyMceAvailable()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }

        // ---------------------------------------

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

        // Set rule model
        // ---------------------------------------
        $this->setRuleData('ebay_rule_view_listing');
        // ---------------------------------------

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view'))
             ->renderLayout();
    }

    public function viewGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $id);

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

         // Set rule model
        // ---------------------------------------
        $this->setRuleData('ebay_rule_view_listing');
        // ---------------------------------------

        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view')->getGridHtml();
        $this->getResponse()->setBody($response);
    }

    //########################################

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

        $tempString = Mage::helper('M2ePro')->__('%amount% Listing(s) were successfully deleted', $deleted);
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
            'status', array('in' => array(
            Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED,
            Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED,
            Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED,
            Ess_M2ePro_Model_Listing_Product::STATUS_SOLD,
            Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED,
            ))
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

        $result   = true;
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
            return false;
        }

        return true;
    }

    //########################################

    public function getCategoryChooserHtmlAction()
    {
        // ---------------------------------------
        $listingId = $this->getRequest()->getParam('listing_id');
        $listingProductIds = $this->getRequestIds();
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        // ---------------------------------------

        $internalData = array();

        // ---------------------------------------
        $categoryTemplateIds  = Mage::getResourceModel('M2ePro/Ebay_Listing_Product')->getTemplateCategoryIds(
            $listingProductIds
        );
        $internalData = array_merge(
            $internalData,
            Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getSameTemplatesData($categoryTemplateIds)
        );
        // ---------------------------------------
        $otherCategoryTemplateIds = Mage::getResourceModel('M2ePro/Ebay_Listing_Product')->getTemplateOtherCategoryIds(
            $listingProductIds
        );

        $internalData = array_merge(
            $internalData,
            Mage::helper('M2ePro/Component_Ebay_Category_Store')->getSameTemplatesData($otherCategoryTemplateIds)
        );
        // ---------------------------------------

        $this->loadLayout();

        /** @var $chooserBlock Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Chooser */
        $chooserBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser');
        $chooserBlock->setDivId('chooser_main_container');
        $chooserBlock->setAccountId($listing->getAccountId());
        $chooserBlock->setMarketplaceId($listing->getMarketplaceId());
        $chooserBlock->setInternalData($internalData);

        // ---------------------------------------
        $wrapper = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_view_settings_category_chooser_wrapper'
        );
        $wrapper->setChild('chooser', $chooserBlock);
        // ---------------------------------------

        $this->getResponse()->setBody($wrapper->toHtml());
    }

    public function getCategorySpecificHtmlAction()
    {
        // ---------------------------------------
        $listingId = $this->getRequest()->getParam('listing_id');
        $listingProductIds = $this->getRequestIds();
        $categoryMode = $this->getRequest()->getParam('category_mode');
        $categoryValue = $this->getRequest()->getParam('category_value');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        // ---------------------------------------

        $this->loadLayout();

        /** @var $specific Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Specific */
        $specific = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_specific');
        $specific->setMarketplaceId($listing->getMarketplaceId());
        $specific->setCategoryMode($categoryMode);
        $specific->setCategoryValue($categoryValue);

        // ---------------------------------------

        $template = $this->identifyCategoryTemplate($listingProductIds, $listingId, $categoryValue, $categoryMode);

        if ($template) {
            $specific->setInternalData($template->getData());
            $specific->setSelectedSpecifics($template->getSpecifics());
        }

        // ---------------------------------------
        $wrapper = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_view_settings_category_specific_wrapper'
        );
        $wrapper->setChild('specific', $specific);
        // ---------------------------------------

        $this->getResponse()->setBody($wrapper->toHtml());
    }

    // ---------------------------------------

    protected function getCategoryTemplate($id)
    {
        return Mage::helper('M2ePro')->getCachedObject(
            'Ebay_Template_Category', (int)$id, null, array('template')
        );
    }

    /**
     * @param array $listingProductIds
     * @param int $listingId
     * @param int $categoryValue
     * @param int $categoryMode
     * @return Ess_M2ePro_Model_Ebay_Template_Category|null
     */
    protected function identifyCategoryTemplate(array $listingProductIds, $listingId, $categoryValue, $categoryMode)
    {
        /** @var $template Ess_M2ePro_Model_Ebay_Template_Category|null */
        $template = null;
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);

        $templateIds = $this->getCategoryTemplateCandidates($listingProductIds, $listingId);
        $countTemplates = count($templateIds);

        if ($countTemplates == 0) {
            return null;
        }

        foreach ($templateIds as $templateId) {
            $tempTemplate = $this->getCategoryTemplate($templateId);

            if ($this->isMainCategoryWasChanged($tempTemplate, $categoryValue)) {
                $template = $this->loadLastCategoryTemplate(
                    $categoryMode, $categoryValue, $listing->getMarketplaceId()
                );

                return $template;
            }
        }

        if ($countTemplates == 1) {
            $templateId = reset($templateIds);
            $template = $this->getCategoryTemplate($templateId);
        } else {
            $isDifferent = false;
            for ($i = 0; $i < $countTemplates - 1; $i++) {
                $templateCurr = $this->getCategoryTemplate($templateIds[$i]);
                $templateNext = $this->getCategoryTemplate($templateIds[$i + 1]);

                $currentSnapshotBuilder = Mage::getModel('M2ePro/Ebay_Template_Category_SnapshotBuilder')
                    ->setModel($templateCurr);
                $nextSnapshotBuilder = Mage::getModel('M2ePro/Ebay_Template_Category_SnapshotBuilder')
                    ->setModel($templateNext);

                $diff = Mage::getModel('M2ePro/Ebay_Template_Category_Diff');
                $diff->setOldSnapshot($currentSnapshotBuilder->getSnapshot());
                $diff->setNewSnapshot($nextSnapshotBuilder->getSnapshot());

                if ($diff->isDifferent()) {
                    $isDifferent = true;
                    break;
                }
            }

            !$isDifferent && $template = $templateNext;
        }

        return $template;
    }

    /**
     * @param array $listingProductIds
     * @param int $listingId
     * @return array
     */
    protected function getCategoryTemplateCandidates(array $listingProductIds, $listingId)
    {
        $templateIds = Mage::getResourceModel('M2ePro/Ebay_Listing_Product')
            ->getTemplateCategoryIds($listingProductIds);
        /**
         * If there are no templates for particular listing product ids, consider action as assigning new template
         */
        if (empty($templateIds)) {
            $templateIds = Mage::getResourceModel('M2ePro/Ebay_Listing')
                ->getTemplateCategoryIds($listingId);
        }

        return array_values($templateIds);
    }

    // ---------------------------------------

    protected function isMainCategoryWasChanged(Ess_M2ePro_Model_Ebay_Template_Category $template, $selectedValue)
    {
        return $template->getData('category_main_id') != $selectedValue &&
               $template->getData('category_main_attribute') != $selectedValue;
    }

    // ---------------------------------------

    protected function loadLastCategoryTemplate($mode, $categoryValue, $marketplaceId)
    {
        $templateData = array(
            'category_main_id'        => 0,
            'category_main_mode'      => $mode,
            'category_main_attribute' => '',
            'marketplace_id'          => $marketplaceId
        );

        if ($mode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
            $templateData['category_main_id'] = $categoryValue;
        } elseif ($mode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            $templateData['category_main_attribute'] = $categoryValue;
        }

        $existingTemplates = Mage::getModel('M2ePro/Ebay_Template_Category')->getCollection()
                                   ->getItemsByPrimaryCategories(array($templateData));

        return reset($existingTemplates);
    }

    //########################################

    public function saveCategoryTemplateAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return;
        }

        if (!isset($post['template_category_data'])) {
            return;
        }

        // ---------------------------------------
        $listingId = $this->getRequest()->getParam('listing_id');
        $listingProductIds = $this->getRequestIds();
        $categoryTemplateData = $post['template_category_data'];
        $categoryTemplateData = Mage::helper('M2ePro')->jsonDecode($categoryTemplateData);
        // ---------------------------------------

        // ---------------------------------------
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        // ---------------------------------------

        // ---------------------------------------
        Mage::helper('M2ePro/Component_Ebay_Category')->fillCategoriesPaths($categoryTemplateData, $listing);

        $builderData = $categoryTemplateData;
        $builderData['account_id'] = $listing->getAccountId();
        $builderData['marketplace_id'] = $listing->getMarketplaceId();

        // ---------------------------------------
        $builder = Mage::getModel('M2ePro/Ebay_Template_Category_Builder');
        $categoryTemplate = $builder->build($builderData);
        // ---------------------------------------
        $builder = Mage::getModel('M2ePro/Ebay_Template_OtherCategory_Builder');
        $otherCategoryTemplate = $builder->build($builderData);
        // ---------------------------------------

        $this->assignTemplatesToProducts(
            $categoryTemplate->getId(), $otherCategoryTemplate->getId(), $listingProductIds
        );
    }

    //########################################

    public function getListingProductBidsAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('You should provide correct parameters');
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $listingProduct */
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
            $message = 'The action is temporarily unavailable. M2E Pro server is currently';
            $message .= ' under the planned maintenance. Please try again later.';

            return Mage::helper('M2ePro')->__($message);
        }

        if (!$listingsProductsIds = $this->getRequest()->getParam('selected_products')) {
            return 'You should select Products';
        }

        $logsActionId = Mage::getModel('M2ePro/Listing_Log')->getResource()->getNextActionId();

        $params['logs_action_id'] = $logsActionId;
        $params['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER;
        $params['is_realtime']    = true;

        $listingsProductsIds = explode(',', $listingsProductsIds);

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingsProductsCollection */
        $listingsProductsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $listingsProductsCollection->addFieldToFilter('id', $listingsProductsIds);

        /** @var Ess_M2ePro_Model_Listing_Product[] $listingsProducts */
        $listingsProducts = $listingsProductsCollection->getItems();

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

        if (empty($listingsProducts)) {
            return Mage::helper('M2ePro')->jsonEncode(array('result'=>'error','action_id'=>$logsActionId));
        }

        if ($action == Ess_M2ePro_Model_Listing_Product::ACTION_STOP && !empty($params['remove'])) {
            foreach ($listingsProducts as $index => $listingProduct) {
                if ($listingProduct->isStoppable()) {
                    continue;
                }

                $removeHandler = Mage::getModel(
                    'M2ePro/Listing_Product_RemoveHandler', array('listing_product' => $listingProduct)
                );
                $removeHandler->process();

                unset($listingsProducts[$index]);
            }
        }

        if (empty($listingsProducts)) {
            return Mage::helper('M2ePro')->jsonEncode(array('result'=>'success','action_id'=>$logsActionId));
        }

        $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Item_Dispatcher');
        $result = (int)$dispatcherObject->process($action, $listingsProducts, $params);

        if ($result == Ess_M2ePro_Helper_Data::STATUS_ERROR) {
            return Mage::helper('M2ePro')->jsonEncode(array('result'=>'error','action_id'=>$logsActionId));
        }

        if ($result == Ess_M2ePro_Helper_Data::STATUS_WARNING) {
            return Mage::helper('M2ePro')->jsonEncode(array('result'=>'warning','action_id'=>$logsActionId));
        }

        if ($result == Ess_M2ePro_Helper_Data::STATUS_SUCCESS) {
            return Mage::helper('M2ePro')->jsonEncode(array('result'=>'success','action_id'=>$logsActionId));
        }

        return Mage::helper('M2ePro')->jsonEncode(array('result'=>'error','action_id'=>$logsActionId));
    }

    protected function scheduleAction($action, array $params = array())
    {
        if (!$listingsProductsIds = $this->getRequest()->getParam('selected_products')) {
            return 'You should select Products';
        }

        $params['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER;

        $listingsProductsIds = explode(',', $listingsProductsIds);

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingsProductsCollection */
        $listingsProductsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $listingsProductsCollection->addFieldToFilter('id', $listingsProductsIds);

        /** @var Ess_M2ePro_Model_Listing_Product[] $listingsProducts */
        $listingsProducts = $listingsProductsCollection->getItems();

        $logsActionId = Mage::getModel('M2ePro/Listing_Log')->getResource()->getNextActionId();

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

        if (empty($listingsProducts)) {
            return Mage::helper('M2ePro')->jsonEncode(array('result'=>'error', 'action_id' => $logsActionId));
        }

        if ($action == Ess_M2ePro_Model_Listing_Product::ACTION_STOP && !empty($params['remove'])) {
            foreach ($listingsProducts as $index => $listingProduct) {
                if ($listingProduct->isStoppable()) {
                    continue;
                }

                $removeHandler = Mage::getModel(
                    'M2ePro/Listing_Product_RemoveHandler', array('listing_product' => $listingProduct)
                );
                $removeHandler->process();

                unset($listingsProducts[$index]);
            }
        }

        if (empty($listingsProducts)) {
            return Mage::helper('M2ePro')->jsonEncode(array('result'=>'success', 'action_id' => $logsActionId));
        }

        $existedScheduledActionsCollection = Mage::getResourceModel(
            'M2ePro/Listing_Product_ScheduledAction_Collection'
        );
        $existedScheduledActionsCollection->addFieldToFilter('listing_product_id', $listingsProductsIds);

        $additionalData = array(
            'params' => $params,
        );
        $tag = null;

        if ($action == Ess_M2ePro_Model_Listing_Product::ACTION_REVISE) {
            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
            $configurator->enableAll();

            $additionalData['configurator'] = $configurator->getData();

            $tag = '/qty/price/title/subtitle/description/images/categories/payment/shipping/return/other/';
        }

        $scheduledActionManager = Mage::getModel('M2ePro/Listing_Product_ScheduledAction_Manager');

        foreach ($listingsProducts as $listingProduct) {
            $scheduledAction = Mage::getModel('M2ePro/Listing_Product_ScheduledAction');
            $scheduledAction->setData(
                array(
                'listing_product_id' => $listingProduct->getId(),
                'component'          => Ess_M2ePro_Helper_Component_Ebay::NICK,
                'action_type'        => $action,
                'is_force'           => true,
                'tag'                => $tag,
                'additional_data'    => Mage::helper('M2ePro')->jsonEncode($additionalData),
                )
            );

            if ($existedScheduledActionsCollection->getItemByColumnValue(
                'listing_product_id', $listingProduct->getId()
            )) {
                $scheduledActionManager->updateAction($scheduledAction);
            } else {
                $scheduledActionManager->addAction($scheduledAction);
            }
        }

        return Mage::helper('M2ePro')->jsonEncode(array('result'=>'success', 'action_id' => $logsActionId));
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
        if (Mage::helper('M2ePro')->jsonDecode($this->getRequest()->getParam('is_realtime'))) {
            return $this->getResponse()->setBody(
                $this->processConnector(Ess_M2ePro_Model_Listing_Product::ACTION_STOP, array('remove' => true))
            );
        }

        return $this->getResponse()->setBody(
            $this->scheduleAction(Ess_M2ePro_Model_Listing_Product::ACTION_STOP, array('remove' => true))
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

    protected function assignTemplatesToProducts($categoryTemplateId, $otherCategoryTemplateId, $productsIds)
    {
        if (empty($productsIds)) {
            return;
        }

        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('id', array('in' => $productsIds));
        // ---------------------------------------

        if ($collection->getSize() == 0) {
            return;
        }

        $transaction = Mage::getModel('core/resource_transaction');

        $snapshots = array();

        try {
            foreach ($collection->getItems() as $listingProduct) {
                $snapshotBuilder = Mage::getModel('M2ePro/Ebay_Listing_Product_SnapshotBuilder');
                $snapshotBuilder->setModel($listingProduct);

                $snapshots[$listingProduct->getId()] = $snapshotBuilder->getSnapshot();

                $listingProduct->setData('template_category_id', $categoryTemplateId);
                $listingProduct->setData('template_other_category_id', $otherCategoryTemplateId);

                $transaction->addObject($listingProduct);
            }

            $transaction->save();
        } catch (Exception $e) {
            $snapshots = false;
            $transaction->rollback();
        }

        if (!$snapshots) {
            return;
        }

        $templateManager = Mage::getSingleton('M2ePro/Ebay_Template_Manager');

        foreach ($collection->getItems() as $listingProduct) {
            $snapshotBuilder = Mage::getModel('M2ePro/Ebay_Listing_Product_SnapshotBuilder');
            $snapshotBuilder->setModel($listingProduct);

            $newData = $snapshotBuilder->getSnapshot();

            $newTemplates = $templateManager->getTemplatesFromData($newData);
            $oldTemplates = $templateManager->getTemplatesFromData($snapshots[$listingProduct->getId()]);

            foreach ($templateManager->getAllTemplates() as $template) {
                $templateManager->setTemplate($template);

                /** @var Ess_M2ePro_Model_Template_SnapshotBuilder_Abstract $snapshotBuilder */
                if ($templateManager->isHorizontalTemplate()) {
                    $snapshotBuilder = Mage::getModel(
                        'M2ePro/Ebay_'.$templateManager->getTemplateModelName().'_SnapshotBuilder'
                    );
                } else {
                    $snapshotBuilder = Mage::getModel(
                        'M2ePro/'.$templateManager->getTemplateModelName().'_SnapshotBuilder'
                    );
                }

                $snapshotBuilder->setModel($newTemplates[$template]);

                $newTemplateData = $snapshotBuilder->getSnapshot();

                /** @var Ess_M2ePro_Model_Template_SnapshotBuilder_Abstract $snapshotBuilder */
                if ($templateManager->isHorizontalTemplate()) {
                    $snapshotBuilder = Mage::getModel(
                        'M2ePro/Ebay_'.$templateManager->getTemplateModelName().'_SnapshotBuilder'
                    );
                } else {
                    $snapshotBuilder = Mage::getModel(
                        'M2ePro/'.$templateManager->getTemplateModelName().'_SnapshotBuilder'
                    );
                }

                $snapshotBuilder->setModel($oldTemplates[$template]);

                $oldTemplateData = $snapshotBuilder->getSnapshot();

                /** @var Ess_M2ePro_Model_Template_Diff_Abstract $diff */
                if ($templateManager->isHorizontalTemplate()) {
                    $diff = Mage::getModel('M2ePro/Ebay_'.$templateManager->getTemplateModelName().'_Diff');
                } else {
                    $diff = Mage::getModel('M2ePro/'.$templateManager->getTemplateModelName().'_Diff');
                }

                $diff->setNewSnapshot($newTemplateData);
                $diff->setOldSnapshot($oldTemplateData);

                /** @var Ess_M2ePro_Model_Template_ChangeProcessor_Abstract $changeProcessor */
                if ($templateManager->isHorizontalTemplate()) {
                    $changeProcessor = Mage::getModel(
                        'M2ePro/Ebay_'.$templateManager->getTemplateModelName().'_ChangeProcessor'
                    );
                } else {
                    $changeProcessor = Mage::getModel(
                        'M2ePro/'.$templateManager->getTemplateModelName().'_ChangeProcessor'
                    );
                }

                $changeProcessor->process(
                    $diff, array(array('id' => $listingProduct->getId(), 'status' => $listingProduct->getStatus()))
                );
            }

            $this->processCategoryTemplateChange($listingProduct, $newData, $snapshots[$listingProduct->getId()]);
            $this->processOtherCategoryTemplateChange($listingProduct, $newData, $snapshots[$listingProduct->getId()]);
        }
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

    protected function processCategoryTemplateChange($listingProduct, array $newData, array $oldData)
    {
        $newTemplateSnapshot = array();

        try {
            $snapshotBuilder = Mage::getModel('M2ePro/Ebay_Template_Category_SnapshotBuilder');

            $newTemplate = Mage::helper('M2ePro')
                ->getCachedObject(
                    'Ebay_Template_Category',
                    $newData['template_category_id'],
                    null, array('template')
                );
            $snapshotBuilder->setModel($newTemplate);

            $newTemplateSnapshot = $snapshotBuilder->getSnapshot();
        } catch (Exception $exception) {
        }

        $oldTemplateSnapshot = array();

        try {
            $snapshotBuilder = Mage::getModel('M2ePro/Ebay_Template_Category_SnapshotBuilder');

            $oldTemplate = Mage::helper('M2ePro')
                ->getCachedObject(
                    'Ebay_Template_Category',
                    $oldData['template_category_id'],
                    null, array('template')
                );
            $snapshotBuilder->setModel($oldTemplate);

            $oldTemplateSnapshot = $snapshotBuilder->getSnapshot();
        } catch (Exception $exception) {
        }

        $diff = Mage::getModel('M2ePro/Ebay_Template_Category_Diff');
        $diff->setNewSnapshot($newTemplateSnapshot);
        $diff->setOldSnapshot($oldTemplateSnapshot);

        $changeProcessor = Mage::getModel('M2ePro/Ebay_Template_Category_ChangeProcessor');
        $changeProcessor->process(
            $diff, array(array('id' => $listingProduct->getId(), 'status' => $listingProduct->getStatus()))
        );
    }

    protected function processOtherCategoryTemplateChange($listingProduct, array $newData, array $oldData)
    {
        $newTemplateSnapshot = array();

        try {
            $snapshotBuilder = Mage::getModel('M2ePro/Ebay_Template_OtherCategory_SnapshotBuilder');

            $newTemplate = Mage::helper('M2ePro')
                ->getCachedObject(
                    'Ebay_Template_OtherCategory',
                    $newData['template_category_other_id'],
                    null, array('template')
                );
            $snapshotBuilder->setModel($newTemplate);

            $newTemplateSnapshot = $snapshotBuilder->getSnapshot();
        } catch (Exception $exception) {
        }

        $oldTemplateSnapshot = array();

        try {
            $snapshotBuilder = Mage::getModel('M2ePro/Ebay_Template_OtherCategory_SnapshotBuilder');

            $oldTemplate = Mage::helper('M2ePro')
                ->getCachedObject(
                    'Ebay_Template_OtherCategory',
                    $oldData['template_category_other_id'],
                    null, array('template')
                );
            $snapshotBuilder->setModel($oldTemplate);

            $oldTemplateSnapshot = $snapshotBuilder->getSnapshot();
        } catch (Exception $exception) {
        }

        $diff = Mage::getModel('M2ePro/Ebay_Template_OtherCategory_Diff');
        $diff->setNewSnapshot($newTemplateSnapshot);
        $diff->setOldSnapshot($oldTemplateSnapshot);

        $changeProcessor = Mage::getModel('M2ePro/Ebay_Template_OtherCategory_ChangeProcessor');
        $changeProcessor->process(
            $diff, array(array('id' => $listingProduct->getId(), 'status' => $listingProduct->getStatus()))
        );
    }

    //########################################
}
