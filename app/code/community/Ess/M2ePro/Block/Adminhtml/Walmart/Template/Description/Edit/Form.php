<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Template_Description_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartTemplateDescriptionEditForm');
        // ---------------------------------------

        $this->setTemplate('M2ePro/walmart/template/description/form.phtml');
    }

    //########################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
            'id'      => 'edit_form',
            'method'  => 'post',
            'action'  => $this->getUrl('*/*/save'),
            'enctype' => 'multipart/form-data'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
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
        for ($i = 0; $i < 5; $i++) {
            $button = $this->getMultiElementButton('key_features', $i);
            $this->setChild("select_attributes_for_key_features_{$i}_button", $button);
        }

        // ---------------------------------------

        // ---------------------------------------
        for ($i = 0; $i < 5; $i++) {
            $button = $this->getMultiElementButton('other_features', $i);
            $this->setChild("select_attributes_for_other_features_{$i}_button", $button);
        }

        // ---------------------------------------

        // ---------------------------------------
        for ($i = 0; $i < 5; $i++) {
            $button = $this->getNameValueMultiElementButton('attributes', $i);
            $this->setChild("select_attributes_for_attributes_{$i}_button", $button);
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
        WalmartTemplateDescriptionObj.multi_element_keyup('{$type}', $('{$type}_{$index}'));
JS;
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Insert'),
            'onclick' => $onClick,
            'class'   => "select_attributes_for_{$type}_{$index}_button"
        );
        return $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
    }

    protected function getNameValueMultiElementButton($type, $index)
    {
        $onClick = <<<JS
        AttributeObj.appendToText('select_attributes_for_{$type}_{$index}', '{$type}_value_{$index}');
        WalmartTemplateDescriptionObj.multi_element_keyup('{$type}', $('{$type}_value_{$index}'));
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

        return $value === null ? sprintf($html, "value='{$attributeCode}'")
            : sprintf($html, "attribute_code='{$attributeCode}' value='{$value}'");
    }

    //########################################
}
