<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Listing_Other_View extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('buyListing');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_buy_listing_other_view';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
            $this->_headerText = Mage::helper('M2ePro')->__(
                '3rd Party %channel_title% Listings',
                Mage::helper('M2ePro/Component_Buy')->getTitle()
            );
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('3rd Party Listings');
        }
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
        if (!is_null($this->getRequest()->getParam('back'))) {
            $url = Mage::helper('M2ePro')->getBackUrl();
            $this->_addButton('back', array(
                'label'   => Mage::helper('M2ePro')->__('Back'),
                'onclick' => 'CommonHandlerObj.back_click(\'' . $url . '\')',
                'class'   => 'back'
            ));
        }
        // ---------------------------------------

    }

    //########################################

    public function getGridHtml()
    {
        $accountId = $this->getRequest()->getParam('account');

        // ---------------------------------------
        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_other_view_header','',
            array(
                'account' => Mage::helper('M2ePro/Component_Buy')->getCachedObject('Account', $accountId),
                'marketplace' => Mage::helper('M2ePro/Component_Buy')->getMarketplace()
            )
        );
        // ---------------------------------------

        $mapToProductBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_listing_other_mapping');

        return $viewHeaderBlock->toHtml() . $mapToProductBlock->toHtml() . parent::getGridHtml();
    }

    //########################################

    protected function _toHtml()
    {
         /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');
        $componentMode = Ess_M2ePro_Helper_Component_Buy::NICK;

        $logViewUrl = $this->getUrl(
            '*/adminhtml_common_log/listingOther',
            array(
                'filter' => base64_encode('component_mode=' . Ess_M2ePro_Helper_Component_Buy::NICK),
                'back' => $helper->makeBackUrlParam('*/adminhtml_common_listing_other/index/tab/' . $componentMode)
            )
        );

        $prepareData = $this->getUrl('*/adminhtml_listing_other_moving/prepareMoveToListing');
        $createDefaultListingUrl = $this->getUrl('*/adminhtml_listing_other_moving/createDefaultListing');
        $getMoveToListingGridHtml = $this->getUrl('*/adminhtml_listing_other_moving/moveToListingGrid');
        $getFailedProductsGridHtml = $this->getUrl('*/adminhtml_listing_other_moving/getFailedProductsGrid');
        $tryToMoveToListing = $this->getUrl('*/adminhtml_listing_other_moving/tryToMoveToListing');
        $moveToListing = $this->getUrl('*/adminhtml_listing_other_moving/moveToListing');

        $mapAutoToProductUrl = $this->getUrl('*/adminhtml_listing_other_mapping/autoMap');

        $removingProductsUrl = $this->getUrl('*/adminhtml_common_listing_other/removing');
        $unmappingProductsUrl = $this->getUrl('*/adminhtml_listing_other_mapping/unmapping');

        $someProductsWereNotMappedMessage = 'No matches were found. Please change the Mapping Attributes in <strong>';
        $someProductsWereNotMappedMessage .= 'Configuration > Account > 3rd Party Listings</strong> ';
        $someProductsWereNotMappedMessage .= 'or try to map manually.';
        $someProductsWereNotMappedMessage = $helper->escapeJs($helper->__($someProductsWereNotMappedMessage));

        $successfullyMappedMessage = $helper->escapeJs($helper->__('Product was successfully Mapped.'));

        $createListing = $helper->escapeJs($helper->__(
            'Listings, which have the same Marketplace and Account were not found.'
        ));
        $createListing .= $helper->escapeJs($helper->__('Would you like to create one with Default Settings ?'));
        $popupTitle = $helper->escapeJs($helper->__('Moving Rakuten.com Items'));
        $popupTitleSingle = $helper->escapeJs($helper->__('Moving Rakuten.com Item'));
        $failedProductsPopupTitle = $helper->escapeJs($helper->__('Products failed to move'));

        $confirmMessage = $helper->escapeJs($helper->__('Are you sure?'));

        $successfullyMovedMessage = $helper->escapeJs($helper->__('Product(s) was successfully Moved.'));
        $productsWereNotMovedMessage = $helper->escapeJs(
            $helper->__('Products were not Moved. <a target="_blank" href="%url%">View Log</a> for details.')
        );
        $someProductsWereNotMovedMessage = $helper->escapeJs($helper->__(
            'Some of the Products were not Moved. <a target="_blank" href="%url%">View Log</a> for details.'
        ));

        $notEnoughDataMessage = $helper->escapeJs($helper->__('Not enough data.'));
        $successfullyUnmappedMessage = $helper->escapeJs($helper->__('Product(s) was successfully Unmapped.'));
        $successfullyRemovedMessage = $helper->escapeJs($helper->__('Product(s) was successfully Removed.'));

        $viewAllProductLogMessage = $helper->escapeJs($helper->__('View All Product Log.'));

        $selectItemsMessage = $helper->escapeJs(
            $helper->__('Please select the Products you want to perform the Action on.')
        );
        $selectActionMessage = $helper->escapeJs($helper->__('Please select Action.'));

        $processingDataMessage = $helper->escapeJs($helper->__('Processing %product_title% Product(s).'));

        $successWord = $helper->escapeJs($helper->__('Success'));
        $noticeWord = $helper->escapeJs($helper->__('Notice'));
        $warningWord = $helper->escapeJs($helper->__('Warning'));
        $errorWord = $helper->escapeJs($helper->__('Error'));
        $closeWord = $helper->escapeJs($helper->__('Close'));

        $autoMapProgressTitle = $helper->escapeJs($helper->__('Map Item(s) to Products'));
        $selectOnlyMapped = $helper->escapeJs($helper->__('Only Mapped Products must be selected.'));
        $selectTheSameTypeProducts = $helper->escapeJs(
            $helper->__('Selected Items must belong to the same Account and Marketplace.')
        );

        $translations = json_encode(array(

            'Mapping Product' => $helper->__('Mapping Product'),
            'Product does not exist.' => $helper->__('Product does not exist.'),
            'Please enter correct Product ID.' => $helper->__('Please enter correct Product ID.'),
            'Product(s) was successfully Mapped.' => $helper->__('Product(s) was successfully Mapped.'),
            'Please enter correct Product ID or SKU' => $helper->__('Please enter correct Product ID or SKU'),

            'Current version only supports Simple Products. Please, choose Simple Product.' => $helper->__(
                'Current version only supports Simple Products. Please, choose Simple Product.'
            ),

            'Item was not Mapped as the chosen %product_id% Simple Product has Custom Options.' => $helper->__(
                'Item was not Mapped as the chosen %product_id% Simple Product has Custom Options.'
            )

        ));

        $urls = json_encode(array(

            'adminhtml_common_log/listingOther' => $this->getUrl('*/adminhtml_common_log/listingOther',array(
                'back' => $helper->makeBackUrlParam('*/adminhtml_common_listing_other/index')
            )),

            'adminhtml_listing_other_mapping/map' => $this->getUrl('*/adminhtml_listing_other_mapping/map'),

        ));

        $javascriptsMain = <<<HTML
<script type="text/javascript">

    M2eProBuy = {};
    M2eProBuy.url = {};
    M2eProBuy.formData = {};
    M2eProBuy.customData = {};
    M2eProBuy.text = {};

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});

    M2eProBuy.url.logViewUrl = '{$logViewUrl}';
    M2eProBuy.url.prepareData = '{$prepareData}';
    M2eProBuy.url.createDefaultListing = '{$createDefaultListingUrl}';
    M2eProBuy.url.getGridHtml = '{$getMoveToListingGridHtml}';
    M2eProBuy.url.getFailedProductsGridHtml = '{$getFailedProductsGridHtml}';
    M2eProBuy.url.tryToMoveToListing = '{$tryToMoveToListing}';
    M2eProBuy.url.moveToListing = '{$moveToListing}';
    M2eProBuy.url.mapAutoToProduct = '{$mapAutoToProductUrl}';
    M2eProBuy.url.removingProducts = '{$removingProductsUrl}';
    M2eProBuy.url.unmappingProducts = '{$unmappingProductsUrl}';

    M2eProBuy.text.create_listing = '{$createListing}';
    M2eProBuy.text.popup_title = '{$popupTitle}';
    M2eProBuy.text.popup_title_single = '{$popupTitleSingle}';
    M2eProBuy.text.failed_products_popup_title = '{$failedProductsPopupTitle}';
    M2eProBuy.text.confirm = '{$confirmMessage}';
    M2eProBuy.text.successfully_moved = '{$successfullyMovedMessage}';
    M2eProBuy.text.products_were_not_moved = '{$productsWereNotMovedMessage}';
    M2eProBuy.text.some_products_were_not_moved = '{$someProductsWereNotMovedMessage}';
    M2eProBuy.text.not_enough_data = '{$notEnoughDataMessage}';
    M2eProBuy.text.successfully_unmapped = '{$successfullyUnmappedMessage}';
    M2eProBuy.text.successfully_removed = '{$successfullyRemovedMessage}';

    M2eProBuy.text.select_items_message = '{$selectItemsMessage}';
    M2eProBuy.text.select_action_message = '{$selectActionMessage}';

    M2eProBuy.text.automap_progress_title = '{$autoMapProgressTitle}';
    M2eProBuy.text.processing_data_message = '{$processingDataMessage}';
    M2eProBuy.text.successfully_mapped = '{$successfullyMappedMessage}';
    M2eProBuy.text.failed_mapped = '{$someProductsWereNotMappedMessage}';

    M2eProBuy.text.select_only_mapped_products = '{$selectOnlyMapped}';
    M2eProBuy.text.select_the_same_type_products = '{$selectTheSameTypeProducts}';

    M2eProBuy.text.view_all_product_log_message = '{$viewAllProductLogMessage}';

    M2eProBuy.text.success_word = '{$successWord}';
    M2eProBuy.text.notice_word = '{$noticeWord}';
    M2eProBuy.text.warning_word = '{$warningWord}';
    M2eProBuy.text.error_word = '{$errorWord}';
    M2eProBuy.text.close_word = '{$closeWord}';

    M2eProBuy.customData.componentMode = '{$componentMode}';
    M2eProBuy.customData.gridId = 'buyListingOtherGrid';

    Event.observe(window,'load',function() {
        ListingProgressBarObj = new ProgressBar('listing_other_progress_bar');
        GridWrapperObj = new AreaWrapper('listing_other_content_container');

        BuyListingOtherGridHandlerObj    = new CommonBuyListingOtherGridHandler('buyListingOtherGrid');
        BuyListingOtherMappingHandlerObj = new ListingOtherMappingHandler(
            BuyListingOtherGridHandlerObj,
            'buy'
        );

        BuyListingOtherGridHandlerObj.movingHandler.setOptions(M2eProBuy);
        BuyListingOtherGridHandlerObj.autoMappingHandler.setOptions(M2eProBuy);
        BuyListingOtherGridHandlerObj.removingHandler.setOptions(M2eProBuy);
        BuyListingOtherGridHandlerObj.unmappingHandler.setOptions(M2eProBuy);
    });

</script>
HTML;

        return $javascriptsMain.
            '<div id="listing_other_progress_bar"></div>' .
            '<div id="listing_container_errors_summary" class="errors_summary" style="display: none;"></div>' .
            '<div id="listing_other_content_container">' .
            parent::_toHtml() .
            '</div>';
    }

    //########################################
}