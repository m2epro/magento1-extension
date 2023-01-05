<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Grid_Column_Renderer_Qty
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Number
{
    //########################################

    public function render(Varien_Object $row)
    {
        $value = $this->_getValue($row);

        if (!$row->getData('is_variation_parent')) {
            if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
                return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
            }

            if ($value === null || $value === '') {
                if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED &&
                    !$row->getData('is_online_price_invalid')) {
                    return Mage::helper('M2ePro')->__('N/A');
                }
                else {
                    return '<i style="color:gray;">receiving...</i>';
                }
            }

            if ($value <= 0) {
                return '<span style="color: red;">0</span>';
            }

            return $value;
        }

        $variationChildStatuses = Mage::helper('M2ePro')->jsonDecode($row->getData('variation_child_statuses'));

        if (empty($variationChildStatuses) || $value === null || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $activeChildrenCount = 0;
        foreach ($variationChildStatuses as $childStatus => $count) {
            if ($childStatus == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
                continue;
            }

            $activeChildrenCount += (int)$count;
        }

        if ($activeChildrenCount == 0) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ($value <= 0) {
            return '<span style="color: red;">0</span>';
        }

        return $value;
    }

    //########################################
}
