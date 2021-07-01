<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View_Settings_Grid_Column_Filter_PolicySettings extends
    Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Abstract
{
    //########################################

    public function getHtml()
    {
        $helper = Mage::helper('M2ePro');

        $value = $this->getValue('select');

        $optionsHtml = '';
        foreach ($this->_getOptions() as $option) {
            $optionsHtml .= $this->_renderOption($option, $value);
        }

        $value = $this->getValue();
        $inputValue = '';

        if (is_array($value) && isset($value['input'])) {
            $inputValue = $value['input'];
        } elseif (is_string($value)) {
            $inputValue = $value;
        }

        $html = <<<HTML
<div class="field-100">
    <input type="text" name="{$this->_getHtmlName()}[input]" id="{$this->_getHtmlId()}_input"
           value="{$this->escapeHtml($inputValue)}" class="input-text no-changes"/>
</div>
<div style="padding: 5px 0; text-align: right; font-weight: normal">
    <label>{$helper->__('Overrides')}</label> :
    <select style="width: 125px" name="{$this->_getHtmlName()}[select]" id="{$this->_getHtmlId()}_select">
        {$optionsHtml}
    </select>
</div>

HTML;

        return parent::getHtml() . $html;
    }

    protected function _getOptions()
    {
        return array(
            array(
                'label' => Mage::helper('adminhtml')->__('Any'),
                'value' => ''
            ),
            array(
                'label' => Mage::helper('adminhtml')->__('Policies'),
                'value' => Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE
            ),
            array(
                'label' => Mage::helper('adminhtml')->__('Custom Settings'),
                'value' => Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM
            ),
            array(
                'label' => Mage::helper('adminhtml')->__('No'),
                'value' => Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT
            ),
        );
    }

    protected function _renderOption($option, $value)
    {
        $selected = (($option['value'] == $value && ($value !== null)) ? ' selected="selected"' : '' );
        return '<option value="'. $this->escapeHtml($option['value']).'"'.$selected.'>'
            .$this->escapeHtml($option['label'])
            .'</option>';
    }

    //########################################
}
