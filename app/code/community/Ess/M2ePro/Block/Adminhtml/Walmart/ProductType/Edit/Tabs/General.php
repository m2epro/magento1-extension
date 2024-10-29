<?php

class Ess_M2ePro_Block_Adminhtml_Walmart_ProductType_Edit_Tabs_General
    extends Mage_Adminhtml_Block_Widget_Form
{
    /** @var Ess_M2ePro_Model_Walmart_Marketplace_Repository */
    private $marketplaceRepository;
    /** @var Ess_M2ePro_Model_Walmart_ProductType */
    private $productType;
    /** @var Ess_M2ePro_Model_Walmart_ProductType_Builder */
    private $productTypeBuilder;
    private $formData = array();

    public function __construct(array $args = array())
    {
        $this->productType = $args['productType'];
        $this->productTypeBuilder = Mage::getModel('M2ePro/Walmart_ProductType_Builder');
        $this->marketplaceRepository = Mage::getModel('M2ePro/Walmart_Marketplace_Repository');

        parent::__construct($args);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('walmartProductTypeEditTabsGeneral');

        $this->formData = $this->getFormData();
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        // ---------------------------------------

        $form->addField(
            'general_id',
            'hidden',
            array(
                'name' => 'general[id]',
                'value' => $this->formData['id']
            )
        );

        // ---------------------------------------

        $fieldSet = $form->addFieldset(
            'magento_block_product_type_edit_general',
            array()
        );

        $isEdit = !$this->productType->isObjectNew();
        $marketplaceId = !$this->productType->isObjectNew()
            ? $this->productType->getMarketplaceId()
            : $this->getSuggestedMarketplaceId();

        $fieldSet->addField(
            'general_product_type_title',
            'text',
            array(
                'label' => __('Title'),
                'name' => 'general[product_type_title]',
                'value' => !$this->productType->isObjectNew()
                    ? $this->productType->getTitle()
                    : '',
                'style' => 'min-width: 240px',
                'required' => true,
                'class' => 'M2ePro-general-product-type-title',
            )
        );

        $fieldSet->addField(
            'general_marketplace_id',
            'select',
            array(
                'name' => 'general[marketplace_id]',
                'label' => Mage::helper('M2ePro')->__('Marketplace'),
                'title' => Mage::helper('M2ePro')->__('Marketplace'),
                'values' => $this->getMarketplaceDataOptions(),
                'value' => $marketplaceId,
                'class' => 'required-entry',
                'required' => true,
                'disabled' => $isEdit,
                'style' => 'min-width: 240px',
            )
        );

        $fieldSet->addField(
            'general_product_type_selection',
            'note',
            array(
                'label' => Mage::helper('M2ePro')->__('Product Type'),
                'required' => true,
                'after_element_html' => $this->getProductTypeEditHtml($isEdit)
            )
        );

        // ---------------------------------------

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return string[]
     */
    public function getFormData()
    {
        if (!$this->productType->isObjectNew()) {
            return $this->productType->getData();
        }

        return $this->productTypeBuilder->getDefaultData();
    }

    protected function _beforeToHtml()
    {
        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addControllerActions('adminhtml_walmart_productType');
        Mage::helper('M2ePro/View')->getJsPhpRenderer()->addClassConstants('Ess_M2ePro_Model_Walmart_ProductType');

        Mage::helper('M2ePro/View')->getJsTranslatorRenderer()->addTranslations(
            array(
                'The specified Product Title is already used for other Product Type. Product Type Title must be unique.'
                => Mage::helper('M2ePro')->__(
                    'The specified Product Title is already used for other Product Type. Product Type Title must be unique.
                    ')
            )
        );

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->add(
            $this->getUrl('*/adminhtml_walmart_productType/isUniqueTitle'),
            'walmart_productType/isUniqueTitle'
        );

        $isMarketplaceSuggested = $this->getSuggestedMarketplaceId() ? 'true' : 'false';
        Mage::helper('M2ePro/View')->getJsRenderer()->addOnReadyJs(<<<JS
           window.WalmartProductTypeTabsObj = new WalmartProductTypeTabs();
           window.WalmartProductTypeContentObj = new WalmartProductTypeContent();
           window.WalmartProductTypeObj = new WalmartProductType();
           window.WalmartProductTypeSearchObj = new WalmartProductTypeSearch();
           window.WalmartProductTypeFinderObj = new WalmartProductTypeFinder();

           WalmartProductTypeObj.initObservers();
           if ($isMarketplaceSuggested) {
                WalmartProductTypeObj.onChangeMarketplaceId();
           }
JS
        );

        Mage::helper('M2ePro/View')->getCssRenderer()->add(
            <<<CSS
.admin__field-label {
    text-align: left;
}
CSS
        );

        return parent::_beforeToHtml();
    }

    /**
     * @return array[]
     */
    private function getMarketplaceDataOptions()
    {
        $optionsResult = array(
            array('value' => '', 'label' => '', 'attrs' => array('style' => 'display: none;'))
        );

        foreach ($this->marketplaceRepository->findActive() as $marketplace) {
            if (
                !$marketplace->getChildObject()
                    ->isSupportedProductType()
            ) {
                continue;
            }

            $optionsResult[] = array(
                'value' => $marketplace->getId(),
                'label' => $marketplace->getTitle(),
            );
        }

        return $optionsResult;
    }

    /**
     * @param bool $isEdit
     * @return string
     */
    private function getProductTypeEditHtml($isEdit)
    {
        $textNotSelected = Mage::helper('M2ePro')->__('Not Selected');
        $textEdit = Mage::helper('M2ePro')->__('Edit');

        $title = $isEdit ? $this->getDictionaryTitle() : '';
        $quotedTitle = Mage::helper('core')->escapeHtml($title);
        $displayModeNotSelected = $isEdit ? 'none' : 'inline-block';
        $displayModeTitle = $isEdit ? 'inline-block' : 'none';

        $productTypeNick = $isEdit ? $this->productType->getNick() : '';
        $quotedNick = Mage::helper('core')->escapeHtml($productTypeNick);

        return <<<HTML
<div style="width: 240px">
    <span id="general_product_type_not_selected"
        class="product_type_nick_not_selected"
        style="display: $displayModeNotSelected;">$textNotSelected</span>
    <span id="general_selected_product_type_title"
        class="product_type_nick"
        style="display: $displayModeTitle;">$quotedTitle</span>

    <a id="product_type_edit_activator"
        style="margin-left: 1rem; display: none;"
        href="javascript: void(0);"">$textEdit</a>

    <input id="general_product_type"
        name="general[nick]"
        value="$quotedNick"
        class="required-entry m2epro-field-without-tooltip"
        type="hidden">
</div>
HTML;
    }

    /**
     * @return int
     */
    private function getSuggestedMarketplaceId()
    {
        return (int)$this->getRequest()->getParam('marketplace_id', 0);
    }

    /**
     * @return string
     */
    private function getDictionaryTitle()
    {
        return $this->productType->getDictionary()->getTitle();
    }
}
