<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Template_Description_Edit_Tabs_Definition
    extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonTemplateDescriptionEditTabsDefinition');
        // ---------------------------------------

        $this->setTemplate('M2ePro/amazon/template/description/tabs/definition.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Insert'),
            'onclick' => "AttributeObj.appendToText('select_attributes_for_title', 'title_template');",
            'class'   => 'select_attributes_for_title_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('select_attributes_for_title_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Insert'),
            'onclick' => "AttributeObj.appendToTextarea('#' + $('select_attributes').value + '#');",
            'class'   => 'add_product_attribute_button',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('add_product_attribute_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        for ($i = 0; $i < 4; $i++) {
            $button = $this->getMultiElementButton('target_audience', $i);
            $this->setChild("select_attributes_for_target_audience_{$i}_button", $button);
        }

        // ---------------------------------------

        // ---------------------------------------
        for ($i = 0; $i < 5; $i++) {
            $button = $this->getMultiElementButton('bullet_points', $i);
            $this->setChild("select_attributes_for_bullet_points_{$i}_button", $button);
        }

        // ---------------------------------------

        // ---------------------------------------
        for ($i = 0; $i < 5; $i++) {
            $button = $this->getMultiElementButton('search_terms', $i);
            $this->setChild("select_attributes_for_search_terms_{$i}_button", $button);
        }

        // ---------------------------------------

        // ---------------------------------------
        $attributeHelper = Mage::helper('M2ePro/Magento_Attribute');
        $this->setData('all_attributes', $attributeHelper->getAll());
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################

    protected function getMultiElementButton($type, $index)
    {
        $onClick = <<<JS
AttributeObj.appendToText('select_attributes_for_{$type}_{$index}', '{$type}_{$index}');
AmazonTemplateDescriptionDefinitionObj.multi_element_keyup('{$type}',{value:' '});
JS;
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Insert'),
            'onclick' => $onClick,
            'class'   => "select_attributes_for_{$type}_{$index}_button"
        );
        return $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
    }

    //########################################

    public function getForceAddedAttributeOptionHtml($attributeCode, $availableValues, $value = null)
    {
        if (empty($attributeCode) ||
            Mage::helper('M2ePro/Magento_Attribute')->isExistInAttributesArray($attributeCode, $availableValues)) {
            return '';
        }

        $attributeLabel = Mage::helper('M2ePro')
            ->escapeHtml(Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($attributeCode));

        $html = "<option %s selected=\"selected\">{$attributeLabel}</option>";

        if ($value === null) {
            return sprintf($html, "value='{$attributeCode}'");
        }

        return sprintf($html, "attribute_code='{$attributeCode}' value='{$value}'");
    }

    // ---------------------------------------

    public function getWeightUnits()
    {
        return array(
            'GR',
            'KG',
            'OZ',
            'LB',
            'MG'
        );
    }

    public function getDimensionsUnits()
    {
        return array(
            'MM',
            'CM',
            'M',
            'IN',
            'FT'
        );
    }

    //########################################
}