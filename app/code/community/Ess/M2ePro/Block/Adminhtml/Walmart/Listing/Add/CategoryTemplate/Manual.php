<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Add_CategoryTemplate_Manual
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('categoryTemplateCategoryTemplateManual');
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $this->_headerText = Mage::helper('M2ePro')->__(
                "Assign %component_name% Category Policy",
                Mage::helper('M2ePro/Component_Walmart')->getTitle()
            );
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__("Assign Category Policy");
        }

        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_walmart_listing_add_categoryTemplate_manual';
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
        $this->_addButton(
            'back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'ListingGridHandlerObj.resetCategoryTemplate()',
            'class'     => 'back'
            )
        );

        // ---------------------------------------
        $this->_addButton(
            'save_and_go_to_listing_view', array(
            'id'        => 'walmart_listing_category_continue_btn',
            'label'     => Mage::helper('M2ePro')->__('Continue'),
            'onclick'   => 'ListingGridHandlerObj.completeCategoriesDataStep()',
            'class'     => 'scalable next'
            )
        );
        // ---------------------------------------
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
        $templateCategoryPopupTitle = $helper->escapeJs($helper->__('Assign Category Policy'));
        $setCategoryPolicy = $helper->escapeJs($helper->__('Set Category Policy.'));
        // ---------------------------------------

        // URL
        $mapToTemplateCategory = $this->getUrl('*/adminhtml_walmart_listing/mapToTemplateCategory');
        $unmapFromTemplateCategory = $this->getUrl('*/adminhtml_walmart_listing/unmapFromTemplateCategory');
        $validateProductsForTemplateCategoryAssign = $this->getUrl(
            '*/adminhtml_walmart_listing/validateProductsForTemplateCategoryAssign'
        );
        $assignByMagentoCategorySaveCategory = $this->getUrl(
            '*/*/assignByMagentoCategorySaveCategory', array('_current' => true)
        );
        $viewTemplateCategoriesGrid = $this->getUrl('*/*/viewTemplateCategoriesGrid');
        $checkCategoryTemplateProducts = $this->getUrl('*/*/checkCategoryTemplateProducts', array('_current' => true));

        $resetCategoryTemplate = $this->getUrl('*/*/resetCategoryTemplate', array('_current' => true));
        $checkCategoryTemplateSucceed = $this->getUrl('*/*/index', array('_current' => true, 'step' => 4));
        // ---------------------------------------

        // Translations
        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
            'Assign Category Policy' => Mage::helper('M2ePro')->__('Assign Category Policy')
            )
        );
        // ---------------------------------------

        $javascript = <<<HTML
<script type="text/javascript">
    selectTemplateCategory = function (el, templateId)
    {
        ListingGridHandlerObj.mapToTemplateCategory(el, templateId);
    };

    if (typeof M2ePro == 'undefined') {
        M2ePro = {};
        M2ePro.url = {};
        M2ePro.formData = {};
        M2ePro.customData = {};
        M2ePro.text = {};
    }

    M2ePro.text.templateCategoryPopupTitle = '{$templateCategoryPopupTitle}';
    M2ePro.text.setCategoryPolicy = '{$setCategoryPolicy}';

    M2ePro.url.mapToTemplateCategory = '{$mapToTemplateCategory}';
    M2ePro.url.assignByMagentoCategorySaveCategory = '{$assignByMagentoCategorySaveCategory}';
    M2ePro.url.unmapFromTemplateCategory = '{$unmapFromTemplateCategory}';
    M2ePro.url.validateProductsForTemplateCategoryAssign = '{$validateProductsForTemplateCategoryAssign}';
    M2ePro.url.viewTemplateCategoriesGrid = '{$viewTemplateCategoriesGrid}';

    M2ePro.url.resetCategoryTemplate = '{$resetCategoryTemplate}';
    M2ePro.url.checkCategoryTemplateSucceed = '{$checkCategoryTemplateSucceed}';
    M2ePro.url.checkCategoryTemplateProducts = '{$checkCategoryTemplateProducts}';

    M2ePro.translator.add({$translations});

    Event.observe(window, 'load', function() {

        CommonHandler.prototype.scroll_page_to_top = function() { return; }

        ListingGridHandlerObj = new CategoryTemplateGridHandler(
            '{$this->getChild('grid')->getId()}',
            {$this->getListing()->getId()}
        );
    });

</script>
HTML;

        $popupsHtml = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_walmart_listing_add_categoryTemplate_warningPopup')
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

        if ($this->listing === null) {
            $this->listing = Mage::helper('M2ePro/Component_Walmart')
                ->getObject('Listing', $listingId)->getChildObject();
        }

        return $this->listing;
    }

    //########################################
}
