<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_View extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    const VIEW_MODE_AMAZON        = 'amazon';
    const VIEW_MODE_MAGENTO       = 'magento';
    const VIEW_MODE_SELLERCENTRAL = 'sellercentral';
    const VIEW_MODE_SETTINGS      = 'settings';

    const DEFAULT_VIEW_MODE = self::VIEW_MODE_AMAZON;

    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing = null;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->_listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $this->setId('amazonListingView');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_listing_view_' . $this->getViewMode();


        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Amazon')->getTitle();
            $this->_headerText = Mage::helper('M2ePro')->__('View %component_name% Listing', $componentName);
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
            '*/adminhtml_amazon_log/listing', array(
                'listing_id' => $this->_listing->getId()
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
                'id' => 'add_products',
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
            self::VIEW_MODE_AMAZON,
            self::VIEW_MODE_SETTINGS,
            self::VIEW_MODE_MAGENTO,
            self::VIEW_MODE_SELLERCENTRAL
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
            'adminhtml_amazon_listing_autoAction', array(
                'listing_id' => $this->getRequest()->getParam('id'),
                'component' => Ess_M2ePro_Helper_Component_Amazon::NICK
            )
        );
        $showAutoAction   = Mage::helper('M2ePro')->jsonEncode((bool)$this->getRequest()->getParam('auto_actions'));

        $path = 'adminhtml_amazon_log/listingProduct';
        $urls[$path] = $this->getUrl(
            '*/' . $path, array(
            'channel' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'back' => $helper->makeBackUrlParam(
                '*/adminhtml_amazon_listing/view', array(
                    'id' => $this->_listing->getId()
                )
            )
            )
        );

        $path = 'adminhtml_amazon_listing/duplicateProducts';
        $urls[$path] = $this->getUrl('*/' . $path);

        $urls = array_merge(
            $urls,
            Mage::helper('M2ePro')->getControllerActions(
                'adminhtml_amazon_listing_transferring',
                array('listing_id' => $this->_listing->getId())
            )
        );

        $urls = array_merge(
            $urls, Mage::helper('M2ePro')->getControllerActions(
                'adminhtml_amazon_listing_repricing',
                array(
                'id' => $this->_listing->getId(),
                'account_id' => $this->_listing->getAccountId()
                )
            )
        );

        $urls['moveToListingPopupHtml'] = $this->getUrl('*/adminhtml_listing_moving/moveToListingPopupHtml');
        $urls['prepareMoveToListing'] = $this->getUrl('*/adminhtml_listing_moving/prepareMoveToListing');
        $urls['moveToListing'] = $this->getUrl('*/adminhtml_listing_moving/moveToListing');

        $urls = Mage::helper('M2ePro')->jsonEncode($urls);
        // ---------------------------------------

        $component = Ess_M2ePro_Helper_Component_Amazon::NICK;

        $temp = Mage::helper('M2ePro/Data_Session')->getValue('products_ids_for_list', true);
        $productsIdsForList = empty($temp) ? '' : $temp;

        $gridId = $this->getChild('grid')->getId();
        $ignoreListings = Mage::helper('M2ePro')->jsonEncode(array($this->_listing->getId()));

        $marketplace = Mage::helper('M2ePro')->jsonEncode($this->_listing->getMarketplace()->getData());
        $isNewAsinAvailable = Mage::helper('M2ePro')->jsonEncode(
            $this->_listing->getMarketplace()->getChildObject()->isNewAsinAvailable()
        );

        $logViewUrl = $this->getUrl(
            '*/adminhtml_amazon_log/listing', array(
                'listing_id' => $this->_listing->getId(),
                'back' => $helper->makeBackUrlParam(
                    '*/adminhtml_amazon_listing/view',
                    array('id' => $this->_listing->getId())
                )
            )
        );
        $getErrorsSummary = $this->getUrl('*/adminhtml_listing/getErrorsSummary');

        $runListProducts = $this->getUrl('*/adminhtml_amazon_listing/runListProducts');
        $runReviseProducts = $this->getUrl('*/adminhtml_amazon_listing/runReviseProducts');
        $runRelistProducts = $this->getUrl('*/adminhtml_amazon_listing/runRelistProducts');
        $runStopProducts = $this->getUrl('*/adminhtml_amazon_listing/runStopProducts');
        $runStopAndRemoveProducts = $this->getUrl('*/adminhtml_amazon_listing/runStopAndRemoveProducts');
        $runDeleteAndRemoveProducts = $this->getUrl('*/adminhtml_amazon_listing/runDeleteAndRemoveProducts');

        $marketplaceSynchUrl = $this->getUrl(
            '*/adminhtml_amazon_marketplace/index'
        );

        $getVariationEditPopupUrl = $this->getUrl('*/adminhtml_amazon_listing/getVariationEditPopup');
        $getVariationManagePopupUrl = $this->getUrl('*/adminhtml_amazon_listing/getVariationManagePopup');

        $variationEditActionUrl = $this->getUrl('*/adminhtml_amazon_listing/variationEdit');
        $variationManageActionUrl = $this->getUrl('*/adminhtml_amazon_listing/variationManage');
        $variationManageGenerateActionUrl = $this->getUrl('*/adminhtml_amazon_listing/variationManageGenerate');
        $variationResetActionUrl = $this->getUrl('*/adminhtml_amazon_listing/variationReset');

        $saveListingAdditionalDataActionUrl = $this->getUrl(
            '*/adminhtml_listing/saveListingAdditionalData', array(
            'id' => $this->_listing->getId()
            )
        );

        $popupTitle = $helper->escapeJs($helper->__('Moving Amazon Items'));

        $lockedObjNoticeMessage = $helper->escapeJs($helper->__('Some Amazon request(s) are being processed now.'));
        $sendingDataToAmazonMessage = $helper->escapeJs(
            $helper->__(
                'Sending %product_title% Product(s) data on Amazon.'
            )
        );
        $viewAllProductLogMessage = $helper->escapeJs($helper->__('View All Product Log.'));

        $listingLockedMessage = $helper->escapeJs(
            $helper->__('The Listing was locked by another process. Please try again later.')
        );
        $listingEmptyMessage = $helper->escapeJs($helper->__('Listing is empty.'));

        $listingAllItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')
            ->__('Listing All Items On Amazon')
        );
        $listingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')
            ->__('Listing Selected Items On Amazon')
        );
        $revisingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')
            ->__('Revising Selected Items On Amazon')
        );
        $relistingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')
            ->__('Relisting Selected Items On Amazon')
        );
        $stoppingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')
            ->__('Stopping Selected Items On Amazon')
        );
        $stoppingAndRemovingSelectedItemsMessage = Mage::helper('M2ePro')
                                                ->escapeJs(
                                                    Mage::helper('M2ePro')
                                                    ->__('Stopping On Amazon And Removing From Listing Selected Items')
                                                );
        $deletingAndRemovingSelectedItemsMessage = Mage::helper('M2ePro')
                                                    ->escapeJs(
                                                        Mage::helper('M2ePro')
                                                        ->__('Removing From Amazon And Listing Selected Items')
                                                    );

        $removingSelectedItemsMessage = $helper->escapeJs($helper->__('Removing From Listing Selected Items'));

        $searchAsinManual = $this->getUrl('*/adminhtml_amazon_listing/searchAsinManual');
        $getSearchAsinMenu = $this->getUrl('*/adminhtml_amazon_listing/getSearchAsinMenu');
        $suggestedAsinGridHmtl = $this->getUrl('*/adminhtml_amazon_listing/getSuggestedAsinGrid');
        $searchAsinAuto = $this->getUrl('*/adminhtml_amazon_listing/searchAsinAuto');
        $getProductsSearchStatus = $this->getUrl('*/adminhtml_amazon_listing/getProductsSearchStatus');
        $mapToAsin = $this->getUrl('*/adminhtml_amazon_listing/mapToAsin');
        $unmapFromAsin = $this->getUrl('*/adminhtml_amazon_listing/unmapFromAsin');
        $mapToNewAsin = $this->getUrl('*/adminhtml_amazon_listing/mapToNewAsin');

        $switchToAFN = $this->getUrl('*/adminhtml_amazon_listing/switchToAFN');
        $switchToMFN = $this->getUrl('*/adminhtml_amazon_listing/switchToMFN');

        $getAFNQtyBySku = $this->getUrl('*/adminhtml_amazon_listing/getAFNQtyBySku');
        $getUpdatedRepricingPriceBySkus = $this->getUrl(
            '*/adminhtml_amazon_listing_repricing/getUpdatedPriceBySkus'
        );

        $variationProductManage = $this->getUrl(
            '*/adminhtml_amazon_listing_variation_product_manage/index'
        );
        $variationProductSetGeneralIdOwner = $this->getUrl(
            '*/adminhtml_amazon_listing_variation_product_manage/setGeneralIdOwner'
        );
        $variationProductSetVariationTheme = $this->getUrl(
            '*/adminhtml_amazon_listing_variation_product_manage/setVariationTheme'
        );
        $variationProductSetMatchedAttributes = $this->getUrl(
            '*/adminhtml_amazon_listing_variation_product_manage/setMatchedAttributes'
        );
        $variationProductSetListingProductSku = $this->getUrl(
            '*/adminhtml_amazon_listing_variation_product_manage/setListingProductSku'
        );
        $manageVariationViewTemplateDescriptionsGrid = $this->getUrl(
            '*/adminhtml_amazon_listing_variation_product_manage/viewTemplateDescriptionsGrid'
        );
        $manageVariationMapToTemplateDescription = $this->getUrl(
            '*/adminhtml_amazon_listing_variation_product_manage/mapToTemplateDescription'
        );
        $addAttributesToVocabularyUrl = $this->getUrl(
            '*/adminhtml_amazon_listing_variation_product_manage/addAttributesToVocabulary'
        );
        $addOptionsToVocabularyUrl = $this->getUrl(
            '*/adminhtml_amazon_listing_variation_product_manage/addOptionsToVocabulary'
        );

        $viewVocabularyAjax = $this->getUrl(
            '*/adminhtml_amazon_listing_variation_product_manage/viewVocabularyAjax'
        );
        $saveAutoActionSettings = $this->getUrl(
            '*/adminhtml_amazon_listing_variation_product_manage/saveAutoActionSettings'
        );
        $removeAttributeFromVocabulary = $this->getUrl(
            '*/adminhtml_amazon_listing_variation_product_manage/removeAttributeFromVocabulary'
        );
        $removeOptionFromVocabulary = $this->getUrl(
            '*/adminhtml_amazon_listing_variation_product_manage/removeOptionFromVocabulary'
        );

        $viewVariationsSettingsAjax = $this->getUrl(
            '*/adminhtml_amazon_listing_variation_product_manage/viewVariationsSettingsAjax'
        );

        $newAsinUrl = $this->getUrl(
            '*/adminhtml_amazon_template_newProduct',
            array(
                'marketplace_id' => $this->_listing->getMarketplaceId(),
            )
        );

        $mapToTemplateDescription = $this->getUrl('*/adminhtml_amazon_listing/mapToTemplateDescription');
        $unmapFromTemplateDescription = $this->getUrl('*/adminhtml_amazon_listing/unmapFromTemplateDescription');
        $validateProductsForTemplateDescriptionAssign = $this->getUrl(
            '*/adminhtml_amazon_listing/validateProductsForTemplateDescriptionAssign'
        );
        $viewTemplateDescriptionsGrid = $this->getUrl('*/adminhtml_amazon_listing/viewTemplateDescriptionsGrid');
        $templateDescriptionPopupTitle = $helper->escapeJs($helper->__('Assign Description Policy'));

        $assignShippingTemplate    = $this->getUrl('*/adminhtml_amazon_listing/assignShippingTemplate');
        $unmapFromTemplateShipping = $this->getUrl('*/adminhtml_amazon_listing/unassignShippingTemplate');
        $viewTemplateShippingPopup = $this->getUrl('*/adminhtml_amazon_listing/viewTemplateShippingPopup');
        $viewTemplateShippingGrid  = $this->getUrl('*/adminhtml_amazon_listing/viewTemplateShippingGrid');

        $assignProductTaxCode    = $this->getUrl('*/adminhtml_amazon_template_productTaxCode/assign');
        $unassignProductTaxCode     = $this->getUrl('*/adminhtml_amazon_template_productTaxCode/unassign');
        $viewProductTaxCodePopup = $this->getUrl('*/adminhtml_amazon_template_productTaxCode/viewPopup');
        $viewProductTaxCodeGrid  = $this->getUrl('*/adminhtml_amazon_template_productTaxCode/viewGrid');

        $templateShippingPopupTitle = $helper->escapeJs($helper->__('Assign Shipping Policy'));
        $templateProductTaxCodePopupTitle = $helper->escapeJs($helper->__('Assign Product Tax Code Policy'));

        $enterProductSearchQueryMessage = $helper->escapeJs(
            $helper->__('Please enter Product Title or ASIN/ISBN/UPC/EAN.')
        );
        $autoMapAsinSearchProducts = $helper->escapeJs($helper->__('Search %product_title% Product(s) on Amazon.'));
        $autoMapAsinProgressTitle = $helper->escapeJs($helper->__('Automatic Assigning ASIN/ISBN to Item(s)'));
        $autoMapAsinErrorMessage = $helper->escapeJs(
            $helper->__('Server is currently unavailable. Please try again later.')
        );
        $newAsinNotAvailable = $helper->escapeJs(
            $helper->__('The new ASIN/ISBN creation functionality is not available in %code% Marketplace yet.')
        );
        $notSynchronizedMarketplace = $helper->escapeJs(
            $helper->__(
                'In order to use New ASIN/ISBN functionality, please re-synchronize Marketplace data.'
            ).' '.
            $helper->__(
                'Press "Save And Update" Button after redirect on Marketplace Page.'
            )
        );

        $noVariationsLeftText = $helper->__('All variations are already added.');

        $variationManageMatchedAttributesError = $helper->__('Please choose valid Attributes.');
        $variationManageMatchedAttributesErrorDuplicateSelection =
            $helper->__('You can not choose the same Attribute twice.');

        $variationManageSkuPopUpTitle =
            $helper->__('Enter Amazon Parent Product SKU');

        $switchToIndividualModePopUpTitle = $helper->__('Change "Manage Variations" Mode');
        $switchToParentModePopUpTitle = $helper->__('Change "Manage Variations" Mode');

        $emptySkuError = $helper->escapeJs($helper->__('Please enter Amazon Parent Product SKU.'));

        $translations = Mage::helper('M2ePro')->jsonEncode(array(
            'Auto Add/Remove Rules' => $helper->__('Auto Add/Remove Rules'),
            'Based on Magento Categories' => $helper->__('Based on Magento Categories'),
            'You must select at least 1 Category.' => $helper->__('You must select at least 1 Category.'),
            'Rule with the same Title already exists.' => $helper->__('Rule with the same Title already exists.'),
            'Sell on Another Marketplace' => $helper->__('Sell on Another Marketplace'),
            'Create new' => $helper->__('Create new')
        ));

        $constants = Mage::helper('M2ePro')
            ->getClassConstantAsJson('Ess_M2ePro_Model_Amazon_Account');

        $javascriptsMain = <<<HTML
<script type="text/javascript">

    M2ePro.php.setConstants({$constants}, 'Ess_M2ePro_Model_Amazon_Account');

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

    M2ePro.url.searchAsinManual = '{$searchAsinManual}';
    M2ePro.url.getSearchAsinMenu = '{$getSearchAsinMenu}';
    M2ePro.url.searchAsinAuto = '{$searchAsinAuto}';
    M2ePro.url.getProductsSearchStatus = '{$getProductsSearchStatus}';
    M2ePro.url.suggestedAsinGrid = '{$suggestedAsinGridHmtl}';
    M2ePro.url.mapToAsin = '{$mapToAsin}';
    M2ePro.url.unmapFromAsin = '{$unmapFromAsin}';
    M2ePro.url.mapToNewAsin = '{$mapToNewAsin}';

    M2ePro.url.switchToAFN = '{$switchToAFN}';
    M2ePro.url.switchToMFN = '{$switchToMFN}';

    M2ePro.url.getAFNQtyBySku = '{$getAFNQtyBySku}';
    M2ePro.url.getUpdatedRepricingPriceBySkus = '{$getUpdatedRepricingPriceBySkus}';

    M2ePro.url.variationProductManage = '{$variationProductManage}';
    M2ePro.url.variationProductSetGeneralIdOwner = '{$variationProductSetGeneralIdOwner}';
    M2ePro.url.variationProductSetVariationTheme = '{$variationProductSetVariationTheme}';
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

    M2ePro.url.newAsin = '{$newAsinUrl}';

    M2ePro.url.mapToTemplateDescription = '{$mapToTemplateDescription}';
    M2ePro.url.unmapFromTemplateDescription = '{$unmapFromTemplateDescription}';
    M2ePro.url.validateProductsForTemplateDescriptionAssign = '{$validateProductsForTemplateDescriptionAssign}';
    M2ePro.url.viewTemplateDescriptionsGrid = '{$viewTemplateDescriptionsGrid}';

    M2ePro.url.assignShippingTemplate = '{$assignShippingTemplate}';
    M2ePro.url.unassignShippingTemplate = '{$unmapFromTemplateShipping}';
    M2ePro.url.viewTemplateShippingPopup = '{$viewTemplateShippingPopup}';
    M2ePro.url.viewTemplateShippingGrid = '{$viewTemplateShippingGrid}';

    M2ePro.url.assignProductTaxCode = '{$assignProductTaxCode}';
    M2ePro.url.unassignProductTaxCode = '{$unassignProductTaxCode}';
    M2ePro.url.viewProductTaxCodePopup = '{$viewProductTaxCodePopup}';
    M2ePro.url.viewProductTaxCodeGrid = '{$viewProductTaxCodeGrid}';

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
    M2ePro.text.sending_data_message = '{$sendingDataToAmazonMessage}';
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

    M2ePro.text.templateDescriptionPopupTitle = '{$templateDescriptionPopupTitle}';

    M2ePro.text.templateShippingPopupTitle = '{$templateShippingPopupTitle}';
    M2ePro.text.templateProductTaxCodePopupTitle = '{$templateProductTaxCodePopupTitle}';

    M2ePro.text.enter_productSearch_query = '{$enterProductSearchQueryMessage}';
    M2ePro.text.automap_asin_search_products = '{$autoMapAsinSearchProducts}';
    M2ePro.text.automap_asin_progress_title = '{$autoMapAsinProgressTitle}';
    M2ePro.text.automap_error_message = '{$autoMapAsinErrorMessage}';

    M2ePro.text.new_asin_not_available = '{$newAsinNotAvailable}';
    M2ePro.text.not_synchronized_marketplace = '{$notSynchronizedMarketplace}';

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
    M2ePro.customData.isNewAsinAvailable = {$isNewAsinAvailable};

    Event.observe(window, 'load', function() {

        ListingGridObj = new AmazonListingGrid('{$gridId}', {$this->_listing->getId()});

        ListingProgressBarObj = new ProgressBar('listing_view_progress_bar');
        GridWrapperObj = new AreaWrapper('listing_view_content_container');

        ListingProductVariationObj = new AmazonListingProductVariation(M2ePro, ListingGridObj);

        if (M2ePro.productsIdsForList) {
            ListingGridObj.getGridMassActionObj().checkedString = M2ePro.productsIdsForList;
            ListingGridObj.actionHandler.listAction();
        }

        ListingAutoActionObj = new AmazonListingAutoAction();
        if ({$showAutoAction}) {
            ListingAutoActionObj.loadAutoActionHtml();
        }

        AmazonListingAfnQtyObj = new AmazonListingAfnQty();
        AmazonListingRepricingPriceObj = new AmazonListingRepricingPrice();
    });
</script>
HTML;

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_view_help');
        $productSearchBlock = $this->getLayout()
                                   ->createBlock('M2ePro/adminhtml_amazon_listing_productSearch_main');

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
            'M2ePro/adminhtml_amazon_listing_view_listingSwitcher'
        );
        // ---------------------------------------

        // ---------------------------------------
        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header', '',
            array(
                'listing' => Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
                    'Listing', $this->_listing->getId()
                )
            )
        );
        // ---------------------------------------

        // ---------------------------------------
        $switchToIndividualPopup = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_listing_variation_product_switchToIndividualPopup'
        );
        // ---------------------------------------

        // ---------------------------------------
        $switchToParentPopup = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_listing_variation_product_switchToParentPopup'
        );
        // ---------------------------------------

        return $javascriptsMain
            . $templatesDropDownBlock->toHtml()
            . $listingSwitcher->toHtml()
            . $addProductsDropDownBlock->toHtml()
            . $helpBlock->toHtml()
            . $viewHeaderBlock->toHtml()
            . $productSearchBlock->toHtml()
            . $switchToIndividualPopup->toHtml()
            . $switchToParentPopup->toHtml()
            . parent::getGridHtml();
    }

    public function getHeaderHtml()
    {
        // ---------------------------------------
        $collection = Mage::getModel('M2ePro/Listing')->getCollection();
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Amazon::NICK);
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
        $listingTitle = Mage::helper('M2ePro')->escapeHtml($this->_listing->getTitle());
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
            '*/adminhtml_amazon_listing/view',
            array(
                'id' => $this->_listing->getId()
            )
        );

        // ---------------------------------------
        $url = $this->getUrl(
            '*/adminhtml_amazon_listing/edit',
            array(
                'id'   => $this->_listing->getId(),
                'back' => $backUrl,
                'tab'  => 'selling'
            )
        );
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('Selling'),
            'target' => '_blank'
        );
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl(
            '*/adminhtml_amazon_listing/edit',
            array(
                'id'   => $this->_listing->getId(),
                'back' => $backUrl,
                'tab'  => 'search'
            )
        );
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('Search'),
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
            '*/adminhtml_amazon_listing/view', array(
            'id' => $this->_listing->getId()
            )
        );

        // ---------------------------------------
        $url = $this->getUrl(
            '*/adminhtml_amazon_listing_productAdd/index',
            array(
                'id' => $this->_listing->getId(),
                'back' => $backUrl,
                'clear' => 1,
                'step' => 2,
                'source' => Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Product_Add_SourceMode::SOURCE_LIST
            )
        );
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('From Products List')
        );
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl(
            '*/adminhtml_amazon_listing_productAdd/index',
            array(
                'id' => $this->_listing->getId(),
                'back' => $backUrl,
                'clear' => 1,
                'step' => 2,
                'source' => Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Product_Add_SourceMode::SOURCE_CATEGORIES
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
