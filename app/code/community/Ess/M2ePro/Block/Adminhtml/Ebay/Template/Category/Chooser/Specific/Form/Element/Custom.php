<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Template_Category_Specific as Specific;

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Category_Chooser_Specific_Form_Element_Custom extends
    Varien_Data_Form_Element_Abstract
{
    //########################################

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setType('specifics');
    }

    //########################################

    public function getElementHtml()
    {
        return '';
    }

    public function getHtmlAttributes()
    {
        return array('title', 'class', 'style', 'onclick', 'onchange', 'disabled', 'readonly', 'tabindex');
    }

    //########################################

    public function getSpecifics()
    {
        return array_merge(
            array(
                array(
                    '__template__'          => true,
                    'attribute_title'        => '',
                    'value_mode'             => '',
                    'value_custom_value'     => '',
                    'value_custom_attribute' => ''
                ),
            ),
            $this->getData('specifics')
        );
    }

    //########################################

    public function getModeHtml($index, $specific)
    {
        $element = new Varien_Data_Form_Element_Hidden(
            array(
                'name'     => 'specific[custom_' . $index . '][mode]',
                'class'    => 'specific_mode',
                'value'    => Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_CUSTOM_ITEM_SPECIFICS,
                'disabled' => isset($specific['__template__'])
            )
        );
        $element->setForm($this->getForm());
        $element->setId('specific_custom_mode_' . $index);

        return $element->getElementHtml();
    }

    public function getAttributeTitleLabelHtml($index, $specific)
    {
        $display = 'display: none;';
        if (isset($specific['value_mode']) &&
            ($specific['value_mode'] == Specific::VALUE_MODE_CUSTOM_ATTRIBUTE)
        ) {
            $display = '';
        }

        $labelText = Mage::helper('M2ePro')->__('From Attribute label');

        return <<<HTML
<span id="specific_custom_attribute_title_label_{$index}" style="{$display}">
    <strong>{$labelText}</strong>
</span>
HTML;
    }

    public function getAttributeTitleInputHtml($index, $specific)
    {
        $display = 'display: none;';
        if (isset($specific['value_mode']) &&
            ($specific['value_mode'] == Specific::VALUE_MODE_CUSTOM_VALUE ||
                $specific['value_mode'] == Specific::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE)
        ) {
            $display = '';
        }

        $element = new Varien_Data_Form_Element_Text(
            array(
                'name'   => 'specific[custom_' . $index . '][attribute_title]',
                'style'  => 'width: 99%; padding-left: 2px; padding-right: 0;' . $display,
                'class'  => 'M2ePro-required-when-visible M2ePro-custom-specific-attribute-title custom-item-specific',
                'value'  => isset($specific['attribute_title']) ? $specific['attribute_title'] : '',
                'disabled' => isset($specific['__template__'])
            )
        );

        $element->setNoSpan(true);
        $element->setForm($this->getForm());
        $element->setId('specific_custom_attribute_title_input_' . $index);

        return $element->getHtml();
    }

    public function getValueModeSelectHtml($index, $specific)
    {
        $values = array(
            array(
                'label' => '',
                'value' => '',
                'style' => 'display: none'
            ),
            array(
                'value' => Specific::VALUE_MODE_CUSTOM_ATTRIBUTE,
                'label' => Mage::helper('M2ePro')->__('Custom Attribute'),
            ),
            array(
                'value' => Specific::VALUE_MODE_CUSTOM_VALUE,
                'label' => Mage::helper('M2ePro')->__('Custom Value'),
            ),
            array(
                'value' => Specific::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE,
                'label' => Mage::helper('M2ePro')->__('Custom Label / Attribute'),
            )
        );

        $element = new Varien_Data_Form_Element_Select(
            array(
                'name'     => 'specific[custom_' . $index . '][value_mode]',
                'class'    => 'M2ePro-required-when-visible specific-value-mode',
                'style'    => 'width: 100%',
                'onchange' => 'EbayTemplateCategorySpecificsObj.customSpecificModeChange(this);',
                'value'    => isset($specific['value_mode']) ? $specific['value_mode'] : '',
                'values'   => $values,
                'disabled' => isset($specific['__template__'])
            )
        );
        $element->setNoSpan(true);
        $element->setForm($this->getForm());
        $element->setId('specific_custom_value_mode_' . $index);

        return $element->getHtml();
    }

    public function getValueCustomAttributeHtml($index, $specific)
    {
        $attributes = Mage::helper('M2ePro/Magento_Attribute')->getAll();

        $attributesValues = array(
            array(
                'label' => '',
                'value' => ''
            )
        );
        foreach ($attributes as $attribute) {
            $attributesValues[] = array(
                'label' => $attribute['label'],
                'value' => $attribute['code']
            );
        }

        $display = 'display: none;';
        if (isset($specific['value_mode']) &&
            ($specific['value_mode'] == Specific::VALUE_MODE_CUSTOM_ATTRIBUTE ||
                $specific['value_mode'] == Specific::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE)
        ) {
            $display = '';
        }

        $element = new Varien_Data_Form_Element_Select(
            array(
                'name'   => 'specific[custom_' . $index . '][value_custom_attribute]',
                'style'  => 'width: 100%;' . $display,
                'class'  => 'M2ePro-required-when-visible M2ePro-custom-attribute-can-be-created',
                'value'  => isset($specific['value_custom_attribute']) ? $specific['value_custom_attribute'] : '',
                'values' => $attributesValues,
                'apply_to_all_attribute_sets' => 0,
                'disabled' => isset($specific['__template__'])
            )
        );
        $element->setNoSpan(true);
        $element->setForm($this->getForm());
        $element->setId('specific_custom_value_custom_attribute_' . $index);

        return $element->getHtml();
    }

    public function getValueCustomValueHtml($index, $specific)
    {
        if (empty($specific['value_custom_value'])) {
            $customValues = array('');
        } else {
            $customValues = Mage::helper('M2ePro')->jsonDecode($specific['value_custom_value']);
        }

        $display = 'display: none;';
        if (isset($specific['value_mode']) &&
            $specific['value_mode'] == Specific::VALUE_MODE_CUSTOM_VALUE
        ) {
            $display = '';
        }

        $element = new Varien_Data_Form_Element_Text(
            array(
                'name'     => 'specific[custom_' . $index . '][value_custom_value][]',
                'style'    => 'width: 99.4%; padding-left: 2px; padding-right: 0;' . $display,
                'class'    => 'M2ePro-required-when-visible item-specific',
                'value'    => $customValues[0],
                'disabled' => isset($specific['__template__'])
            )
        );
        $element->setNoSpan(true);
        $element->setForm($this->getForm());
        $element->setId('specific_custom_value_custom_value_' . $index);

        return $element->toHtml();
    }

    //########################################
}
