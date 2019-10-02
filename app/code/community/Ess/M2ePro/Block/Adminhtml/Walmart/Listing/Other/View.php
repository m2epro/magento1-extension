<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Other_View extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartListing');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_walmart_listing_other_view';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Walmart')->getTitle();
            $this->_headerText = Mage::helper('M2ePro')->__('%component_name% / 3rd Party Listings', $componentName);
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
        if ($this->getRequest()->getParam('back') !== null) {
            $url = Mage::helper('M2ePro')->getBackUrl();
            $this->_addButton(
                'back', array(
                'label'   => Mage::helper('M2ePro')->__('Back'),
                'onclick' => 'CommonHandlerObj.back_click(\'' . $url . '\')',
                'class'   => 'back'
                )
            );
        }

        $url = $this->getUrl('*/adminhtml_walmart_log/listingOther');
        $this->_addButton(
            'view_log', array(
            'label'     => Mage::helper('M2ePro')->__('View Log'),
            'onclick'   => 'window.open(\''.$url.'\')',
            'class'     => 'button_link'
            )
        );
        // ---------------------------------------

    }

    //########################################

    public function getGridHtml()
    {
        $accountId = $this->getRequest()->getParam('account');
        $marketplaceId = $this->getRequest()->getParam('marketplace');

        // ---------------------------------------
        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_other_view_header', '',
            array(
                'account' => Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
                    'Account', $accountId
                ),
                'marketplace' => Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
                    'Marketplace', $marketplaceId
                )
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
        $componentMode = Ess_M2ePro_Helper_Component_Walmart::NICK;

        $prepareData = $this->getUrl('*/adminhtml_listing_other_moving/prepareMoveToListing');
        $createDefaultListingUrl = $this->getUrl('*/adminhtml_listing_other_moving/createDefaultListing');
        $getMoveToListingGridHtml = $this->getUrl('*/adminhtml_listing_other_moving/moveToListingGrid');
        $moveToListing = $this->getUrl('*/adminhtml_walmart_listing_other/moveToListing');

        $mapAutoToProductUrl = $this->getUrl('*/adminhtml_listing_other_mapping/autoMap');

        $removingProductsUrl = $this->getUrl('*/adminhtml_walmart_listing_other/removing');
        $unmappingProductsUrl = $this->getUrl('*/adminhtml_listing_other_mapping/unmapping');

        $someProductsWereNotMappedMessage = 'No matches were found. Please change the Mapping Attributes in <strong>';
        $someProductsWereNotMappedMessage .= 'Configuration > Account > 3rd Party Listings</strong> ';
        $someProductsWereNotMappedMessage .= 'or try to map manually.';
        $someProductsWereNotMappedMessage = $helper->escapeJs($helper->__($someProductsWereNotMappedMessage));

        $successfullyMappedMessage = $helper->escapeJs($helper->__('Product was successfully Mapped.'));

        $createListing = $helper->escapeJs(
            $helper->__(
                'Listings, which have the same Marketplace and Account were not found.'
            )
        );
        $createListing .= $helper->escapeJs($helper->__('Would you like to create one with Default Settings ?'));
        $popupTitle = $helper->escapeJs($helper->__('Moving Walmart Items'));

        $confirmMessage = $helper->escapeJs($helper->__('Are you sure?'));

        $notEnoughDataMessage = $helper->escapeJs($helper->__('Not enough data'));
        $successfullyUnmappedMessage = $helper->escapeJs($helper->__('Product(s) was successfully Unmapped.'));
        $successfullyRemovedMessage = $helper->escapeJs($helper->__('Product(s) was successfully Removed.'));

        $viewAllProductLogMessage = $helper->escapeJs($helper->__('View All Product Log.'));

        $selectItemsMessage = $helper->escapeJs(
            $helper->__('Please select the Products you want to perform the Action on.')
        );
        $selectActionMessage = $helper->escapeJs($helper->__('Please select Action.'));

        $processingDataMessage = $helper->escapeJs($helper->__('Processing %product_title% Product(s).'));

        $autoMapProgressTitle = $helper->escapeJs($helper->__('Map Item(s) to Products'));

        $successWord = $helper->escapeJs($helper->__('Success'));
        $noticeWord = $helper->escapeJs($helper->__('Notice'));
        $warningWord = $helper->escapeJs($helper->__('Warning'));
        $errorWord = $helper->escapeJs($helper->__('Error'));
        $closeWord = $helper->escapeJs($helper->__('Close'));

        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(

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

            )
        );

        $urls = Mage::helper('M2ePro')->jsonEncode(
            array(
            'adminhtml_walmart_log/listingOther' => $this->getUrl(
                '*/adminhtml_walmart_log/listingOther', array(
                'back' => $helper->makeBackUrlParam('*/adminhtml_walmart_listing_other/index')
                )
            ),
            'adminhtml_listing_other_mapping/map' => $this->getUrl('*/adminhtml_listing_other_mapping/map'),
            'adminhtml_walmart_listing_productAdd/index' => $this->getUrl(
                '*/adminhtml_walmart_listing_productAdd/index', array('step' => 3)
            )
            )
        );

        $javascriptsMain = <<<HTML
<script type="text/javascript">

    M2eProWalmart = {};
    M2eProWalmart.url = {};
    M2eProWalmart.formData = {};
    M2eProWalmart.customData = {};
    M2eProWalmart.text = {};

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});

    M2eProWalmart.url.prepareData = '{$prepareData}';
    M2eProWalmart.url.createDefaultListing = '{$createDefaultListingUrl}';
    M2eProWalmart.url.getGridHtml = '{$getMoveToListingGridHtml}';
    M2eProWalmart.url.moveToListing = '{$moveToListing}';

    M2eProWalmart.url.mapAutoToProduct = '{$mapAutoToProductUrl}';

    M2eProWalmart.url.removingProducts = '{$removingProductsUrl}';
    M2eProWalmart.url.unmappingProducts = '{$unmappingProductsUrl}';

    M2eProWalmart.text.create_listing = '{$createListing}';
    M2eProWalmart.text.popup_title = '{$popupTitle}';
    M2eProWalmart.text.confirm = '{$confirmMessage}';
    M2eProWalmart.text.not_enough_data = '{$notEnoughDataMessage}';
    M2eProWalmart.text.successfully_unmapped = '{$successfullyUnmappedMessage}';
    M2eProWalmart.text.successfully_removed = '{$successfullyRemovedMessage}';

    M2eProWalmart.text.select_items_message = '{$selectItemsMessage}';
    M2eProWalmart.text.select_action_message = '{$selectActionMessage}';

    M2eProWalmart.text.automap_progress_title = '{$autoMapProgressTitle}';
    M2eProWalmart.text.processing_data_message = '{$processingDataMessage}';
    M2eProWalmart.text.successfully_mapped = '{$successfullyMappedMessage}';
    M2eProWalmart.text.failed_mapped = '{$someProductsWereNotMappedMessage}';

    M2eProWalmart.text.view_all_product_log_message = '{$viewAllProductLogMessage}';

    M2eProWalmart.text.success_word = '{$successWord}';
    M2eProWalmart.text.notice_word = '{$noticeWord}';
    M2eProWalmart.text.warning_word = '{$warningWord}';
    M2eProWalmart.text.error_word = '{$errorWord}';
    M2eProWalmart.text.close_word = '{$closeWord}';

    M2eProWalmart.customData.componentMode = '{$componentMode}';
    M2eProWalmart.customData.gridId = 'walmartListingOtherGrid';

    Event.observe(window,'load',function() {
        ListingProgressBarObj = new ProgressBar('listing_other_progress_bar');
        GridWrapperObj = new AreaWrapper('listing_other_content_container');

        WalmartListingOtherGridHandlerObj    = new WalmartListingOtherGridHandler('walmartListingOtherGrid');
        WalmartListingOtherMappingHandlerObj = new ListingOtherMappingHandler(
            WalmartListingOtherGridHandlerObj,
            'walmart'
        );

        WalmartListingOtherGridHandlerObj.movingHandler.setOptions(M2eProWalmart);
        WalmartListingOtherGridHandlerObj.autoMappingHandler.setOptions(M2eProWalmart);
        WalmartListingOtherGridHandlerObj.removingHandler.setOptions(M2eProWalmart);
        WalmartListingOtherGridHandlerObj.unmappingHandler.setOptions(M2eProWalmart);

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
