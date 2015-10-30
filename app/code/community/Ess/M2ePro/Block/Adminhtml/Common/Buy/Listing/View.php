<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Listing_View extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('buyListingView');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_buy_listing_view';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
            $headerText = Mage::helper('M2ePro')->__(
                'View %component_name% Listing',
                Mage::helper('M2ePro/Component_Buy')->getTitle()
            );
        } else {
            $headerText = Mage::helper('M2ePro')->__('View Listing ');
        }

        $this->_headerText = $headerText;
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl('*/adminhtml_common_log/listing', array(
            'id' => $listingData['id'],
            'channel' => Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::CHANNEL_ID_BUY
        ));
        $this->_addButton('view_log', array(
            'label'   => Mage::helper('M2ePro')->__('View Log'),
            'onclick' => 'window.open(\'' . $url . '\')',
            'class'   => 'button_link'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton('edit_settings', array(
            'label'   => Mage::helper('M2ePro')->__('Edit Settings'),
            'onclick' => '',
            'class'   => 'drop_down edit_settings_drop_down'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton('add_products', array(
            'label'     => Mage::helper('M2ePro')->__('Add Products'),
            'onclick'   => '',
            'class'     => 'add drop_down add_products_drop_down'
        ));
        // ---------------------------------------
    }

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
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');

        // ---------------------------------------
        $urls = $helper->getControllerActions(
            'adminhtml_common_listing_autoAction', array(
                'listing_id' => $this->getRequest()->getParam('id'),
                'component' => Ess_M2ePro_Helper_Component_Buy::NICK
            )
        );
        $showAutoAction   = json_encode((bool)$this->getRequest()->getParam('auto_actions'));

        $path = 'adminhtml_common_log/listingProduct';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            'channel' => Ess_M2ePro_Helper_Component_Buy::NICK,
            'back'=>$helper->makeBackUrlParam('*/adminhtml_common_buy_listing/view', array('id' =>$listingData['id']))
        ));

        $path = 'adminhtml_common_listing/duplicateProducts';
        $urls[$path] = $this->getUrl('*/' . $path);

        $urls = json_encode($urls);
        // ---------------------------------------

        $component = Ess_M2ePro_Helper_Component_Buy::NICK;
        $layout = $this->getLayout();

        $temp = Mage::helper('M2ePro/Data_Session')->getValue('products_ids_for_list', true);
        $productsIdsForList = empty($temp) ? '' : $temp;

        $gridId = 'buyListingViewGrid' . $listingData['id'];
        $ignoreListings = json_encode(array($listingData['id']));

        $logViewUrl = $this->getUrl('*/adminhtml_common_log/listing', array(
            'id' => $listingData['id'],
            'channel' => Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::CHANNEL_ID_BUY,
            'back'=>$helper->makeBackUrlParam('*/adminhtml_common_buy_listing/view', array('id' =>$listingData['id']))
        ));
        $getErrorsSummary = $this->getUrl('*/adminhtml_listing/getErrorsSummary');

        $runListProducts = $this->getUrl('*/adminhtml_common_buy_listing/runListProducts');
        $runReviseProducts = $this->getUrl('*/adminhtml_common_buy_listing/runReviseProducts');
        $runRelistProducts = $this->getUrl('*/adminhtml_common_buy_listing/runRelistProducts');
        $runStopProducts = $this->getUrl('*/adminhtml_common_buy_listing/runStopProducts');
        $runStopAndRemoveProducts = $this->getUrl('*/adminhtml_common_buy_listing/runStopAndRemoveProducts');

        $prepareData = $this->getUrl('*/adminhtml_listing_moving/prepareMoveToListing');
        $getMoveToListingGridHtml = $this->getUrl('*/adminhtml_listing_moving/moveToListingGrid');
        $getFailedProductsGridHtml = $this->getUrl('*/adminhtml_listing_moving/getFailedProductsGrid');
        $tryToMoveToListing = $this->getUrl('*/adminhtml_listing_moving/tryToMoveToListing');
        $moveToListing = $this->getUrl('*/adminhtml_listing_moving/moveToListing');

        $getVariationEditPopupUrl = $this->getUrl('*/adminhtml_common_listing/getVariationEditPopup');
        $getVariationManagePopupUrl = $this->getUrl('*/adminhtml_common_listing/getVariationManagePopup');

        $variationEditActionUrl = $this->getUrl('*/adminhtml_common_listing/variationEdit');
        $variationManageActionUrl = $this->getUrl('*/adminhtml_common_listing/variationManage');
        $variationManageGenerateActionUrl = $this->getUrl('*/adminhtml_common_listing/variationManageGenerate');

        $popupTitle = $helper->escapeJs($helper->__('Moving Rakuten.com Items'));
        $popupTitleSingle = $helper->escapeJs($helper->__('Moving Rakuten.com Item'));
        $failedProductsPopupTitle = $helper->escapeJs($helper->__('Products failed to move'));

        $taskCompletedMessage = $helper->escapeJs($helper->__('Task completed. Please wait ...'));
        $taskCompletedSuccessMessage = $helper->escapeJs(
            $helper->__('"%task_title%" Task has successfully submitted to be processed.')
        );
        $taskCompletedWarningMessage = $helper->escapeJs($helper->__(
            '"%task_title%" Task has completed with warnings. <a target="_blank" href="%url%">View Log</a> for details.'
        ));
        $taskCompletedErrorMessage = $helper->escapeJs($helper->__(
            '"%task_title%" Task has completed with errors. <a target="_blank" href="%url%">View Log</a> for details.'
        ));

        $lockedObjNoticeMessage = $helper->escapeJs(
            $helper->__('Some Rakuten.com request(s) are being processed now.')
        );
        $sendingDataToBuyMessage = $helper->escapeJs($helper->__(
            'Sending %product_title% Product(s) data on Rakuten.com.'
        ));
        $viewAllProductLogMessage = $helper->escapeJs($helper->__('View All Product Log.'));

        $listingLockedMessage = $helper->escapeJs(
            $helper->__('The Listing was locked by another process. Please try again later.')
        );
        $listingEmptyMessage = $helper->escapeJs($helper->__('Listing is empty.'));

        $listingAllItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('Listing All Items On Rakuten.com'));
        $listingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('Listing Selected Items On Rakuten.com'));
        $revisingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('Revising Selected Items On Rakuten.com'));
        $relistingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('Relisting Selected Items On Rakuten.com'));
        $stoppingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')
            ->__('Stopping Selected Items On Rakuten.com'));
        $stoppingAndRemovingSelectedItemsMessage = Mage::helper('M2ePro')
            ->escapeJs(Mage::helper('M2ePro')
            ->__('Stopping On Rakuten.com And Removing From Listing Selected Items'));
        $addNewSkuSelectedItemsMessage = Mage::helper('M2ePro')
            ->escapeJs(Mage::helper('M2ePro')
            ->__('Adding New SKU On Rakuten.com'));

        $successfullyMovedMessage = $helper->escapeJs($helper->__('Product(s) was successfully Moved.'));
        $productsWereNotMovedMessage = $helper->escapeJs(
            $helper->__('Product(s) was not Moved. <a target="_blank" href="%url%">View Log</a> for details.')
        );
        $someProductsWereNotMovedMessage = $helper->escapeJs(
            $helper->__('Some Product(s) was not Moved. <a target="_blank" href="%url%">View Log</a> for details.')
        );

        $selectItemsMessage = $helper->escapeJs(
            $helper->__('Please select the Products you want to perform the Action on.')
        );
        $selectActionMessage = $helper->escapeJs($helper->__('Please select Action.'));

        $successWord = $helper->escapeJs($helper->__('Success'));
        $noticeWord = $helper->escapeJs($helper->__('Notice'));
        $warningWord = $helper->escapeJs($helper->__('Warning'));
        $errorWord = $helper->escapeJs($helper->__('Error'));
        $closeWord = $helper->escapeJs($helper->__('Close'));

        $naString = Mage::helper('M2ePro')->__('N/A');
        $assignString = Mage::helper('M2ePro')->__('Assign Rakuten.com SKU');

        $textConfirm = $helper->escapeJs($helper->__('Are you sure?'));

        $searchBuyComSkuManual = $this->getUrl('*/adminhtml_common_buy_listing/searchBuyComSkuManual');
        $suggestedBuyComSkuGridHmtl = $this->getUrl('*/adminhtml_common_buy_listing/getSuggestedBuyComSkuGrid');
        $searchBuyComSkuAuto = $this->getUrl('*/adminhtml_common_buy_listing/searchBuyComSkuAuto');
        $mapToBuyComSku = $this->getUrl('*/adminhtml_common_buy_listing/mapToBuyComSku');
        $unmapFromBuyComSku = $this->getUrl('*/adminhtml_common_buy_listing/unmapFromBuyComSku');

        $newGeneralId = $this->getUrl('*/adminhtml_common_buy_template_newProduct');

        $marketplaceId = Mage::helper('M2ePro/Data_Global')->getValue('marketplace_id');
        $marketplaceInstance = Mage::helper('M2ePro/Component_Buy')->getCachedObject('Marketplace',$marketplaceId);
        $marketplace = json_encode($marketplaceInstance->getData());

        $marketplaceSynchUrl = $this->getUrl(
            '*/adminhtml_common_marketplace/index',
            array('tab' => Ess_M2ePro_Block_Adminhtml_Common_Marketplace::TAB_ID_RAKUTEN)
        );
        $notSynchronizedMarketplace = $helper->escapeJs(
            $helper->__(
                'In order to use New SKU functionality, please re-synchronize Marketplace data.'
            ).' '.
                $helper->__(
                    'Press "Save And Update" Button after redirect on Marketplace Page.'
                )
        );

        $enterProductSearchQueryMessage = $helper->escapeJs(
            $helper->__('Please enter Product Title or Rakuten.com SKU/ISBN/UPC.')
        );
        $autoMapBuyComSkuProgressTitle = $helper->escapeJs($helper->__('Assign Rakuten.com SKU to Item(s)'));
        $autoMapBuyComSkuErrorMessage = $helper->escapeJs(
            $helper->__('Server is currently unavailable. Please try again later.')
        );

        $noVariationsLeftText = $helper->__('All Variations are already added.');

        $translations = json_encode(array(
            'Auto Add/Remove Rules' => $helper->__('Auto Add/Remove Rules'),
            'Based on Magento Categories' => $helper->__('Based on Magento Categories'),
            'You must select at least 1 Category.' => $helper->__('You must select at least 1 Category.'),
            'Rule with the same Title already exists.' => $helper->__('Rule with the same Title already exists.')
        ));

        $javascriptsMain = <<<HTML
<script type="text/javascript">

    if (typeof M2ePro == 'undefined') {
        M2ePro = {};
        M2ePro.url = {};
        M2ePro.formData = {};
        M2ePro.customData = {};
        M2ePro.text = {};
    }

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

    M2ePro.url.searchBuyComSkuManual = '{$searchBuyComSkuManual}';
    M2ePro.url.searchBuyComSkuAuto = '{$searchBuyComSkuAuto}';
    M2ePro.url.suggestedBuyComSkuGrid = '{$suggestedBuyComSkuGridHmtl}';
    M2ePro.url.mapToBuyComSku = '{$mapToBuyComSku}';
    M2ePro.url.unmapFromBuyComSku = '{$unmapFromBuyComSku}';

    M2ePro.url.prepareData = '{$prepareData}';
    M2ePro.url.getGridHtml = '{$getMoveToListingGridHtml}';
    M2ePro.url.getFailedProductsGridHtml = '{$getFailedProductsGridHtml}';
    M2ePro.url.tryToMoveToListing = '{$tryToMoveToListing}';
    M2ePro.url.moveToListing = '{$moveToListing}';

    M2ePro.url.get_variation_edit_popup = '{$getVariationEditPopupUrl}';
    M2ePro.url.get_variation_manage_popup = '{$getVariationManagePopupUrl}';

    M2ePro.url.variation_edit_action = '{$variationEditActionUrl}';
    M2ePro.url.variation_manage_action = '{$variationManageActionUrl}';
    M2ePro.url.variation_manage_generate_action = '{$variationManageGenerateActionUrl}';

    M2ePro.url.newGeneralId = '{$newGeneralId}';
    M2ePro.url.marketplace_synch = '{$marketplaceSynchUrl}';

    M2ePro.text.popup_title = '{$popupTitle}';
    M2ePro.text.popup_title_single = '{$popupTitleSingle}';
    M2ePro.text.failed_products_popup_title = '{$failedProductsPopupTitle}';

    M2ePro.text.task_completed_message = '{$taskCompletedMessage}';
    M2ePro.text.task_completed_success_message = '{$taskCompletedSuccessMessage}';
    M2ePro.text.task_completed_warning_message = '{$taskCompletedWarningMessage}';
    M2ePro.text.task_completed_error_message = '{$taskCompletedErrorMessage}';

    M2ePro.text.locked_obj_notice = '{$lockedObjNoticeMessage}';
    M2ePro.text.sending_data_message = '{$sendingDataToBuyMessage}';
    M2ePro.text.view_all_product_log_message = '{$viewAllProductLogMessage}';

    M2ePro.text.listing_locked_message = '{$listingLockedMessage}';
    M2ePro.text.listing_empty_message = '{$listingEmptyMessage}';

    M2ePro.text.listing_all_items_message = '{$listingAllItemsMessage}';
    M2ePro.text.listing_selected_items_message = '{$listingSelectedItemsMessage}';
    M2ePro.text.revising_selected_items_message = '{$revisingSelectedItemsMessage}';
    M2ePro.text.relisting_selected_items_message = '{$relistingSelectedItemsMessage}';
    M2ePro.text.stopping_selected_items_message = '{$stoppingSelectedItemsMessage}';
    M2ePro.text.stopping_and_removing_selected_items_message = '{$stoppingAndRemovingSelectedItemsMessage}';
    M2ePro.text.add_new_sku_selected_items_message = '{$addNewSkuSelectedItemsMessage}';

    M2ePro.text.successfully_moved = '{$successfullyMovedMessage}';
    M2ePro.text.products_were_not_moved = '{$productsWereNotMovedMessage}';
    M2ePro.text.some_products_were_not_moved = '{$someProductsWereNotMovedMessage}';

    M2ePro.text.select_items_message = '{$selectItemsMessage}';
    M2ePro.text.select_action_message = '{$selectActionMessage}';

    M2ePro.text.success_word = '{$successWord}';
    M2ePro.text.notice_word = '{$noticeWord}';
    M2ePro.text.warning_word = '{$warningWord}';
    M2ePro.text.error_word = '{$errorWord}';
    M2ePro.text.close_word = '{$closeWord}';

    M2ePro.text.na = '{$naString}';
    M2ePro.text.assign = '{$assignString}';

    M2ePro.text.confirm = '{$textConfirm}';

    M2ePro.text.enter_productSearch_query = '{$enterProductSearchQueryMessage}';
    M2ePro.text.automap_buy_com_sku_progress_title = '{$autoMapBuyComSkuProgressTitle}';
    M2ePro.text.automap_error_message = '{$autoMapBuyComSkuErrorMessage}';

    M2ePro.text.no_variations_left = '{$noVariationsLeftText}';

    M2ePro.text.not_synchronized_marketplace = '{$notSynchronizedMarketplace}';

    M2ePro.customData.componentMode = '{$component}';
    M2ePro.customData.gridId = '{$gridId}';
    M2ePro.customData.ignoreListings = '{$ignoreListings}';

    M2ePro.customData.marketplace = {$marketplace};

    Event.observe(window, 'load', function() {

        ListingGridHandlerObj = new CommonBuyListingGridHandler(
            'buyListingViewGrid{$listingData['id']}',
            {$listingData['id']}
        );

        ListingGridHandlerObj.actionHandler.setOptions(M2ePro);
        ListingGridHandlerObj.movingHandler.setOptions(M2ePro);
        ListingGridHandlerObj.productSearchHandler.setOptions(M2ePro);

        ListingProgressBarObj = new ProgressBar('listing_view_progress_bar');
        GridWrapperObj = new AreaWrapper('listing_view_content_container');

        ListingProductVariationHandlerObj = new CommonListingProductVariationHandler(M2ePro,
                                                                               ListingGridHandlerObj);

        if (M2ePro.productsIdsForList) {
            ListingGridHandlerObj.getGridMassActionObj().checkedString = M2ePro.productsIdsForList;
            ListingGridHandlerObj.actionHandler.listAction();
        }

        ListingAutoActionHandlerObj = new ListingAutoActionHandler();
        if ({$showAutoAction}) {
            ListingAutoActionHandlerObj.loadAutoActionHtml();
        }

    });

</script>
HTML;

        $helpBlock = $layout->createBlock('M2ePro/adminhtml_common_buy_listing_view_help');
        $productSearchMenuBlock = $layout->createBlock('M2ePro/adminhtml_common_buy_listing_productSearch_menu');
        $productSearchBlock = $layout->createBlock('M2ePro/adminhtml_common_buy_listing_productSearch_main');

        // ---------------------------------------
        $data = array(
            'target_css_class' => 'edit_settings_drop_down',
            'items'            => $this->getTemplatesButtonDropDownItems()
        );
        $templatesDropDownBlock = $layout->createBlock('M2ePro/adminhtml_widget_button_dropDown');
        $templatesDropDownBlock->setData($data);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'target_css_class' => 'add_products_drop_down',
            'items'            => $this->getAddProductsDropDownItems()
        );
        $addProductsDropDownBlock = $layout->createBlock('M2ePro/adminhtml_widget_button_dropDown');
        $addProductsDropDownBlock->setData($data);
        // ---------------------------------------

        // ---------------------------------------
        $listingSwitcher = $this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_listing_view_listingSwitcher');
        // ---------------------------------------

        // ---------------------------------------
        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header','',
            array('listing' => Mage::helper('M2ePro/Component_Buy')->getCachedObject('Listing',$listingData['id']))
        );
        // ---------------------------------------

        return $javascriptsMain
            . $templatesDropDownBlock->toHtml()
            . $listingSwitcher->toHtml()
            . $addProductsDropDownBlock->toHtml()
            . $helpBlock->toHtml()
            . $viewHeaderBlock->toHtml()
            . $productSearchMenuBlock->toHtml()
            . $productSearchBlock->toHtml()
            . parent::getGridHtml();
    }

    public function getHeaderHtml()
    {
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // ---------------------------------------
        $collection = Mage::getModel('M2ePro/Listing')->getCollection();
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Buy::NICK);
        $collection->addFieldToFilter('id', array('neq' => $listingData['id']));
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

        if (count($items) == 0) {
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

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        $backUrl = Mage::helper('M2ePro')->makeBackUrlParam(
            '*/adminhtml_common_buy_listing/view',
            array(
                'id' => $listingData['id']
            )
        );

        // ---------------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_buy_listing/edit',
            array(
                'id'    => $listingData['id'],
                'back'  => $backUrl,
                'tab'   => 'selling'
            )
        );
        $items[] = array(
            'url'       => $url,
            'label'     => Mage::helper('M2ePro')->__('Selling Settings'),
            'target'    => '_blank'
        );
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_buy_listing/edit',
            array(
                'id'    => $listingData['id'],
                'back'  => $backUrl,
                'tab'   => 'search'
            )
        );
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('Search Settings'),
            'target' => '_blank'
        );
        // ---------------------------------------

        // ---------------------------------------
        $items[] = array(
            'url' => 'javascript: void(0);',
            'onclick' => 'ListingAutoActionHandlerObj.loadAutoActionHtml();',
            'label' => Mage::helper('M2ePro')->__('Auto Add/Remove Rules')
        );
        // ---------------------------------------

        return $items;
    }

    public function getAddProductsDropDownItems()
    {
        $items = array();

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        $backUrl = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_common_buy_listing/view', array(
            'id' => $listingData['id']
        ));

        // ---------------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_listing_productAdd/index',
            array(
                'id' => $listingData['id'],
                'back' => $backUrl,
                'component' => Ess_M2ePro_Helper_Component_Buy::NICK,
                'clear' => 1,
                'step' => 2,
                'source' => Ess_M2ePro_Block_Adminhtml_Common_Listing_Add_SourceMode::SOURCE_LIST
            )
        );
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('From Products List')
        );
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_listing_productAdd/index',
            array(
                'id' => $listingData['id'],
                'back' => $backUrl,
                'component' => Ess_M2ePro_Helper_Component_Buy::NICK,
                'clear' => 1,
                'step' => 2,
                'source' => Ess_M2ePro_Block_Adminhtml_Common_Listing_Add_SourceMode::SOURCE_CATEGORIES
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