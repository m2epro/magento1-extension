<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Grid_Column_Renderer_ItemId
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    //########################################

    public function render(Varien_Object $row)
    {
        $itemId = $this->_getValue($row);

        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if ($itemId === null || $itemId === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $accountId = ($this->getColumn()->getData('account_id')) ? $this->getColumn()->getData('account_id')
                                                                 : $row->getData('account_id');
        $marketplaceId = ($this->getColumn()->getData('marketplace_id')) ? $this->getColumn()->getData('marketplace_id')
                                                                         : $row->getData('marketplace_id');

        $url = $this->getUrl(
            '*/adminhtml_ebay_listing/gotoEbay/',
            array(
                'item_id'        => $itemId,
                'account_id'     => $accountId,
                'marketplace_id' => $marketplaceId
            )
        );

        return '<a href="' . $url . '" target="_blank">' . $itemId . '</a>';
    }

    //########################################
}
