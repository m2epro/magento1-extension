<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Other_View extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayListingOtherView');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing_other_view';

        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Ebay')->getTitle();
            $this->_headerText = Mage::helper('M2ePro')->__('%component_name% / 3rd Party Listings', $componentName);
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('3rd Party Listings');
        }

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if ($this->getRequest()->getParam('back') !== null) {
            $url = Mage::helper('M2ePro')->getBackUrl();
            $this->_addButton(
                'back', array(
                'label'   => Mage::helper('M2ePro')->__('Back'),
                'onclick' => 'CommonObj.back_click(\'' . $url . '\')',
                'class'   => 'back'
                )
            );
        }
    }

    //########################################

    public function getGridHtml()
    {
        $accountId = $this->getRequest()->getParam('account');
        $marketplaceId = $this->getRequest()->getParam('marketplace');

        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_other_view_header', '',
            array(
                'account' => Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Account', $accountId),
                'marketplace' => Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Marketplace', $marketplaceId)
            )
        );

        return $viewHeaderBlock->toHtml() . parent::getGridHtml();
    }

    //########################################

    protected function _toHtml()
    {
        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');

        $urls = array();
        $urls['adminhtml_listing_other_mapping/map'] = $this->getUrl('*/adminhtml_listing_other_mapping/map');
        $urls['adminhtml_ebay_listing_categorySettings/index'] = $this->getUrl(
            '*/adminhtml_ebay_listing_categorySettings/index', array('step' => 1)
        );

        $urls = Mage::helper('M2ePro')->jsonEncode($urls);

        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
            'Mapping Product' => $helper->__('Mapping Product'),
            'Product does not exist.' => $helper->__('Product does not exist.'),
            'Please enter correct Product ID.' => $helper->__('Please enter correct Product ID.'),
            'Product(s) was successfully Mapped.' => $helper->__('Product(s) was successfully Mapped.'),
            'Please enter correct Product ID or SKU' => $helper->__('Please enter correct Product ID or SKU')
            )
        );

        $component = Ess_M2ePro_Helper_Component_Ebay::NICK;

        $mapAutoToProductUrl = $this->getUrl('*/adminhtml_listing_other_mapping/autoMap');

        $removingProductsUrl = $this->getUrl('*/adminhtml_ebay_listing_other/removing');
        $unmappingProductsUrl = $this->getUrl('*/adminhtml_listing_other_mapping/unmapping');

        $someProductsWereNotMappedMessage = 'No matches were found. Please change the Mapping Attributes in <strong>';
        $someProductsWereNotMappedMessage .= 'Configuration > Account > 3rd Party Listings</strong> ';
        $someProductsWereNotMappedMessage .= 'or try to map manually.';
        $someProductsWereNotMappedMessage = $helper->escapeJs($helper->__($someProductsWereNotMappedMessage));

        $prepareData = $this->getUrl('*/adminhtml_listing_other_moving/prepareMoveToListing');
        $getMoveToListingGridHtml = $this->getUrl('*/adminhtml_listing_other_moving/moveToListingGrid');
        $moveToListing = $this->getUrl('*/adminhtml_ebay_listing_other/moveToListing');

        $popupTitle = $helper->escapeJs($helper->__('Moving eBay Items'));

        $successfullyMappedMessage = $helper->escapeJs($helper->__('Product was successfully Mapped.'));
        $notEnoughDataMessage = $helper->escapeJs($helper->__('Not enough data.'));
        $successfullyUnmappedMessage = $helper->escapeJs($helper->__('Product(s) was successfully Unmapped.'));
        $successfullyRemovedMessage = $helper->escapeJs($helper->__('Product(s) was successfully Removed.'));

        $processingDataMessage = $helper->escapeJs($helper->__('Processing %product_title% Product(s).'));
        $autoMapProgressTitle = $helper->escapeJs($helper->__('Map Item(s) to Products'));

        $javascript = <<<HTML
<script type="text/javascript">

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});

    M2ePro.url.mapAutoToProduct = '{$mapAutoToProductUrl}';
    M2ePro.text.failed_mapped = '{$someProductsWereNotMappedMessage}';

    M2ePro.url.prepareData = '{$prepareData}';
    M2ePro.url.getGridHtml = '{$getMoveToListingGridHtml}';
    M2ePro.url.moveToListing = '{$moveToListing}';

    M2ePro.url.removingProducts = '{$removingProductsUrl}';
    M2ePro.url.unmappingProducts = '{$unmappingProductsUrl}';

    M2ePro.text.successfully_mapped = '{$successfullyMappedMessage}';
    M2ePro.text.automap_progress_title = '{$autoMapProgressTitle}';
    M2ePro.text.processing_data_message = '{$processingDataMessage}';

    M2ePro.text.popup_title = '{$popupTitle}';

    M2ePro.text.not_enough_data = '{$notEnoughDataMessage}';
    M2ePro.text.successfully_unmapped = '{$successfullyUnmappedMessage}';
    M2ePro.text.successfully_removed = '{$successfullyRemovedMessage}';

    M2ePro.customData.componentMode = '{$component}';
    M2ePro.customData.gridId = 'ebayListingOtherGrid';

    // ---------------------------------------

    Event.observe(window,'load',function() {

        ListingProgressBarObj = new ProgressBar('listing_other_progress_bar');
        GridWrapperObj = new AreaWrapper('listing_other_content_container');

        EbayListingOtherGridObj = new EbayListingOtherGrid('ebayListingOtherViewGrid');
        EbayListingOtherMappingObj = new ListingOtherMapping(EbayListingOtherGridObj,'ebay');
    });

</script>
HTML;

        $mapToProductBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_listing_other_mapping');

        return  $javascript .
                $mapToProductBlock->toHtml() .
                '<div id="listing_other_progress_bar"></div>' .
                '<div id="listing_container_errors_summary" class="errors_summary" style="display: none;"></div>' .
                '<div id="listing_other_content_container">' .
                parent::_toHtml() .
                '</div>';
    }

    //########################################
}
