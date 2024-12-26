<?php

class Ess_M2ePro_Block_Adminhtml_Amazon_Mapping_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /** @var Ess_M2ePro_Helper_Magento_Attribute */
    private $magentoAttributesHelper;
    /** @var Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping_Repository */
    private $mappingRepository;

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonMappingEditForm');
        $this->setContainerId('amazonMappingEdit');
        $this->setTemplate('M2ePro/amazon/mapping/edit/form.phtml');

        $this->magentoAttributesHelper = Mage::helper('M2ePro/Magento_Attribute');
        $this->mappingRepository = Mage::getModel('M2ePro/Amazon_ProductType_AttributeMapping_Repository');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save'),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping[]
     */
    public function getMappedAttributes()
    {
        return $this->mappingRepository->getAllItems();
    }

    /**
     * @return string
     */
    public function makeMagentoAttributesDropDownHtml(
        Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping $attributeMapping
    ) {
        $attributes = $this->magentoAttributesHelper->getAll();

        $html = sprintf(
            '<select id="attribute-%1$s" name="attributes[%1$s]" class="%2$s">',
            $attributeMapping->getId(),
            'select admin__control-select M2ePro-custom-attribute-can-be-created'
        );
        $html .= sprintf('<option value="">%s</option>', $this->__('None'));
        $html .= sprintf(
            '<optgroup label="%s">',
            $this->__('Magento Attributes')
        );
        foreach ($attributes as $attribute) {
            $html .= sprintf(
                '<option value="%1$s"%3$s>%2$s</option>',
                $attribute['code'],
                $attribute['label'],
                $attribute['code'] === $attributeMapping->getMagentoAttributeCode() ? ' selected' : ''
            );
        }
        $html .= '</optgroup>';
        $html .= '</select>';

        return $html;
    }
}
