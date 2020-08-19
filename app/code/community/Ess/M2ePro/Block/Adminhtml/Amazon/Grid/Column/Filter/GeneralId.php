<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Grid_Column_Filter_GeneralId extends
    Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Abstract
{
    //########################################

    protected function _renderOption($option, $value)
    {
        $selected = (($option['value'] == $value && ($value !== null)) ? ' selected="selected"' : '' );
        return '<option value="'. $this->escapeHtml($option['value']).'"'.$selected.'>'
            .$this->escapeHtml($option['label'])
            .'</option>';
    }

    public function getHtml()
    {
        $helper = Mage::helper('M2ePro');

        $value = $this->getValue('select');

        $optionsHtml = '';
        foreach ($this->_getOptions() as $option) {
            $optionsHtml .= $this->_renderOption($option, $value);
        }

        $html = <<<HTML
<div class="field-100">
    <input type="text" name="{$this->_getHtmlName()}[input]" id="{$this->_getHtmlId()}_input"
           value="{$this->getEscapedValue('input')}" class="input-text no-changes"/>
</div>
<div style="padding: 5px 0; width: auto; font-weight: bold; margin-top: 20px;">
    <label>{$helper->__('ASIN Creator')}</label>:
    <select style="margin-left:6px; float:none; width:auto !important;" name="{$this->_getHtmlName()}[select]" id="{$this->_getHtmlId()}_select">
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
                'label' => Mage::helper('M2ePro')->__('Any'),
                'value' => ''
            ),
            array(
                'label' => Mage::helper('M2ePro')->__('Yes'),
                'value' => Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES
            ),
            array(
                'label' => Mage::helper('M2ePro')->__('No'),
                'value' => Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_NO
            ),
        );
    }

    //########################################
}
