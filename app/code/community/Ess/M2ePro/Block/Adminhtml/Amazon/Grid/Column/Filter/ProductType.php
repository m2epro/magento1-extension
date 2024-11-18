<?php

class Ess_M2ePro_Block_Adminhtml_Amazon_Grid_Column_Filter_ProductType extends
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
    <label>{$helper->__('Assigned')}</label> :
    <select style="width: 50px" name="{$this->_getHtmlName()}[select]" id="{$this->_getHtmlId()}_select">
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
                'label' => Mage::helper('adminhtml')->__('Yes'),
                'value' => 1
            ),
            array(
                'label' => Mage::helper('adminhtml')->__('No'),
                'value' => 0
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