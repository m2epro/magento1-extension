<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Grid_Column_Renderer_MinMaxPrice
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Number
{
    //########################################

    public function render(Varien_Object $row)
    {
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }
        $currency = $this->getColumn()->getData('currency');
        $onlineMinPrice = $row->getData('min_online_price');
        $onlineMaxPrice = $row->getData('max_online_price');
        $onlineStartPrice = $row->getData('online_start_price');
        $onlineCurrentPrice = $row->getData('online_current_price');

        if ($onlineMinPrice === null || $onlineMinPrice === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((float)$onlineMinPrice <= 0) {
            return '<span style="color: #f00;">0</span>';
        }

        if (!empty($onlineStartPrice)) {
            $onlineReservePrice = $row->getData('online_reserve_price');
            $onlineBuyItNowPrice = $row->getData('online_buyitnow_price');

            $onlineStartStr = Mage::app()->getLocale()->currency($currency)->toCurrency($onlineStartPrice);

            $startPriceText = Mage::helper('M2ePro')->__('Start Price');

            $iconHelpPath = $this->getSkinUrl('M2ePro/images/i_logo.png');
            $toolTipIconPath = $this->getSkinUrl('M2ePro/images/i_icon.png');
            $onlineCurrentPriceHtml = '';
            $onlineReservePriceHtml = '';
            $onlineBuyItNowPriceHtml = '';

            if ($row->getData('online_bids') > 0 || $onlineCurrentPrice > $onlineStartPrice) {
                $currentPriceText = Mage::helper('M2ePro')->__('Current Price');
                $onlineCurrentStr = Mage::app()->getLocale()->currency($currency)->toCurrency($onlineCurrentPrice);
                $onlineCurrentPriceHtml = '<strong>'.$currentPriceText.':</strong> '.$onlineCurrentStr.'<br/><br/>';
            }

            if ($onlineReservePrice > 0) {
                $reservePriceText = Mage::helper('M2ePro')->__('Reserve Price');
                $onlineReserveStr = Mage::app()->getLocale()->currency($currency)->toCurrency($onlineReservePrice);
                $onlineReservePriceHtml = '<strong>'.$reservePriceText.':</strong> '.$onlineReserveStr.'<br/>';
            }

            if ($onlineBuyItNowPrice > 0) {
                $buyItNowText = Mage::helper('M2ePro')->__('Buy It Now Price');
                $onlineBuyItNowStr = Mage::app()->getLocale()->currency($currency)->toCurrency($onlineBuyItNowPrice);
                $onlineBuyItNowPriceHtml = '<strong>'.$buyItNowText.':</strong> '.$onlineBuyItNowStr;
            }

            $intervalHtml = <<<HTML
<img class="tool-tip-image"
     style="vertical-align: middle;"
     src="{$toolTipIconPath}"><span class="tool-tip-message" style="display:none; text-align: left; min-width: 140px;">
    <img src="{$iconHelpPath}"><span style="color:gray;">
        {$onlineCurrentPriceHtml}
        <strong>{$startPriceText}:</strong> {$onlineStartStr}<br/>
        {$onlineReservePriceHtml}
        {$onlineBuyItNowPriceHtml}
    </span>
</span>
HTML;

            if ($onlineCurrentPrice > $onlineStartPrice) {
                $resultHtml = '<span style="color: grey; text-decoration: line-through;">'.$onlineStartStr.'</span>';
                $resultHtml .= '<br/>'.$intervalHtml.'&nbsp;'.
                    '<span class="product-price-value">'.$onlineCurrentStr.'</span>';
            } else {
                $resultHtml = $intervalHtml.'&nbsp;'.'<span class="product-price-value">'.$onlineStartStr.'</span>';
            }
        } else {
            $onlineMinPriceStr = Mage::app()->getLocale()->currency($currency)->toCurrency($onlineMinPrice);
            $onlineMaxPriceStr = Mage::app()->getLocale()->currency($currency)->toCurrency($onlineMaxPrice);

            $resultHtml = '<span class="product-price-value">' . $onlineMinPriceStr . '</span>' .
                (($onlineMinPrice != $onlineMaxPrice) ? ' - ' . $onlineMaxPriceStr :  '');
        }

        $listingProductId = (int)$row->getData('listing_product_id');
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product', $listingProductId);
        $onlineBids = $listingProduct->getChildObject()->getOnlineBids();

        if ($onlineBids) {
            $title = $row->getName();

            $onlineTitle = $row->getData('online_title');
            !empty($onlineTitle) && $title = $onlineTitle;

            $title = Mage::helper('M2ePro')->escapeHtml($title);

            $bidsPopupTitle = Mage::helper('M2ePro')->__('Bids of &quot;%s&quot;', $title);
            $bidsPopupTitle = addslashes($bidsPopupTitle);

            $bidsTitle = Mage::helper('M2ePro')->__('Show bids list');
            $bidsText = Mage::helper('M2ePro')->__('Bid(s)');

            if ($listingProduct->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE) {
                $resultHtml .= '<br/><br/><span style="font-size: 10px; color: gray;">' .
                    $onlineBids. ' ' . $bidsText . '</span>';
            } else {
                $resultHtml .= <<<HTML
<br/>
<br/>
<a class="m2ePro-ebay-auction-bids-link"
    href="javascript:void(0)"
    title="{$bidsTitle}"
    onclick="EbayListingEbayGridObj
        .listingProductBids.openPopUp({$listingProductId},'{$bidsPopupTitle}')"
>{$onlineBids} {$bidsText}</a>
HTML;
            }
        }

        return $resultHtml;
    }

    //########################################
}
