<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Template_Category_Specific as Specific;
use Ess_M2ePro_Block_Adminhtml_Ebay_Template_Category_Chooser_Specific_Form_Element_Dictionary_Multiselect as Multi;

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Category_Chooser_Specific_Form_Element_Dictionary extends
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

    //########################################

    public function getSpecifics()
    {
        return $this->getData('specifics');
    }

    //########################################

    public function getAttributeTitleHiddenHtml($index, $specific)
    {
        $element = new Varien_Data_Form_Element_Hidden(
            array(
                'name' => 'specific[dictionary_' . $index . '][attribute_title]',
                'class' => 'M2ePro-dictionary-specific-attribute-title',
                'value' => $specific['title']
            )
        );
        $element->setForm($this->getForm());
        $element->setId('specific_dictionary_attribute_title_' . $index);

        return $element->getElementHtml();
    }

    public function getModeHtml($index)
    {
        $element = new Varien_Data_Form_Element_Hidden(
            array(
                'name'  => 'specific[dictionary_' . $index . '][mode]',
                'class' => 'specific_mode',
                'value' => Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_ITEM_SPECIFICS
            )
        );
        $element->setForm($this->getForm());
        $element->setId('specific_dictionary_mode_' . $index);

        return $element->getElementHtml();
    }

    public function getAttributeTitleLabelHtml($index, $specific)
    {
        $required = '';
        if ($specific['required']) {
            $required = '&nbsp;<span class="required">*</span>';
        }

        return <<<HTML
    <span id="specific_dictionary_attribute_title_label_{$index}">{$specific['title']}{$required}</span>
HTML;
    }

    public function getValueModeSelectHtml($index, $specific)
    {
        $values = array(
            Specific::VALUE_MODE_NONE => array(
                'value' => Specific::VALUE_MODE_NONE,
                'label' => Mage::helper('M2ePro')->__('None'),
            ),
            Specific::VALUE_MODE_EBAY_RECOMMENDED => array(
                'value' => Specific::VALUE_MODE_EBAY_RECOMMENDED,
                'label' => Mage::helper('M2ePro')->__('eBay Recommended'),
            ),
            Specific::VALUE_MODE_CUSTOM_VALUE => array(
                'value' => Specific::VALUE_MODE_CUSTOM_VALUE,
                'label' => Mage::helper('M2ePro')->__('Custom Value'),
            ),
            Specific::VALUE_MODE_CUSTOM_ATTRIBUTE => array(
                'value' => Specific::VALUE_MODE_CUSTOM_ATTRIBUTE,
                'label' => Mage::helper('M2ePro')->__('Custom Attribute'),
            ),
        );

        if ($specific['required']) {
            $values[Specific::VALUE_MODE_NONE] = array(
                'label' => '',
                'value' => '',
                'style' => 'display: none'
            );
        }

        if ($specific['type'] == Specific::RENDER_TYPE_TEXT) {
            unset($values[Specific::VALUE_MODE_EBAY_RECOMMENDED]);
        }

        if ($specific['type'] == Specific::RENDER_TYPE_SELECT_ONE ||
            $specific['type'] == Specific::RENDER_TYPE_SELECT_MULTIPLE
        ) {
            unset($values[Specific::VALUE_MODE_CUSTOM_VALUE]);
        }

        if (empty($specific['values'])) {
            if ($specific['type'] == Specific::RENDER_TYPE_SELECT_ONE_OR_TEXT ||
                $specific['type'] == Specific::RENDER_TYPE_SELECT_MULTIPLE_OR_TEXT
            ) {
                unset($values[Specific::VALUE_MODE_EBAY_RECOMMENDED]);
            }
        }

        $element = new Varien_Data_Form_Element_Select(
            array(
                'name'     => 'specific[dictionary_' . $index . '][value_mode]',
                'class'    => 'specific-value-mode',
                'style'    => 'width: 100%',
                'onchange' => "EbayTemplateCategorySpecificsObj.dictionarySpecificModeChange('{$index}', this);",
                'value' => !empty($specific['template_specific']) ? $specific['template_specific']['value_mode'] : null,
                'values' => $values
            )
        );

        $element->setNoSpan(true);
        $element->setClass('M2ePro-required-when-visible');
        $element->setForm($this->getForm());
        $element->setId('specific_dictionary_value_mode_' . $index);

        return $element->getHtml();
    }

    public function getValueEbayRecomendedHtml($index, $specific)
    {
        $values = array();
        foreach ($specific['values'] as $value) {
            $values[] = array(
                'label' => $value['value'],
                'value' => $value['value']
            );
        }

        $display = 'display: none;';
        $disabled = true;
        if (isset($specific['template_specific']['value_mode']) &&
            $specific['template_specific']['value_mode'] == Specific::VALUE_MODE_EBAY_RECOMMENDED) {
            $display = '';
            $disabled = false;
        }

        if ($specific['type'] == Specific::RENDER_TYPE_SELECT_MULTIPLE ||
            $specific['type'] == Specific::RENDER_TYPE_SELECT_MULTIPLE_OR_TEXT
        ) {
            $element = new Multi(
                array(
                    'name' => 'specific[dictionary_' . $index . '][value_ebay_recommended][]',
                    'style' => 'width: 100%;' . $display,
                    'value' => empty($specific['template_specific']['value_ebay_recommended'])
                        ? array()
                        : Mage::helper('M2ePro')->jsonDecode($specific['template_specific']['value_ebay_recommended']),
                    'values' => $values,
                    'data-min_values' => $specific['min_values'],
                    'data-max_values' => $specific['max_values'],
                    'disabled' => $disabled
                )
            );
        } else {
            array_unshift(
                $values,
                array(
                    'label' => '',
                    'value' => '',
                    'style' => 'display: none'
                )
            );
            $element = new Varien_Data_Form_Element_Select(
                array(
                    'name' => 'specific[dictionary_' . $index . '][value_ebay_recommended]',
                    'style' => 'width: 100%;' . $display,
                    'value' => empty($specific['template_specific']['value_ebay_recommended'])
                        ? ''
                        : Mage::helper('M2ePro')->jsonDecode($specific['template_specific']['value_ebay_recommended']),
                    'values' => $values,
                    'disabled' => $disabled
                )
            );
        }

        if ($specific['required']) {
            $element->setClass('M2ePro-required-when-visible');
        }

        $element->setNoSpan(true);
        $element->setForm($this->getForm());
        $element->setId('specific_dictionary_value_ebay_recommended_' . $index);

        return $element->getHtml();
    }

    public function getValueCustomValueHtml($index, $specific)
    {
        $addMoreTxt = Mage::helper('M2ePro')->__('Add more');

        $customValueRows = '';

        if (empty($specific['template_specific']['value_custom_value'])) {
            $customValues = array('');
        } else {
            $customValues = Mage::helper('M2ePro')->jsonDecode($specific['template_specific']['value_custom_value']);
        }

        $display = 'display: none;';
        $disabled = true;
        if (isset($specific['template_specific']['value_mode']) &&
            $specific['template_specific']['value_mode'] == Specific::VALUE_MODE_CUSTOM_VALUE) {
            $display = '';
            $disabled = false;
        }

        $displayRemoveBtn = 'display: none;';
        if ($specific['max_values'] > 1 && count($customValues) > 1 && count($customValues) < $specific['max_values']) {
            $displayRemoveBtn = '';
        }

        $customIndex = 0;
        foreach ($customValues as $value) {
            /** @var Mage_Adminhtml_Block_Widget_Button $removeCustomValueBtn */
            $removeCustomValueBtn = Mage::getSingleton('core/layout')
                ->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'label' => '',
                        'onclick' => 'EbayTemplateCategorySpecificsObj.removeItemSpecificsCustomValue(this);',
                        'class' => 'scalable delete remove_item_specifics_custom_value_button',
                        'style' => 'padding-bottom:1px; padding-right:0px; padding-left:4px;'
                    )
                );

            $element = new Varien_Data_Form_Element_Text(
                array(
                    'name' => 'specific[dictionary_' . $index . '][value_custom_value][]',
                    'style' => 'width: 99%; padding-left: 2px; padding-right: 0;',
                    'class' => 'M2ePro-required-when-visible item-specific',
                    'value' => $value,
                    'disabled' => $disabled
                )
            );
            $element->setNoSpan(true);
            $element->setForm($this->getForm());
            $element->setId('specific_dictionary_value_custom_value_' . $index . '_' . $customIndex);

            $customValueRows .= <<<HTML
    <tr>
        <td style="border: none; width: 100%; vertical-align:top; text-align: left; padding: 0; ">
            {$element->getHtml()}
        </td>
        <td class="btn_value_remove" style="padding:0; border: none; {$displayRemoveBtn}">
            {$removeCustomValueBtn->toHtml()}
        </td>
    </tr>
HTML;

            $customIndex++;
        }

        $displayAddBtn = 'display: none;';
        if ($specific['max_values'] > 1 && count($customValues) < $specific['max_values']) {
            $displayAddBtn = '';
        }

        $html = <<<HTML
    <div id="specific_dictionary_custom_value_table_{$index}" style="{$display}">
        <table style="border:none; border-spacing: 0 2px">
            <tbody id="specific_dictionary_custom_value_table_body_{$index}" 
            data-min_values="{$specific['min_values']}" 
            data-max_values="{$specific['max_values']}">
                {$customValueRows}
            </tbody>
        </table>
        <a href="javascript: void(0);"
           style="float:right; font-size:11px; {$displayAddBtn}"
           onclick="EbayTemplateCategorySpecificsObj.addItemSpecificsCustomValueRow({$index},this);">
            {$addMoreTxt}
        </a>
    </div>
HTML;

        return $html;
    }

    public function getValueCustomAttributeHtml($index, $specific)
    {
        $attributes = Mage::helper('M2ePro/Magento_Attribute')->getAll();

        foreach ($attributes as &$attribute) {
            $attribute['value'] = $attribute['code'];
            unset($attribute['code']);
        }

        $display = 'display: none;';
        $disabled = true;
        if (isset($specific['template_specific']['value_mode']) &&
            $specific['template_specific']['value_mode'] == Specific::VALUE_MODE_CUSTOM_ATTRIBUTE) {
            $display = '';
            $disabled = false;
        }

        $element = new Varien_Data_Form_Element_Select(
            array(
                'name' => 'specific[dictionary_' . $index . '][value_custom_attribute]',
                'style' => 'width: 100%;' . $display,
                'class' => 'M2ePro-custom-attribute-can-be-created',
                'value' => empty($specific['template_specific']['value_custom_attribute']) ?
                    '' :
                    $specific['template_specific']['value_custom_attribute'],
                'values' => $attributes,
                'apply_to_all_attribute_sets' => 0,
                'disabled' => $disabled
            )
        );

        $element->setNoSpan(true);
        $element->setForm($this->getForm());
        $element->setId('specific_dictionary_value_custom_attribute_' . $index);

        return $element->getHtml();
    }

    //########################################
}
