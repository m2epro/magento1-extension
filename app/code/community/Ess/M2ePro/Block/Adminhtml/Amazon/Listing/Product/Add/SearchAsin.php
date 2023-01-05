<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Product_Add_SearchAsin
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $listingId = $this->getRequest()->getParam('id');

        $this->setId('searchAsinForListingProducts');

        $this->_headerText = Mage::helper('M2ePro')->__("Search Existing Amazon Products (ASIN/ISBN)");
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_listing_product_add_searchAsin';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = $this->getUrl(
            '*/*/removeAddedProducts', array(
                'id'       => $listingId,
                '_current' => true
            )
        );
        $this->_addButton(
            'back', array(
                'label'   => Mage::helper('M2ePro')->__('Back'),
                'onclick' => 'ListingGridObj.back_click(\'' . $url . '\')',
                'class'   => 'back'
            )
        );

        $this->_addButton(
            'edit_search_settings', array(
                'label'   => Mage::helper('M2ePro')->__('Edit Search Settings'),
                'onclick' => 'ListingGridObj.editSearchSettings(\'' .
                             Mage::helper('M2ePro')->__('Listing Search Settings') . '\' ,' .
                             $this->getListing()->getId() .
                             ');'
            )
        );

        $url = $this->getUrl(
            '*/adminhtml_amazon_listing_productAdd/exitToListing',
            array('id' => $this->getRequest()->getParam('id'))
        );
        $confirm =
            $this->__('Are you sure?') . '\n\n'
            . $this->__('All unsaved changes will be lost and you will be returned to the Listings grid.');
        $this->_addButton(
            'exit_to_listing',
            array(
                'id' => 'exit_to_listing',
                'label' => Mage::helper('M2ePro')->__('Cancel'),
                'onclick' => "confirmSetLocation('$confirm', '$url');",
                'class' => 'scalable'
            )
        );

        $this->_addButton(
            'add_products_search_asin_continue', array(
                'id' => 'add_products_search_asin_continue',
                'label'   => Mage::helper('M2ePro')->__('Continue'),
                'onclick' => 'ListingGridObj.checkSearchResults(' . $listingId . ')',
                'class'   => 'scalable next'
            )
        );
    }

    public function getGridHtml()
    {
        $listing = Mage::helper('M2ePro/Component')->getCachedUnknownObject(
            'Listing', $this->getRequest()->getParam('id')
        );

        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header', '',
            array('listing' => $listing)
        );

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_product_add_searchAsin_help');

        $productSearchBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_listing_productSearch_main');

        $data = array(
            'id'      => 'productSearch_cleanSuggest_button',
            'label'   => Mage::helper('M2ePro')->__('Search ASIN/ISBN Manually'),
            'class'   => 'productSearch_cleanSuggest_button',
            'onclick' => 'ListingGridObj.productSearchHandler.clearSearchResultsAndManualSearch()'
        );

        $buttonResetBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $productSearchBlock->setChild('productSearch_cleanSuggest_button', $buttonResetBlock);

        return $helpBlock->toHtml()
               . $viewHeaderBlock->toHtml()
               . $productSearchBlock->toHtml()
               . parent::getGridHtml();
    }

    protected function _toHtml()
    {
        $helper = Mage::helper('M2ePro');

        $sendingDataToAmazonMessage = $helper->escapeJs(
            $helper->__(
                'Sending %product_title% Product(s) data on Amazon.'
            )
        );

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

        $newAsinPopupTitle = $helper->escapeJs($helper->__('New ASIN/ISBN creation'));
        $notCompletedPopupTitle = $helper->escapeJs(
            $helper->__('Adding of New Products to the Listing was not competed')
        );
        // ---------------------------------------

        // URL
        $searchAsinManual = $this->getUrl('*/adminhtml_amazon_listing/searchAsinManual');
        $getSearchAsinMenu = $this->getUrl('*/adminhtml_amazon_listing/getSearchAsinMenu');
        $suggestedAsinGridHmtl = $this->getUrl('*/adminhtml_amazon_listing/getSuggestedAsinGrid');
        $searchAsinAuto = $this->getUrl('*/adminhtml_amazon_listing/searchAsinAuto');
        $getProductsSearchStatus = $this->getUrl('*/adminhtml_amazon_listing/getProductsSearchStatus');
        $mapToAsin = $this->getUrl('*/adminhtml_amazon_listing/mapToAsin');
        $unmapFromAsin = $this->getUrl('*/adminhtml_amazon_listing/unmapFromAsin');
        $mapToNewAsin = $this->getUrl('*/adminhtml_amazon_listing/mapToNewAsin');

        $addAttributesToVocabularyUrl = $this->getUrl(
            '*/adminhtml_amazon_listing_variation_product_manage/addAttributesToVocabulary'
        );
        $addOptionsToVocabularyUrl = $this->getUrl(
            '*/adminhtml_amazon_listing_variation_product_manage/addOptionsToVocabulary'
        );

        $viewSearchSettings = $this->getUrl('*/adminhtml_amazon_listing_productAdd/viewSearchSettings');
        $saveSearchSettings = $this->getUrl('*/adminhtml_amazon_listing_productAdd/saveSearchSettings');

        $checkSearchResults = $this->getUrl(
            '*/adminhtml_amazon_listing_productAdd/checkSearchResults', array(
                'id' => $this->getListing()->getId()
            )
        );

        $showNewAsinStep = $this->getUrl(
            '*/adminhtml_amazon_listing_productAdd/showNewAsinStep', array(
                'id'     => $this->getListing()->getId(),
                'wizard' => $this->getRequest()->getParam('wizard')
            )
        );

        $addProductsUrl = $this->getUrl(
            '*/adminhtml_amazon_listing_productAdd/addProducts', array(
                'wizard' => $this->getRequest()->getParam('wizard')
            )
        );
        $backUrl = $this->getUrl(
            '*/*/index', array(
                'wizard' => $this->getRequest()->getParam('wizard')
            )
        );
        // ---------------------------------------

        $showNotCompletedPopup = '';
        if ($this->getRequest()->getParam('not_completed', false)) {
            $showNotCompletedPopup = 'ListingGridObj.showNotCompletedPopup();';
        }

        $javascript = <<<HTML
<script type="text/javascript">
    if (typeof M2ePro == 'undefined') {
        M2ePro = {};
        M2ePro.url = {};
        M2ePro.formData = {};
        M2ePro.customData = {};
        M2ePro.text = {};
    }

    M2ePro.text.sending_data_message = '{$sendingDataToAmazonMessage}';

    M2ePro.text.new_asin_not_available = '{$newAsinNotAvailable}';
    M2ePro.text.not_synchronized_marketplace = '{$notSynchronizedMarketplace}';

    M2ePro.text.enter_productSearch_query = '{$enterProductSearchQueryMessage}';
    M2ePro.text.automap_asin_search_products = '{$autoMapAsinSearchProducts}';
    M2ePro.text.automap_asin_progress_title = '{$autoMapAsinProgressTitle}';
    M2ePro.text.automap_error_message = '{$autoMapAsinErrorMessage}';

    M2ePro.text.new_asin_popup_title = '{$newAsinPopupTitle}';
    M2ePro.text.not_completed_popup_title = '{$notCompletedPopupTitle}';

    M2ePro.url.add_products = '{$addProductsUrl}';
    M2ePro.url.back = '{$backUrl}';

    M2ePro.url.searchAsinManual = '{$searchAsinManual}';
    M2ePro.url.getSearchAsinMenu = '{$getSearchAsinMenu}';
    M2ePro.url.searchAsinAuto = '{$searchAsinAuto}';
    M2ePro.url.getProductsSearchStatus = '{$getProductsSearchStatus}';
    M2ePro.url.suggestedAsinGrid = '{$suggestedAsinGridHmtl}';
    M2ePro.url.mapToAsin = '{$mapToAsin}';
    M2ePro.url.unmapFromAsin = '{$unmapFromAsin}';
    M2ePro.url.mapToNewAsin = '{$mapToNewAsin}';

    M2ePro.url.addAttributesToVocabulary = '{$addAttributesToVocabularyUrl}';
    M2ePro.url.addOptionsToVocabulary = '{$addOptionsToVocabularyUrl}';

    M2ePro.url.viewSearchSettings = '{$viewSearchSettings}';
    M2ePro.url.saveSearchSettings = '{$saveSearchSettings}';

    M2ePro.url.checkSearchResults = '{$checkSearchResults}';
    M2ePro.url.showNewAsinStep = '{$showNewAsinStep}';

    Event.observe(window, 'load', function() {

        Common.prototype.scroll_page_to_top = function() { return; }

        ListingGridObj = new AmazonListingSearchAsinGrid(
            '{$this->getChild('grid')->getId()}',
            {$this->getListing()->getId()}
        );

        ListingProgressBarObj = new ProgressBar('search_asin_progress_bar');
        GridWrapperObj = new AreaWrapper('search_asin_products_container');

        {$showNotCompletedPopup}
    });

</script>
HTML;

        // ---------------------------------------
        $notCompletedPopup = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_listing_product_add_searchAsin_notCompleted'
        );
        // ---------------------------------------

        return $notCompletedPopup->toHtml() .
            $javascript .
            '<div id="search_asin_progress_bar"></div>' .
                '<div id="search_asin_products_container">' .
                parent::_toHtml() .
            '</div>';
    }

    //########################################

    public function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if ($this->_listing === null) {
            $this->_listing = Mage::helper('M2ePro/Component_Amazon')
                ->getObject('Listing', $listingId)->getChildObject();
        }

        return $this->_listing;
    }

    //########################################
}
