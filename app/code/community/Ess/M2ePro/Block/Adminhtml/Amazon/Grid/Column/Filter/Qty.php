<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Listing_Product as AmazonListingProduct;

class Ess_M2ePro_Block_Adminhtml_Amazon_Grid_Column_Filter_Qty
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Range
{
    //########################################

    public function getHtml()
    {
        $anySelected = $noSelected = $yesSelected = '';
        $filterValue = (string)$this->getValue('afn');

        $filterValue === ''  && $anySelected = ' selected="selected" ';
        $filterValue === '0' && $noSelected  = ' selected="selected" ';
        $filterValue === '1' && $yesSelected = ' selected="selected" ';

        $isEnabled  = AmazonListingProduct::IS_AFN_CHANNEL_YES;
        $isDisabled = AmazonListingProduct::IS_AFN_CHANNEL_NO;

        $helper = Mage::helper('M2ePro');

        $html = <<<HTML
<div class="range" style="width: 135px;">
    <div class="range-line" style="width: auto;">
        <span class="label" style="width: auto;">
            {$helper->__('Fulfillment')}:&nbsp;
        </span>
        <select id="{$this->_getHtmlName()}"
                style="margin-left:6px; float:none; width:auto !important;"
                name="{$this->_getHtmlName()}[afn]"
            >
            <option {$anySelected} value="">{$helper->__('Any')}</option>
            <option {$noSelected}  value="{$isDisabled}">{$helper->__('MFN')}</option>
            <option {$yesSelected} value="{$isEnabled}">{$helper->__('AFN')}</option>
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
        if ((isset($value['from']) && $value['from'] !== '') ||
            (isset($value['to']) && $value['to'] !== '') ||
            (isset($value['afn']) && $value['afn'] !== '')
        ) {
            return $value;
        }

        return null;
    }

    //########################################
}
