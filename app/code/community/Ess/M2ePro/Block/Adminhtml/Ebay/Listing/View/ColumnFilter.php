<?php


class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View_ColumnFilter extends
    Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Abstract
{
    public function getHtml()
    {
        $value = $this->getValue();
        $inputValue = '';

        if (is_array($value) && isset($value['input'])) {
            $inputValue = $value['input'];
        } elseif (is_string($value)) {
            $inputValue = $value;
        }

        $html = <<<HTML
<div class="field-100">
    <input type="text" name="{$this->_getHtmlName()}" id="{$this->_getHtmlId()}"
           value="{$this->escapeHtml($inputValue)}" class="input-text no-changes"/>
</div>
HTML;

        return parent::getHtml() . $html;
    }

}