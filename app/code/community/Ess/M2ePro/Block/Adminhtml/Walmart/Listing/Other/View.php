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

        $this->setId('walmartListing');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_walmart_listing_other_view';

        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Walmart')->getTitle();
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
                'account' => Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
                    'Account', $accountId
                ),
                'marketplace' => Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
                    'Marketplace', $marketplaceId
                )
            )
        );

        $mapToProductBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_listing_other_mapping');

        return $viewHeaderBlock->toHtml() . $mapToProductBlock->toHtml() . parent::getGridHtml();
    }

    //########################################

    protected function _toHtml()
    {
        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');
        $componentMode = Ess_M2ePro_Helper_Component_Walmart::NICK;

        $mapAutoToProductUrl = $this->getUrl('*/adminhtml_listing_other_mapping/autoMap');

        $removingProductsUrl = $this->getUrl('*/adminhtml_walmart_listing_other/removing');
        $unmappingProductsUrl = $this->getUrl('*/adminhtml_listing_other_mapping/unmapping');

        $someProductsWereNotMappedMessage = 'No matches were found. Please change the Mapping Attributes in <strong>';
        $someProductsWereNotMappedMessage .= 'Configuration > Account > 3rd Party Listings</strong> ';
        $someProductsWereNotMappedMessage .= 'or try to map manually.';
        $someProductsWereNotMappedMessage = $helper->escapeJs($helper->__($someProductsWereNotMappedMessage));


        $createListing = $helper->escapeJs(
            $helper->__(
                'Listings, which have the same Marketplace and Account were not found.'
            )
        );
        $createListing .= $helper->escapeJs($helper->__('Would you like to create one with Default Settings ?'));
        $popupTitle = $helper->escapeJs($helper->__('Moving Walmart Items'));

        $processingDataMessage = $helper->escapeJs($helper->__('Processing %product_title% Product(s).'));

        $autoMapProgressTitle = $helper->escapeJs($helper->__('Map Item(s) to Products'));

        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
                'Product was Mapped.' => $helper->__('Product was Mapped.'),
                'Product(s) was Mapped.' => $helper->__('Product(s) was Mapped.'),
                'Product(s) was Unmapped.' => $helper->__('Product(s) was Unmapped.'),
                'Product(s) was Removed.' => $helper->__('Product(s) was Removed.'),
                'Not enough data' => $helper->__('Not enough data'),

                'Mapping Product' => $helper->__('Mapping Product'),
                'Product does not exist.' => $helper->__('Product does not exist.'),
                'Please enter correct Product ID.' => $helper->__('Please enter correct Product ID.'),
                'Please enter correct Product ID or SKU' => $helper->__('Please enter correct Product ID or SKU'),

                'Current version only supports Simple Products. Please, choose Simple Product.' => $helper->__(
                    'Current version only supports Simple Products. Please, choose Simple Product.'
                ),

                'Item was not Mapped as the chosen %product_id% Simple Product has Custom Options.' => $helper->__(
                    'Item was not Mapped as the chosen %product_id% Simple Product has Custom Options.'
                )

            )
        );

        $urls = array();
        $urls['adminhtml_listing_other_mapping/map'] = $this->getUrl('*/adminhtml_listing_other_mapping/map');
        $urls['adminhtml_walmart_listing_productAdd/index'] = $this->getUrl(
            '*/adminhtml_walmart_listing_productAdd/index',
            array('step' => 3)
        );

        $urls['moveToListingPopupHtml'] = $this->getUrl('*/adminhtml_listing_other_moving/moveToListingGrid');
        $urls['prepareMoveToListing'] = $this->getUrl('*/adminhtml_listing_other_moving/prepareMoveToListing');
        $urls['moveToListing'] = $this->getUrl('*/adminhtml_walmart_listing_other/moveToListing');

        $urls = Mage::helper('M2ePro')->jsonEncode($urls);


        $javascriptsMain = <<<HTML
<script type="text/javascript">

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});

    M2ePro.url.mapAutoToProduct = '{$mapAutoToProductUrl}';

    M2ePro.url.removingProducts = '{$removingProductsUrl}';
    M2ePro.url.unmappingProducts = '{$unmappingProductsUrl}';

    M2ePro.text.create_listing = '{$createListing}';
    M2ePro.text.popup_title = '{$popupTitle}';

    M2ePro.text.automap_progress_title = '{$autoMapProgressTitle}';
    M2ePro.text.processing_data_message = '{$processingDataMessage}';
    M2ePro.text.failed_mapped = '{$someProductsWereNotMappedMessage}';

    M2ePro.customData.componentMode = '{$componentMode}';
    M2ePro.customData.gridId = 'walmartListingOtherGrid';

    Event.observe(window,'load',function() {
        ListingProgressBarObj = new ProgressBar('listing_other_progress_bar');
        GridWrapperObj = new AreaWrapper('listing_other_content_container');

        WalmartListingOtherGridObj    = new WalmartListingOtherGrid('walmartListingOtherGrid');
        WalmartListingOtherMappingObj = new ListingOtherMapping(
            WalmartListingOtherGridObj,
            'walmart'
        );
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
