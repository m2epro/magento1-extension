<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Grid_Column_Filter_ProductId
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Range
{
    //########################################

    public function getHtml()
    {
        $anySelected = $noSelected = $yesSelected = '';
        $filterValue = (string)$this->getValue('is_mapped');

        $filterValue === ''  && $anySelected = ' selected="selected" ';
        $filterValue === '0' && $noSelected  = ' selected="selected" ';
        $filterValue === '1' && $yesSelected = ' selected="selected" ';

        $isEnabled  = 1;
        $isDisabled = 0;

        $helper = Mage::helper('M2ePro');

        $html = <<<HTML
<div class="range" style="width: 145px;">
    <div class="range-line" style="width: auto;">
        <span class="label" style="width: auto;">
            {$helper->__('Mapped')}:&nbsp;
        </span>
        <select id="{$this->_getHtmlName()}"
                style="margin-left:6px; float:none; width:auto !important;"
                name="{$this->_getHtmlName()}[is_mapped]"
            >
            <option {$anySelected} value="">{$helper->__('Any')}</option>
            <option {$yesSelected} value="{$isEnabled}">{$helper->__('Yes')}</option>
            <option {$noSelected}  value="{$isDisabled}">{$helper->__('No')}</option>
        </select>
    </div>
</div>
HTML;

        return parent::getHtml() . $html;
    }

    //########################################

    public function getValue($index=null)
    {
        if ($index) {
            return $this->getData('value', $index);
        }

        $value = $this->getData('value');
        if ((isset($value['from']) && strlen($value['from']) > 0) ||
            (isset($value['to']) && strlen($value['to']) > 0) ||
            (isset($value['is_mapped']) && $value['is_mapped'] !== '')) {
            return $value;
        }

        return null;
    }

    //########################################
}
