<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Grid_Column_Filter_Status
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    //########################################

    public function getHtml()
    {
        $duplicateWord = Mage::helper('M2ePro')->__('Duplicates');

        $value = $this->getValue();
        $isChecked = (!empty($value['is_duplicate']) && $value['is_duplicate'] == 1) ? 'checked="checked"' : '';

        return parent::getHtml() . <<<HTML
<div class="range" style="width: 95px;">
    <div class="range-line" style="width: auto; padding-top: 5px;">
        <span>{$duplicateWord}:</span>
        <input style="margin-left:6px; float:none; width:auto !important;"
               type="checkbox" value="1" name="{$this->getColumn()->getId()}[is_duplicate]" {$isChecked}>
    </div>
</div>
HTML;
    }

    //########################################

    public function getValue($index = null)
    {
        if ($index) {
            return $this->getData('value', $index);
        }

        $value = $this->getData('value');
        if ((isset($value['value']) && $value['value'] !== null) ||
            (isset($value['is_duplicate']) && $value['is_duplicate'] == 1)) {
            return $value;
        }

        return null;
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
