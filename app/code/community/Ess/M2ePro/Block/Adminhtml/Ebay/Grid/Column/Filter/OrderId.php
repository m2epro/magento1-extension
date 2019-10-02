<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Grid_Column_Filter_OrderId
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text
{
    //########################################

    public function getHtml()
    {
        if (!Mage::helper('M2ePro/Component_Ebay_PickupStore')->isFeatureEnabled()) {
            return parent::getHtml();
        }

        $isInStorePickup = ($this->getValue('is_in_store_pickup') == 1) ? 'checked="checked"' : '';

        $html = '<div class="field-100"><input type="text" name="'.$this->_getHtmlName().'[value]"
                 id="'.$this->_getHtmlId().'" value="'.$this->getEscapedValue('value').'"
                 class="input-text no-changes"/></div>';

        return $html .
            '<span class="label">' .
                 '</span><input style="margin-left:1px;float:none;width:auto !important;" type="checkbox"
                 value="1" name="' . $this->_getHtmlName() . '[is_in_store_pickup]" ' . $isInStorePickup . '> '
                 .Mage::helper('M2ePro')->__('In-Store Pickup');
    }

    //########################################

    public function getValue($index = null)
    {
        if ($index === null) {
            $value = $this->getData('value');
            return is_array($value) ? $value : array('value' => $value);
        }

        return $this->getData('value', $index);
    }

    public function getEscapedValue($index = null)
    {
        $value = $this->getValue($index);
        if ($index === null) {
            $value = $value['value'];
        }

        return htmlspecialchars($value);
    }

    //########################################
}
