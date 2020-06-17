<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_View extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    const VIEW_MODE_WALMART  = 'walmart';
    const VIEW_MODE_MAGENTO  = 'magento';
    const VIEW_MODE_SETTINGS = 'settings';

    const DEFAULT_VIEW_MODE = self::VIEW_MODE_WALMART;

    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing = null;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->_listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $this->setId('walmartListingView');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_walmart_listing_view_' . $this->getViewMode();


        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Walmart')->getTitle();
            $this->_headerText = Mage::helper('M2ePro')->__('View %component_name% Listing ', $componentName);
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('View Listing ');
        }

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = $this->getUrl(
            '*/adminhtml_walmart_log/listing', array(
            'id' => $this->_listing->getId()
            )
        );
        $this->_addButton(
            'view_log', array(
            'label'   => Mage::helper('M2ePro')->__('Logs & Events'),
            'onclick' => 'window.open(\'' . $url . '\')',
            'class'   => 'button_link'
            )
        );

        $this->_addButton(
            'edit_settings', array(
            'label'   => Mage::helper('M2ePro')->__('Edit Settings'),
            'onclick' => '',
            'class'   => 'drop_down edit_settings_drop_down'
            )
        );

        $this->_addButton(
            'add_products', array(
            'label'     => Mage::helper('M2ePro')->__('Add Products'),
            'onclick'   => '',
            'class'     => 'add drop_down add_products_drop_down'
            )
        );
    }

    //########################################

    public function getViewMode()
    {
        $allowedModes = array(
            self::VIEW_MODE_WALMART,
            self::VIEW_MODE_SETTINGS,
            self::VIEW_MODE_MAGENTO
        );
        $mode = $this->getParam('view_mode', self::DEFAULT_VIEW_MODE);

        if (in_array($mode, $allowedModes)) {
            return $mode;
        }

        return self::DEFAULT_VIEW_MODE;
    }

    protected function getParam($paramName, $default = null)
    {
        $session = Mage::helper('M2ePro/Data_Session');
        $sessionParamName = $this->getId() . $this->_listing->getId() . $paramName;

        if ($this->getRequest()->has($paramName)) {
            $param = $this->getRequest()->getParam($paramName);
            $session->setValue($sessionParamName, $param);
            return $param;
        } elseif ($param = $session->getValue($sessionParamName)) {
            return $param;
        }

        return $default;
    }

    //########################################

    protected function _toHtml()
    {
        return '<div id="listing_view_progress_bar"></div>' .
               '<div id="listing_container_errors_summary" class="errors_summary" style="display: none;"></div>' .
               '<div id="listing_view_content_container">'.
               parent::_toHtml() .
               '</div>';
    }

    public function getGridHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::getGridHtml();
        }

        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');

        // ---------------------------------------
        $urls = $helper->getControllerActions(
            'adminhtml_walmart_listing_autoAction', array(
                'listing_id' => $this->getRequest()->getParam('id'),
                'component' => Ess_M2ePro_Helper_Component_Walmart::NICK
            )
        );
        $showAutoAction   = Mage::helper('M2ePro')->jsonEncode((bool)$this->getRequest()->getParam('auto_actions'));

        $path = 'adminhtml_walmart_log/listingProduct';
        $urls[$path] = $this->getUrl(
            '*/' . $path, array(
            'channel' => Ess_M2ePro_Helper_Component_Walmart::NICK,
            'back' => $helper->makeBackUrlParam(
                '*/adminhtml_walmart_listing/view', array(
                'id' => $this->_listing->getId()
                )
            )
            )
        );

        $path = 'adminhtml_walmart_listing/duplicateProducts';
        $urls[$path] = $this->getUrl('*/' . $path);

        $urls['adminhtml_walmart_listing/getEditSkuPopup'] = $this->getUrl(
            '*/adminhtml_walmart_listing/getEditSkuPopup'
        );
        $urls['adminhtml_walmart_listing/editSku'] = $this->getUrl(
            '*/adminhtml_walmart_listing/editSku'
        );
        $urls['adminhtml_walmart_listing/getEditIdentifiersPopup'] = $this->getUrl(
            '*/adminhtml_walmart_listing/getEditIdentifiersPopup'
        );
        $urls['adminhtml_walmart_listing/editIdentifier'] = $this->getUrl(
            '*/adminhtml_walmart_listing/editIdentifier'
        );

        $urls['adminhtml_walmart_listing/runResetProducts'] = $this->getUrl(
            '*/adminhtml_walmart_listing/runResetProducts'
        );

        $urls = Mage::helper('M2ePro')->jsonEncode($urls);
        // ---------------------------------------

        $component = Ess_M2ePro_Helper_Component_Walmart::NICK;

        $temp = Mage::helper('M2ePro/Data_Session')->getValue('products_ids_for_list', true);
        $productsIdsForList = empty($temp) ? '' : $temp;

        $gridId = $this->getChild('grid')->getId();
        $ignoreListings = Mage::helper('M2ePro')->jsonEncode(array($this->_listing->getId()));

        $marketplace = Mage::helper('M2ePro')->jsonEncode($this->_listing->getMarketplace()->getData());

        $logViewUrl = $this->getUrl(
            '*/adminhtml_walmart_log/listing', array(
            'id' => $this->_listing->getId(),
            'back' => $helper->makeBackUrlParam(
                '*/adminhtml_walmart_listing/view',
                array('id'  => $this->_listing->getId())
            )
            )
        );
        $getErrorsSummary = $this->getUrl('*/adminhtml_listing/getErrorsSummary');

        $runListProducts = $this->getUrl('*/adminhtml_walmart_listing/runListProducts');
        $runReviseProducts = $this->getUrl('*/adminhtml_walmart_listing/runReviseProducts');
        $runRelistProducts = $this->getUrl('*/adminhtml_walmart_listing/runRelistProducts');
        $runStopProducts = $this->getUrl('*/adminhtml_walmart_listing/runStopProducts');
        $runStopAndRemoveProducts = $this->getUrl('*/adminhtml_walmart_listing/runStopAndRemoveProducts');
        $runDeleteAndRemoveProducts = $this->getUrl('*/adminhtml_walmart_listing/runDeleteAndRemoveProducts');
        $runResetProducts = $this->getUrl('*/adminhtml_walmart_listing/runResetProducts');

        $prepareData = $this->getUrl('*/adminhtml_listing_moving/prepareMoveToListing');
        $getMoveToListingGridHtml = $this->getUrl('*/adminhtml_listing_moving/moveToListingGrid');
        $moveToListing = $this->getUrl('*/adminhtml_listing_moving/moveToListing');

        $marketplaceSynchUrl = $this->getUrl(
            '*/adminhtml_walmart_marketplace/index'
        );

        $getVariationEditPopupUrl = $this->getUrl('*/adminhtml_walmart_listing/getVariationEditPopup');
        $getVariationManagePopupUrl = $this->getUrl('*/adminhtml_walmart_listing/getVariationManagePopup');

        $variationEditActionUrl = $this->getUrl('*/adminhtml_walmart_listing/variationEdit');
        $variationManageActionUrl = $this->getUrl('*/adminhtml_walmart_listing/variationManage');
        $variationManageGenerateActionUrl = $this->getUrl('*/adminhtml_walmart_listing/variationManageGenerate');
        $variationResetActionUrl = $this->getUrl('*/adminhtml_walmart_listing/variationReset');

        $saveListingAdditionalDataActionUrl = $this->getUrl(
            '*/adminhtml_listing/saveListingAdditionalData', array(
            'id' => $this->_listing->getId()
            )
        );

        $popupTitle = $helper->escapeJs($helper->__('Moving Walmart Items'));

        $lockedObjNoticeMessage = $helper->escapeJs($helper->__('Some Walmart request(s) are being processed now.'));
        $sendingDataToWalmartMessage = $helper->escapeJs(
            $helper->__(
                'Sending %product_title% Product(s) data on Walmart.'
            )
        );
        $viewAllProductLogMessage = $helper->escapeJs($helper->__('View All Product Log.'));

        $listingLockedMessage = $helper->escapeJs(
            $helper->__('The Listing was locked by another process. Please try again later.')
        );
        $listingEmptyMessage = $helper->escapeJs($helper->__('Listing is empty.'));

        $listingAllItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')
            ->__('Listing All Items On Walmart')
        );
        $listingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')
            ->__('Listing Selected Items On Walmart')
        );
        $revisingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')
            ->__('Revising Selected Items On Walmart')
        );
        $relistingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')
            ->__('Relisting Selected Items On Walmart')
        );
        $stoppingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')
            ->__('Stopping Selected Items On Walmart')
        );
        $stoppingAndRemovingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Stopping On Walmart And Removing From Listing Selected Items')
        );
        $deletingAndRemovingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Retiring From Walmart And Removing from Listing Selected Items')
        );
        $resetBlockedProductsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Reset Inactive (Blocked) Items')
        );

        $removingSelectedItemsMessage = $helper->escapeJs($helper->__('Removing From Listing Selected Items'));

        $variationProductManage = $this->getUrl(
            '*/adminhtml_walmart_listing_variation_product_manage/index'
        );
        $variationProductSetChannelAttributes = $this->getUrl(
            '*/adminhtml_walmart_listing_variation_product_manage/setChannelAttributes'
        );
        $variationProductSetSwatchImagesAttribute = $this->getUrl(
            '*/adminhtml_walmart_listing_variation_product_manage/setSwatchImagesAttribute'
        );
        $variationProductSetMatchedAttributes = $this->getUrl(
            '*/adminhtml_walmart_listing_variation_product_manage/setMatchedAttributes'
        );
        $variationProductSetListingProductSku = $this->getUrl(
            '*/adminhtml_walmart_listing_variation_product_manage/setListingProductSku'
        );
        $manageVariationViewTemplateDescriptionsGrid = $this->getUrl(
            '*/adminhtml_walmart_listing_variation_product_manage/viewTemplateDescriptionsGrid'
        );
        $manageVariationMapToTemplateDescription = $this->getUrl(
            '*/adminhtml_walmart_listing_variation_product_manage/mapToTemplateDescription'
        );
        $addAttributesToVocabularyUrl = $this->getUrl(
            '*/adminhtml_walmart_listing_variation_product_manage/addAttributesToVocabulary'
        );
        $addOptionsToVocabularyUrl = $this->getUrl(
            '*/adminhtml_walmart_listing_variation_product_manage/addOptionsToVocabulary'
        );

        $viewVocabularyAjax = $this->getUrl(
            '*/adminhtml_walmart_listing_variation_product_manage/viewVocabularyAjax'
        );
        $saveAutoActionSettings = $this->getUrl(
            '*/adminhtml_walmart_listing_variation_product_manage/saveAutoActionSettings'
        );
        $removeAttributeFromVocabulary = $this->getUrl(
            '*/adminhtml_walmart_listing_variation_product_manage/removeAttributeFromVocabulary'
        );
        $removeOptionFromVocabulary = $this->getUrl(
            '*/adminhtml_walmart_listing_variation_product_manage/removeOptionFromVocabulary'
        );

        $viewVariationsSettingsAjax = $this->getUrl(
            '*/adminhtml_walmart_listing_variation_product_manage/viewVariationsSettingsAjax'
        );

        $categoryTemplateUrl = $this->getUrl(
            '*/adminhtml_walmart_template_newProduct', array(
            'marketplace_id' => $this->_listing->getMarketplaceId(),
            )
        );

        $mapToTemplateCategory = $this->getUrl('*/adminhtml_walmart_listing/mapToTemplateCategory');
        $unmapFromTemplateDescription = $this->getUrl('*/adminhtml_walmart_listing/unmapFromTemplateDescription');
        $validateProductsForTemplateCategoryAssign = $this->getUrl(
            '*/adminhtml_walmart_listing/validateProductsForTemplateCategoryAssign'
        );
        $viewTemplateCategoriesGrid = $this->getUrl('*/adminhtml_walmart_listing/viewTemplateCategoriesGrid');

        $templateDescriptionPopupTitle = $helper->escapeJs($helper->__('Assign Description Policy'));

        $noVariationsLeftText = $helper->__('All variations are already added.');

        $setAttributes = $helper->__('Set Attributes');
        $variationManageMatchedAttributesError = $helper->__('Please choose valid Attributes.');
        $variationManageMatchedAttributesErrorDuplicateSelection =
            $helper->__('You can not choose the same Attribute twice.');

        $variationManageSkuPopUpTitle =
            $helper->__('Enter Walmart Parent Product SKU');

        $switchToIndividualModePopUpTitle = $helper->__('Change "Manage Variations" Mode');
        $switchToParentModePopUpTitle = $helper->__('Change "Manage Variations" Mode');

        $emptySkuError = $helper->escapeJs($helper->__('Please enter Walmart Parent Product SKU.'));

        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
            'Auto Add/Remove Rules'       => $helper->__('Auto Add/Remove Rules'),
            'Based on Magento Categories' => $helper->__('Based on Magento Categories'),
            'You must select at least 1 Category.'     => $helper->__('You must select at least 1 Category.'),
            'Rule with the same Title already exists.' => $helper->__('Rule with the same Title already exists.'),
            'Edit SKU'        => $helper->__('Edit SKU'),
            'Edit Product ID' => $helper->__('Edit Product ID'),

            'Updating SKU has successfully submitted to be processed.' =>
                $helper->__('Updating SKU has successfully submitted to be processed.'),
            'Updating GTIN has successfully submitted to be processed.' =>
                $helper->__('Updating GTIN has successfully submitted to be processed.'),
            'Updating UPC has successfully submitted to be processed.' =>
                $helper->__('Updating UPC has successfully submitted to be processed.'),
            'Updating EAN has successfully submitted to be processed.' =>
                $helper->__('Updating EAN has successfully submitted to be processed.'),
            'Updating ISBN has successfully submitted to be processed.' =>
                $helper->__('Updating ISBN has successfully submitted to be processed.'),

            'Required at least one identifier' => $helper->__('Required at least one identifier'),
            'At least one Variant Attribute must be selected.' =>
                $helper->__('At least one Variant Attribute must be selected.'),

            'SKU contains the special characters that are not allowed by Walmart.' => $helper->__(
                'Hyphen (-), space ( ), and period (.) are not allowed by Walmart. Please use a correct format.'
            )
            )
        );

        $constants = Mage::helper('M2ePro')
            ->getClassConstantAsJson('Ess_M2ePro_Model_Walmart_Account');

        $javascriptsMain = <<<HTML
<script type="text/javascript">

    if (typeof M2ePro == 'undefined') {
        M2ePro = {};
        M2ePro.url = {};
        M2ePro.formData = {};
        M2ePro.customData = {};
        M2ePro.text = {};
    }

    M2ePro.php.setConstants(
        {$constants}, 'Ess_M2ePro_Model_Walmart_Account'
    );

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});

    M2ePro.productsIdsForList = '{$productsIdsForList}';

    M2ePro.url.logViewUrl = '{$logViewUrl}';
    M2ePro.url.getErrorsSummary = '{$getErrorsSummary}';

    M2ePro.url.runListProducts = '{$runListProducts}';
    M2ePro.url.runReviseProducts = '{$runReviseProducts}';
    M2ePro.url.runRelistProducts = '{$runRelistProducts}';
    M2ePro.url.runStopProducts = '{$runStopProducts}';
    M2ePro.url.runStopAndRemoveProducts = '{$runStopAndRemoveProducts}';
    M2ePro.url.runDeleteAndRemoveProducts = '{$runDeleteAndRemoveProducts}';
    M2ePro.url.runResetProducts = '{$runResetProducts}';

    M2ePro.url.variationProductManage = '{$variationProductManage}';
    M2ePro.url.variationProductSetChannelAttributes = '{$variationProductSetChannelAttributes}';
    M2ePro.url.variationProductSetSwatchImagesAttribute = '{$variationProductSetSwatchImagesAttribute}';
    M2ePro.url.variationProductSetMatchedAttributes = '{$variationProductSetMatchedAttributes}';
    M2ePro.url.variationProductSetListingProductSku = '{$variationProductSetListingProductSku}';
    M2ePro.url.variationProductSetListingProductSku = '{$variationProductSetListingProductSku}';
    M2ePro.url.manageVariationViewTemplateDescriptionsGrid = '{$manageVariationViewTemplateDescriptionsGrid}';
    M2ePro.url.manageVariationMapToTemplateDescription = '{$manageVariationMapToTemplateDescription}';
    M2ePro.url.viewVariationsSettingsAjax = '{$viewVariationsSettingsAjax}';
    M2ePro.url.addAttributesToVocabulary = '{$addAttributesToVocabularyUrl}';
    M2ePro.url.addOptionsToVocabulary = '{$addOptionsToVocabularyUrl}';
    M2ePro.url.viewVocabularyAjax = '{$viewVocabularyAjax}';
    M2ePro.url.saveAutoActionSettings = '{$saveAutoActionSettings}';
    M2ePro.url.removeAttributeFromVocabulary = '{$removeAttributeFromVocabulary}';
    M2ePro.url.removeOptionFromVocabulary = '{$removeOptionFromVocabulary}';

    M2ePro.url.categoryTemplate = '{$categoryTemplateUrl}';

    M2ePro.url.mapToTemplateCategory = '{$mapToTemplateCategory}';
    M2ePro.url.unmapFromTemplateDescription = '{$unmapFromTemplateDescription}';
    M2ePro.url.validateProductsForTemplateCategoryAssign = '{$validateProductsForTemplateCategoryAssign}';
    M2ePro.url.viewTemplateCategoriesGrid = '{$viewTemplateCategoriesGrid}';

    M2ePro.url.prepareData = '{$prepareData}';
    M2ePro.url.getGridHtml = '{$getMoveToListingGridHtml}';
    M2ePro.url.moveToListing = '{$moveToListing}';

    M2ePro.url.marketplace_synch = '{$marketplaceSynchUrl}';

    M2ePro.url.get_variation_edit_popup = '{$getVariationEditPopupUrl}';
    M2ePro.url.get_variation_manage_popup = '{$getVariationManagePopupUrl}';

    M2ePro.url.variation_edit_action = '{$variationEditActionUrl}';
    M2ePro.url.variation_manage_action = '{$variationManageActionUrl}';
    M2ePro.url.variation_manage_generate_action = '{$variationManageGenerateActionUrl}';
    M2ePro.url.variation_reset_action = '{$variationResetActionUrl}';

    M2ePro.url.save_listing_additional_data = '{$saveListingAdditionalDataActionUrl}';

    M2ePro.text.popup_title = '{$popupTitle}';

    M2ePro.text.locked_obj_notice = '{$lockedObjNoticeMessage}';
    M2ePro.text.sending_data_message = '{$sendingDataToWalmartMessage}';
    M2ePro.text.view_all_product_log_message = '{$viewAllProductLogMessage}';

    M2ePro.text.listing_locked_message = '{$listingLockedMessage}';
    M2ePro.text.listing_empty_message = '{$listingEmptyMessage}';

    M2ePro.text.listing_all_items_message = '{$listingAllItemsMessage}';
    M2ePro.text.listing_selected_items_message = '{$listingSelectedItemsMessage}';
    M2ePro.text.revising_selected_items_message = '{$revisingSelectedItemsMessage}';
    M2ePro.text.relisting_selected_items_message = '{$relistingSelectedItemsMessage}';
    M2ePro.text.stopping_selected_items_message = '{$stoppingSelectedItemsMessage}';
    M2ePro.text.stopping_and_removing_selected_items_message = '{$stoppingAndRemovingSelectedItemsMessage}';
    M2ePro.text.deleting_and_removing_selected_items_message = '{$deletingAndRemovingSelectedItemsMessage}';
    M2ePro.text.removing_selected_items_message = '{$removingSelectedItemsMessage}';
    M2ePro.text.reset_blocked_products_message = '{$resetBlockedProductsMessage}';

    M2ePro.text.templateDescriptionPopupTitle = '{$templateDescriptionPopupTitle}';

    M2ePro.text.no_variations_left = '{$noVariationsLeftText}';

    M2ePro.text.variation_manage_matched_attributes_error = '{$variationManageMatchedAttributesError}';
    M2ePro.text.variation_manage_matched_attributes_error_duplicate =
        '{$variationManageMatchedAttributesErrorDuplicateSelection}';

    M2ePro.text.variation_manage_matched_sku_popup_title = '{$variationManageSkuPopUpTitle}';
    M2ePro.text.empty_sku_error = '{$emptySkuError}';

    M2ePro.text.switch_to_individual_mode_popup_title = '{$switchToIndividualModePopUpTitle}';
    M2ePro.text.switch_to_parent_mode_popup_title = '{$switchToParentModePopUpTitle}';

    M2ePro.customData.componentMode = '{$component}';
    M2ePro.customData.gridId = '{$gridId}';
    M2ePro.customData.ignoreListings = '{$ignoreListings}';

    M2ePro.customData.marketplace = {$marketplace};

    Event.observe(window, 'load', function() {

        ListingGridObj = new WalmartListingGrid(
            '{$gridId}',
            {$this->_listing->getId()}
        );

        ListingProgressBarObj = new ProgressBar('listing_view_progress_bar');
        GridWrapperObj = new AreaWrapper('listing_view_content_container');

        ListingProductVariationObj = new WalmartListingProductVariation(M2ePro,
                                                                               ListingGridObj);

        if (M2ePro.productsIdsForList) {
            ListingGridObj.getGridMassActionObj().checkedString = M2ePro.productsIdsForList;
            ListingGridObj.actionHandler.listAction();
        }

        ListingAutoActionObj = new WalmartListingAutoAction();
        if ({$showAutoAction}) {
            ListingAutoActionObj.loadAutoActionHtml();
        }

    });

</script>
HTML;

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_view_help');

        // ---------------------------------------
        $data = array(
            'target_css_class' => 'edit_settings_drop_down',
            'items'            => $this->getTemplatesButtonDropDownItems()
        );
        $templatesDropDownBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown');
        $templatesDropDownBlock->setData($data);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'target_css_class' => 'add_products_drop_down',
            'items'            => $this->getAddProductsDropDownItems()
        );
        $addProductsDropDownBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown');
        $addProductsDropDownBlock->setData($data);
        // ---------------------------------------

        // ---------------------------------------
        $listingSwitcher = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_listing_view_listingSwitcher'
        );
        // ---------------------------------------

        // ---------------------------------------
        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header', '',
            array(
                'listing' => Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
                    'Listing',
                    $this->_listing->getId())
            )
        );
        // ---------------------------------------

        // ---------------------------------------
        $switchToIndividualPopup = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_listing_variation_product_switchToIndividualPopup'
        );
        // ---------------------------------------

        // ---------------------------------------
        $switchToParentPopup = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_listing_variation_product_switchToParentPopup'
        );
        // ---------------------------------------

        return $javascriptsMain
            . $templatesDropDownBlock->toHtml()
            . $listingSwitcher->toHtml()
            . $addProductsDropDownBlock->toHtml()
            . $helpBlock->toHtml()
            . $viewHeaderBlock->toHtml()
            . $switchToIndividualPopup->toHtml()
            . $switchToParentPopup->toHtml()
            . parent::getGridHtml();
    }

    public function getHeaderHtml()
    {
        $collection = Mage::getModel('M2ePro/Listing')->getCollection();
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Walmart::NICK);
        $collection->addFieldToFilter('id', array('neq' => $this->_listing->getId()));
        $collection->setPageSize(200);
        $collection->setOrder('title', 'ASC');

        $items = array();
        foreach ($collection->getItems() as $item) {
            $items[] = array(
                'label' => $item->getTitle(),
                'url' => $this->getUrl('*/*/view', array('id' => $item->getId()))
            );
        }

        // ---------------------------------------

        if (empty($items)) {
            return parent::getHeaderHtml();
        }

        // ---------------------------------------
        $data = array(
            'target_css_class' => 'listing-profile-title',
            'style' => 'max-height: 120px; overflow: auto; width: 200px;',
            'items' => $items
        );
        $dropDownBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown');
        $dropDownBlock->setData($data);
        // ---------------------------------------

        return parent::getHeaderHtml() . $dropDownBlock->toHtml();
    }

    public function getHeaderText()
    {
        // ---------------------------------------
        $changeProfile = Mage::helper('M2ePro')->__('Change Listing');
        $headerText = parent::getHeaderText();
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        $listingTitle = Mage::helper('M2ePro')->escapeHtml($listingData['title']);
        // ---------------------------------------

        return <<<HTML
{$headerText} <a href="javascript: void(0);"
   id="listing-profile-title"
   class="listing-profile-title"
   style="font-weight: bold;"
   title="{$changeProfile}"><span class="drop_down_header">"{$listingTitle}"</span></a>
HTML;
    }

    protected function getTemplatesButtonDropDownItems()
    {
        $items = array();

        $backUrl = Mage::helper('M2ePro')->makeBackUrlParam(
            '*/adminhtml_walmart_listing/view',
            array(
                'id' => $this->_listing->getId()
            )
        );

        // ---------------------------------------
        $url = $this->getUrl(
            '*/adminhtml_walmart_listing/edit',
            array(
                'id' => $this->_listing->getId(),
                'back' => $backUrl
            )
        );
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('Configuration'),
            'target' => '_blank'
        );
        // ---------------------------------------

        // ---------------------------------------
        $items[] = array(
            'url' => 'javascript: void(0);',
            'onclick' => 'ListingAutoActionObj.loadAutoActionHtml();',
            'label' => Mage::helper('M2ePro')->__('Auto Add/Remove Rules')
        );
        // ---------------------------------------

        return $items;
    }

    public function getAddProductsDropDownItems()
    {
        $items = array();

        $backUrl = Mage::helper('M2ePro')->makeBackUrlParam(
            '*/adminhtml_walmart_listing/view', array(
            'id' => $this->_listing->getId()
            )
        );

        // ---------------------------------------
        $url = $this->getUrl(
            '*/adminhtml_walmart_listing_productAdd/index',
            array(
                'id' => $this->_listing->getId(),
                'back' => $backUrl,
                'clear' => 1,
                'step' => 2,
                'source' => Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Product_Add_SourceMode::SOURCE_LIST
            )
        );
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('From Products List')
        );
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl(
            '*/adminhtml_walmart_listing_productAdd/index',
            array(
                'id' => $this->_listing->getId(),
                'back' => $backUrl,
                'clear' => 1,
                'step' => 2,
                'source' => Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Product_Add_SourceMode::SOURCE_CATEGORIES
            )
        );
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('From Categories')
        );
        // ---------------------------------------

        return $items;
    }

    //########################################
}
