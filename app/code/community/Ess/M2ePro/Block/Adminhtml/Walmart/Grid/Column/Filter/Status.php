<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Grid_Column_Filter_Status
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    //########################################

    public function getHtml()
    {
        $value = $this->getValue();
        $isResetChecked = !empty($value['is_reset']) ? 'checked="checked"' : '';

        $helper = Mage::helper('M2ePro');

        $html = <<<HTML
<div class="range" style="width: 95px;">
    <div class="range-line" style="width: auto; padding-top: 5px;">
        <span>{$helper->__('Can be fixed')}:</span>
        <input style="margin-left:6px; float:none; width:auto !important;"
               type="checkbox" value="1" name="{$this->getColumn()->getId()}[is_reset]" {$isResetChecked}>
    </div>
</div>
HTML;

        return parent::getHtml() . $html;
    }

    //########################################

    public function getValue()
    {
        $value = $this->getData('value');

        if (is_array($value) &&
            (isset($value['value']) && !is_null($value['value'])) ||
            (isset($value['is_reset']) && $value['is_reset'] == 1))
        {
            return $value;
        }

        return NULL;
    }

    //########################################

    protected function _renderOption($option, $value)
    {
        $value = isset($value['value']) ? $value['value'] : null;
        return parent::_renderOption($option, $value);
    }

    protected function _getHtmlName()
    {
        return "{$this->getColumn()->getId()}[value]";
    }

    //########################################
}