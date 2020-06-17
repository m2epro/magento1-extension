<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Grid_Column_Renderer_Sku
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    //########################################

    public function render(Varien_Object $row)
    {
        $value = $this->_getValue($row);

        if ($value === null || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        }

        $showEditSku = ($this->getColumn()->getData('show_edit_sku') !== null)
                       ? $this->getColumn()->getData('show_edit_sku')
                       : true;

        if (!$showEditSku) {
            return $value;
        }

        $productId = $row->getData('id');

        $isVariationParent = $row->getData('is_variation_parent');

        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED && !$isVariationParent) {
            $value = <<<HTML
<div class="walmart-sku">
    {$value}&nbsp;&nbsp;
    <a href="#" class="walmart-sku-edit"
       onclick="ListingGridObj.editChannelDataHandler.showEditSkuPopup({$productId})">(edit)</a>
</div>
HTML;
        }

        if (!$isVariationParent && $row->getData('is_online_price_invalid')) {

            $message = <<<HTML
Item Price violates Walmart pricing rules. Please adjust the Item Price to comply with the Walmart requirements.<br>
Once the changes are applied, Walmart Item will become Active automatically.
HTML;
            $msg = '<p>'.Mage::helper('M2ePro')->__($message).'</p>';
            if (empty($msg)) {
                return $value;
            }

            $value .= <<<HTML
<div style="float:right; width: 16px">
    <img id="map_link_defected_message_icon_{$row->getId()}"
         class="tool-tip-image"
         style="vertical-align: middle;"
         src="{$this->getSkinUrl('M2ePro/images/warning.png')}">
    <span class="tool-tip-message tool-tip-warning tip-left" style="display:none; max-width: 400px;">
        <img src="{$this->getSkinUrl('M2ePro/images/i_notice.gif')}">
        <span>{$msg}</span>
    </span>
</div>
HTML;
        }

        return $value;
    }

    //########################################
}
