<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Grid_Column_Renderer_Qty
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Number
{
    //########################################

    public function render(Varien_Object $row)
    {
        $value = $this->_getValue($row);

        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $listingProductId = $row->getData('id');


        if (!$row->getData('is_variation_parent')) {
            if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
                return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
            }

            if ((bool)$row->getData('is_afn_channel')) {
                $sku = $row->getData('sku');

                if (empty($sku)) {
                    return Mage::helper('M2ePro')->__('AFN');
                }

                /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
                $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject(
                    'Listing_Product', $listingProductId
                );

                $afn = Mage::helper('M2ePro')->__('AFN');
                $total = Mage::helper('M2ePro')->__('Total');
                $inStock = Mage::helper('M2ePro')->__('In Stock');
                $accountId = $listingProduct->getListing()->getAccountId();

                return <<<HTML
<div id="m2ePro_afn_qty_value_{$listingProductId}">
    <span class="m2ePro-online-sku-value" productId="{$listingProductId}" style="display: none">{$sku}</span>
    <span class="m2epro-empty-afn-qty-data" style="display: none">{$afn}</span>
    <div class="m2epro-afn-qty-data" style="display: none">
        <div class="total">{$total}: <span></span></div>
        <div class="in-stock">{$inStock}: <span></span></div>
    </div>
    <a href="javascript:void(0)"
        onclick="AmazonListingAfnQtyObj.showAfnQty(this,'{$sku}',{$listingProductId}, {$accountId})">
        {$afn}</a>
</div>
HTML;
            }

            $showReceiving = ($this->getColumn()->getData('show_receiving') !== null)
                              ? $this->getColumn()->getData('show_receiving')
                              : true;

            if ($value === null || $value === '') {
                if ($showReceiving) {
                    return '<i style="color:gray;">receiving...</i>';
                } else {
                    return Mage::helper('M2ePro')->__('N/A');
                }
            }

            if ($value <= 0) {
                return '<span style="color: red;">0</span>';
            }

            return $value;
        }

        if ($row->getData('general_id') == '') {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        $variationChildStatuses = Mage::helper('M2ePro')->jsonDecode($row->getData('variation_child_statuses'));

        if (empty($variationChildStatuses)) {
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

        if (!(bool)$row->getData('is_afn_channel')) {
            if ($value <= 0) {
                return '<span style="color: red;">0</span>';
            }

            return $value;
        }

        $resultValue = Mage::helper('M2ePro')->__('AFN');
        $additionalData = (array)Mage::helper('M2ePro')->jsonDecode($row->getData('additional_data'));

        $filter = base64_encode('online_qty[afn]=1');

        $productTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('name'));
        $vpmt = Mage::helper('M2ePro')->__('Manage Variations of &quot;%s&quot; ', $productTitle);
        $vpmt = addslashes($vpmt);

        $linkTitle = Mage::helper('M2ePro')->__('Show AFN Child Products.');
        $afnCountWord = !empty($additionalData['afn_count']) ? $additionalData['afn_count']
            : Mage::helper('M2ePro')->__('show');

        $resultValue = $resultValue."&nbsp;<a href=\"javascript:void(0)\"
                           class=\"hover-underline\"
                           title=\"{$linkTitle}\"
                           onclick=\"ListingGridObj.variationProductManageHandler.openPopUp(
                            {$listingProductId}, '{$vpmt}', '{$filter}'
                        )\">[".$afnCountWord."]</a>";

        return <<<HTML
<div>{$value}</div>
<div>{$resultValue}</div>
HTML;
    }

    //########################################
}
