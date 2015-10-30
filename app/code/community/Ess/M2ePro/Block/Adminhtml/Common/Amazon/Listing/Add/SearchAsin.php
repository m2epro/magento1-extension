<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Add_SearchAsin
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $listingId = $this->getRequest()->getParam('id');

        // Initialization block
        // ---------------------------------------
        $this->setId('searchAsinForListingProducts');
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__("Search Existing Amazon Products (ASIN/ISBN)");
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_amazon_listing_add_searchAsin';
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
        $url = $this->getUrl('*/*/removeAddedProducts', array(
            'id' => $listingId,
            '_current' => true
        ));
        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'ListingGridHandlerObj.back_click(\'' . $url . '\')',
            'class'     => 'back'
        ));

        // ---------------------------------------
        $this->_addButton('auto_action', array(
            'label'     => Mage::helper('M2ePro')->__('Edit Search Settings'),
            'onclick'   => 'ListingGridHandlerObj.editSearchSettings(\'' .
                Mage::helper('M2ePro')->__('Listing Search Settings') . '\' ,' .
                $this->getListing()->getId() .
            ');'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton('save_and_go_to_listing_view', array(
            'label'     => Mage::helper('M2ePro')->__('Continue'),
            'onclick'   => 'ListingGridHandlerObj.checkSearchResults('.$listingId.')',
            'class'     => 'scalable next'
        ));
        // ---------------------------------------
    }

    public function getGridHtml()
    {
        $listing = Mage::helper('M2ePro/Component')->getCachedUnknownObject(
            'Listing', $this->getRequest()->getParam('id')
        );

        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header','',
            array('listing' => $listing)
        );

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_add_searchAsin_help');

        $productSearchBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_productSearch_main');
        // ---------------------------------------
        $data = array(
            'id'      => 'productSearch_cleanSuggest_button',
            'label'   => Mage::helper('M2ePro')->__('Search ASIN/ISBN Manually'),
            'class'   => 'productSearch_cleanSuggest_button',
            'onclick' => 'ListingGridHandlerObj.productSearchHandler.clearSearchResultsAndManualSearch()'
        );
        $buttonResetBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $productSearchBlock->setChild('productSearch_cleanSuggest_button', $buttonResetBlock);
        // ---------------------------------------

        return $helpBlock->toHtml()
               . $viewHeaderBlock->toHtml()
               . $productSearchBlock->toHtml()
               . parent::getGridHtml();
    }

    protected function _toHtml()
    {
        $helper = Mage::helper('M2ePro');

        // TEXT
        $createEmptyListingMessage = $helper->escapeJs($helper->__('Are you sure you want to create empty Listing?'));

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

        $sendingDataToAmazonMessage = $helper->escapeJs($helper->__(
                'Sending %product_title% Product(s) data on Amazon.')
        );

        $selectItemsMessage = $helper->escapeJs(
            $helper->__('Please select the Products you want to perform the Action on.')
        );

        $assignString = Mage::helper('M2ePro')->__('Assign');
        $textConfirm = $helper->escapeJs($helper->__('Are you sure?'));

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
        $searchAsinManual = $this->getUrl('*/adminhtml_common_amazon_listing/searchAsinManual');
        $getSearchAsinMenu = $this->getUrl('*/adminhtml_common_amazon_listing/getSearchAsinMenu');
        $suggestedAsinGridHmtl = $this->getUrl('*/adminhtml_common_amazon_listing/getSuggestedAsinGrid');
        $getCategoriesByAsin = $this->getUrl('*/adminhtml_common_amazon_listing/getCategoriesByAsin');
        $searchAsinAuto = $this->getUrl('*/adminhtml_common_amazon_listing/searchAsinAuto');
        $getProductsSearchStatus = $this->getUrl('*/adminhtml_common_amazon_listing/getProductsSearchStatus');
        $mapToAsin = $this->getUrl('*/adminhtml_common_amazon_listing/mapToAsin');
        $unmapFromAsin = $this->getUrl('*/adminhtml_common_amazon_listing/unmapFromAsin');
        $mapToNewAsin = $this->getUrl('*/adminhtml_common_amazon_listing/mapToNewAsin');

        $viewSearchSettings = $this->getUrl('*/adminhtml_common_amazon_listing_productAdd/viewSearchSettings');
        $saveSearchSettings = $this->getUrl('*/adminhtml_common_amazon_listing_productAdd/saveSearchSettings');

        $checkSearchResults = $this->getUrl('*/adminhtml_common_amazon_listing_productAdd/checkSearchResults', array(
            'id' => $this->getListing()->getId()
        ));

        $showNewAsinStep = $this->getUrl('*/adminhtml_common_amazon_listing_productAdd/showNewAsinStep', array(
            'id' => $this->getListing()->getId()
        ));

        $addProductsUrl = $this->getUrl(
            '*/adminhtml_common_listing_productAdd/addProducts', array(
                'component' => $this->getData('component')
            )
        );
        $backUrl = $this->getUrl('*/*/index');
        // ---------------------------------------

        $showNotCompletedPopup = '';
        if ($this->getRequest()->getParam('not_completed', false)) {
            $showNotCompletedPopup = 'ListingGridHandlerObj.showNotCompletedPopup();';
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

    M2ePro.text.select_items_message = '{$selectItemsMessage}';
    M2ePro.text.create_empty_listing_message = '{$createEmptyListingMessage}';

    M2ePro.text.sending_data_message = '{$sendingDataToAmazonMessage}';

    M2ePro.text.new_asin_not_available = '{$newAsinNotAvailable}';
    M2ePro.text.not_synchronized_marketplace = '{$notSynchronizedMarketplace}';

    M2ePro.text.enter_productSearch_query = '{$enterProductSearchQueryMessage}';
    M2ePro.text.automap_asin_search_products = '{$autoMapAsinSearchProducts}';
    M2ePro.text.automap_asin_progress_title = '{$autoMapAsinProgressTitle}';
    M2ePro.text.automap_error_message = '{$autoMapAsinErrorMessage}';

    M2ePro.text.task_completed_message = '{$taskCompletedMessage}';
    M2ePro.text.task_completed_success_message = '{$taskCompletedSuccessMessage}';
    M2ePro.text.task_completed_warning_message = '{$taskCompletedWarningMessage}';
    M2ePro.text.task_completed_error_message = '{$taskCompletedErrorMessage}';

    M2ePro.text.assign = '{$assignString}';
    M2ePro.text.confirm = '{$textConfirm}';

    M2ePro.text.new_asin_popup_title = '{$newAsinPopupTitle}';
    M2ePro.text.not_completed_popup_title = '{$notCompletedPopupTitle}';

    M2ePro.url.add_products = '{$addProductsUrl}';
    M2ePro.url.back = '{$backUrl}';

    M2ePro.url.searchAsinManual = '{$searchAsinManual}';
    M2ePro.url.getSearchAsinMenu = '{$getSearchAsinMenu}';
    M2ePro.url.searchAsinAuto = '{$searchAsinAuto}';
    M2ePro.url.getProductsSearchStatus = '{$getProductsSearchStatus}';
    M2ePro.url.suggestedAsinGrid = '{$suggestedAsinGridHmtl}';
    M2ePro.url.getCategoriesByAsin = '{$getCategoriesByAsin}';
    M2ePro.url.mapToAsin = '{$mapToAsin}';
    M2ePro.url.unmapFromAsin = '{$unmapFromAsin}';
    M2ePro.url.mapToNewAsin = '{$mapToNewAsin}';

    M2ePro.url.viewSearchSettings = '{$viewSearchSettings}';
    M2ePro.url.saveSearchSettings = '{$saveSearchSettings}';

    M2ePro.url.checkSearchResults = '{$checkSearchResults}';
    M2ePro.url.showNewAsinStep = '{$showNewAsinStep}';

    Event.observe(window, 'load', function() {

        CommonHandler.prototype.scroll_page_to_top = function() { return; }

        ListingGridHandlerObj = new CommonAmazonListingSearchAsinGridHandler(
            '{$this->getChild('grid')->getId()}',
            {$this->getListing()->getId()}
        );

        ListingGridHandlerObj.actionHandler.setOptions(M2ePro);
        ListingGridHandlerObj.productSearchHandler.setOptions(M2ePro);

        ListingProgressBarObj = new ProgressBar('search_asin_progress_bar');
        GridWrapperObj = new AreaWrapper('search_asin_products_container');

        {$showNotCompletedPopup}
    });

</script>
HTML;

        // ---------------------------------------
        $notCompletedPopup = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_common_amazon_listing_add_searchAsin_notCompleted');
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

        if (is_null($this->listing)) {
            $this->listing = Mage::helper('M2ePro/Component_Amazon')
                ->getObject('Listing', $listingId)->getChildObject();
        }

        return $this->listing;
    }

    //########################################
}