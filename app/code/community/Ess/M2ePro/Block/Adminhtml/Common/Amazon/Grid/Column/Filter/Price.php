<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Grid_Column_Filter_Price
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Range
{
    //########################################

    public function getHtml()
    {
        $afnChecked = ($this->getValue('is_repricing') == 1) ? 'checked="checked"' : '';

        return parent::getHtml() .
            '<div class="range"><div class="range-line" style="width: auto;"><span class="label" style="width: auto;">'.
                Mage::helper('M2ePro')->__('Repricing') . ': </span>' .
                '<input style="margin-left:6px;float:none;width:auto !important;" type="checkbox" value="1" name="'.
                $this->_getHtmlName() . '[is_repricing]" ' . $afnChecked . '></div></div>';
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
            (isset($value['is_repricing']) && $value['is_repricing'] == 1)) {
            return $value;
        }
        return null;
    }

    //########################################
}
