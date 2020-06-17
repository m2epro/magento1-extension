<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Grid_Column_Renderer_Qty
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Number
{
    const ONLINE_QTY_SOLD      = 'online_qty_sold';
    const ONLINE_AVAILABLE_QTY = 'online_available_qty';

    //########################################

    public function render(Varien_Object $row)
    {
        $value = $this->_getValue($row);
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if ($value === null || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ($value <= 0) {
            return '<span style="color: red;">0</span>';
        }

        $renderOnlineQty = ($this->getColumn()->getData('render_online_qty'))
                           ? $this->getColumn()->getData('render_online_qty')
                           : self::ONLINE_QTY_SOLD;

        if ($renderOnlineQty === self::ONLINE_AVAILABLE_QTY) {
            if ($row->getData('status') != Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                return '<span style="color: gray; text-decoration: line-through;">' . $value . '</span>';
            }
        }

        return $value;
    }

    //########################################
}
