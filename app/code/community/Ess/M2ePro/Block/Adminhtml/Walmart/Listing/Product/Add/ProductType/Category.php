<?php

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Product_Add_ProductType_Category
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('productTypeProductTypeCategory');

        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $this->_headerText = Mage::helper('M2ePro')->__(
                "Assign %component_name% Product Type",
                Mage::helper('M2ePro/Component_Walmart')->getTitle()
            );
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__("Assign Product Type");
        }

        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_walmart_listing_product_add_productType_category';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton(
            'back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'ListingGridObj.resetProductType()',
            'class'     => 'back'
            )
        );

        $url = $this->getUrl(
            '*/adminhtml_walmart_listing_productAdd/exitToListing',
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
            'add_products_category_product_type_continue', array(
                'id'        => 'add_products_category_product_type_continue',
                'label'     => Mage::helper('M2ePro')->__('Continue'),
                'onclick'   => 'ListingGridObj.completeCategoriesDataStep()',
                'class'     => 'scalable next'
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

        return $viewHeaderBlock->toHtml() . parent::getGridHtml();
    }

    protected function _toHtml()
    {
        $helper = Mage::helper('M2ePro');

        // TEXT
        $productTypePopupTitle = $helper->escapeJs($helper->__('Assign Product Type'));
        // ---------------------------------------

        // URL
        $mapToProductType = $this->getUrl('*/adminhtml_walmart_listing/mapToProductType');
        $validateProductsForProductTypeAssign = $this->getUrl(
            '*/adminhtml_walmart_listing/validateProductsForProductTypeAssign'
        );
        $assignByMagentoCategorySaveProductType = $this->getUrl(
            '*/*/assignByMagentoCategorySaveProductType', array('_current' => true)
        );
        $viewProductTypesGrid = $this->getUrl('*/*/viewProductTypesGrid');
        $checkProductTypeProducts = $this->getUrl('*/*/checkProductTypeProducts', array('_current' => true));

        $resetProductType = $this->getUrl('*/*/resetProductType', array('_current' => true));
        $checkProductTypeSucceed = $this->getUrl('*/*/index', array('_current' => true, 'step' => 4));
        // ---------------------------------------

        // Translations
        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
            'Assign Product Type' => Mage::helper('M2ePro')->__('Assign Product Type')
            )
        );
        // ---------------------------------------

        $javascript = <<<HTML
<script type="text/javascript">
    selectProductType = function (el, templateId)
    {
        ListingGridObj.mapToProductType(el, templateId);
    };

    if (typeof M2ePro == 'undefined') {
        M2ePro = {};
        M2ePro.url = {};
        M2ePro.formData = {};
        M2ePro.customData = {};
        M2ePro.text = {};
    }

    M2ePro.text.productTypePopupTitle = '{$productTypePopupTitle}';

    M2ePro.url.mapToProductType = '{$mapToProductType}';
    M2ePro.url.assignByMagentoCategorySaveProductType = '{$assignByMagentoCategorySaveProductType}';
    M2ePro.url.validateProductsForProductTypeAssign = '{$validateProductsForProductTypeAssign}';
    M2ePro.url.viewProductTypesGrid = '{$viewProductTypesGrid}';

    M2ePro.url.resetProductType = '{$resetProductType}';
    M2ePro.url.checkProductTypeSucceed = '{$checkProductTypeSucceed}';
    M2ePro.url.checkProductTypeProducts = '{$checkProductTypeProducts}';

    M2ePro.translator.add({$translations});

    Event.observe(window, 'load', function() {

        Common.prototype.scroll_page_to_top = function() { return; }

        ListingGridObj = new WalmartListingProductTypeGrid(
            '{$this->getChild('grid')->getId()}',
            {$this->getListing()->getId()}
        );
    });

</script>
HTML;

        $popupsHtml = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_walmart_listing_product_add_productType_warningPopup')
            ->toHtml();

        return $javascript .
            '<div id="search_products_container">' .
                parent::_toHtml() .
            '</div>' .
            '<div style="display: none">' .
                $popupsHtml .
            '</div>';
    }

    //########################################

    public function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if ($this->_listing === null) {
            $this->_listing = Mage::helper('M2ePro/Component_Walmart')
                ->getObject('Listing', $listingId)->getChildObject();
        }

        return $this->_listing;
    }

    //########################################
}
