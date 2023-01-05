<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Product_Add_NewAsin_Manual
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('newAsinDescriptionTemplateManual');

        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $this->_headerText = Mage::helper('M2ePro')->__(
                "Set %component_name% Description Policy for New ASIN/ISBN Creation",
                Mage::helper('M2ePro/Component_Amazon')->getTitle()
            );
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__("Set Description Policy for New ASIN/ISBN Creation");
        }

        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_listing_product_add_newAsin_manual';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = $this->getUrl(
            '*/*/resetNewAsin', array(
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

        $url = $this->getUrl('*/*/index', array('_current' => true, 'step' => 5));
        $this->_addButton(
            'add_products_new_asin_manual_continue', array(
                'id'      => 'add_products_new_asin_manual_continue',
                'label'   => Mage::helper('M2ePro')->__('Continue'),
                'onclick' => 'ListingGridObj.checkProducts(\'' . $url . '\')',
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

        return $viewHeaderBlock->toHtml() . parent::getGridHtml();
    }

    protected function _toHtml()
    {
        $helper = Mage::helper('M2ePro');

        // TEXT
        $templateDescriptionPopupTitle = $helper->escapeJs($helper->__('Assign Description Policy'));
        $setDescriptionPolicy = $helper->escapeJs($helper->__('Set Description Policy.'));
        // ---------------------------------------

        // URL
        $mapToTemplateDescription = $this->getUrl('*/adminhtml_amazon_listing/mapToTemplateDescription');
        $unmapFromTemplateDescription = $this->getUrl('*/adminhtml_amazon_listing/unmapFromTemplateDescription');
        $validateProductsForTemplateDescriptionAssign = $this->getUrl(
            '*/adminhtml_amazon_listing/validateProductsForTemplateDescriptionAssign'
        );
        $assignByMagentoCategorySaveCategory = $this->getUrl(
            '*/*/assignByMagentoCategorySaveCategory', array('_current' => true)
        );
        $assignByMagentoCategoryDeleteCategory = $this->getUrl(
            '*/*/assignByMagentoCategoryDeleteCategory', array('_current' => true)
        );
        $viewTemplateDescriptionsGrid = $this->getUrl('*/*/viewTemplateDescriptionsGrid');

        $mapToNewAsin = $this->getUrl('*/adminhtml_amazon_listing/mapToNewAsin');
        $unmapFromNewAsin = $this->getUrl('*/adminhtml_amazon_listing/unmapFromAsin');
        $checkNewAsinProducts = $this->getUrl('*/*/checkNewAsinProducts', array('_current' => true));
        // ---------------------------------------

        $javascript = <<<HTML
<script type="text/javascript">
    selectTemplateDescription = function (el, templateId, mapToGeneralId)
    {
        ListingGridObj.mapToTemplateDescription(el, templateId, mapToGeneralId);
    };

    if (typeof M2ePro == 'undefined') {
        M2ePro = {};
        M2ePro.url = {};
        M2ePro.formData = {};
        M2ePro.customData = {};
        M2ePro.text = {};
    }

    M2ePro.text.templateDescriptionPopupTitle = '{$templateDescriptionPopupTitle}';
    M2ePro.text.setDescriptionPolicy = '{$setDescriptionPolicy}';

    M2ePro.url.mapToTemplateDescription = '{$mapToTemplateDescription}';
    M2ePro.url.unmapFromTemplateDescription = '{$unmapFromTemplateDescription}';
    M2ePro.url.validateProductsForTemplateDescriptionAssign = '{$validateProductsForTemplateDescriptionAssign}';
    M2ePro.url.viewTemplateDescriptionsGrid = '{$viewTemplateDescriptionsGrid}';

    M2ePro.url.mapToNewAsin = '{$mapToNewAsin}';
    M2ePro.url.assignByMagentoCategorySaveCategory = '{$assignByMagentoCategorySaveCategory}';
    M2ePro.url.assignByMagentoCategoryDeleteCategory = '{$assignByMagentoCategoryDeleteCategory}';
    M2ePro.url.unmapFromNewAsin = '{$unmapFromNewAsin}';
    M2ePro.url.checkNewAsinProducts = '{$checkNewAsinProducts}';

    Event.observe(window, 'load', function() {

        Common.prototype.scroll_page_to_top = function() { return; }

        ListingGridObj = new AmazonListingNewAsinTemplateDescriptionGrid(
            '{$this->getChild('grid')->getId()}',
            {$this->getListing()->getId()}
        );
    });

</script>
HTML;

        return $javascript .
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
