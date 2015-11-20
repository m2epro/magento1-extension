<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Grid_Column_Filter_Qty
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Range
{
    //########################################

    public function getHtml()
    {
        $afnChecked = ($this->getValue('afn') == 1) ? 'checked="checked"' : '';

        return parent::getHtml() .
            '<div class="range"><div class="range-line"><span class="label">' .
                Mage::helper('M2ePro')->__('AFN') . ': </span>' .
                '<input style="margin-left:6px;float:none;width:auto !important;" type="checkbox" value="1" name="' .
                $this->_getHtmlName() . '[afn]" ' . $afnChecked . '></div></div>';
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
            (isset($value['afn']) && $value['afn'] == 1)) {
            return $value;
        }
        return null;
    }

    //########################################
}
