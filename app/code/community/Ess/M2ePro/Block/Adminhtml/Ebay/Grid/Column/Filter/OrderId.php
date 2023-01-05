<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Grid_Column_Filter_OrderId
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text
{
    public function getValue($index = null)
    {
        if ($index === null) {
            $value = $this->getData('value');
            return is_array($value) ? $value : array('value' => $value);
        }

        return $this->getData('value', $index);
    }

    public function getEscapedValue($index = null, $flag = ENT_COMPAT)
    {
        $value = $this->getValue($index);
        if ($index === null) {
            $value = $value['value'];
        }

        return htmlspecialchars($value, $flag, 'UTF-8');
    }
}
