<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Grid_Column_Renderer_CurrentPrice
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Number
{
    //########################################

    public function render(Varien_Object $row)
    {
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        $onlineStartPrice   = $row->getData('online_start_price');
        $onlineCurrentPrice = $row->getData('online_current_price');

        if ($onlineCurrentPrice === null || $onlineCurrentPrice === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((float)$onlineCurrentPrice <= 0) {
            return '<span style="color: #f00;">0</span>';
        }

        $currency = $row->getCurrency();

        if (strpos($currency, ',') !== false) {
            $currency = Mage::helper('M2ePro/Component_Ebay')
                ->getCachedObject('Marketplace', $row->getMarketplaceId())
                ->getChildObject()->getCurrency();
        }

        if (!empty($onlineStartPrice)) {
            $onlineReservePrice  = $row->getData('online_reserve_price');
            $onlineBuyItNowPrice = $row->getData('online_buyitnow_price');

            $onlineStartStr = Mage::app()->getLocale()->currency($currency)->toCurrency($onlineStartPrice);

            $startPriceText = Mage::helper('M2ePro')->__('Start Price');

            $iconHelpPath = $this->getSkinUrl('M2ePro/images/i_logo.png');
            $toolTipIconPath = $this->getSkinUrl('M2ePro/images/i_icon.png');
            $onlineCurrentPriceHtml  = '';
            $onlineReservePriceHtml  = '';
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

            return $resultHtml;
        }

        $noticeHtml = '';
        if ($listingProductId = $row->getData('listing_product_id')) {

            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product', $listingProductId);
            if ($listingProduct->getChildObject()->isVariationsReady()) {
                $iconHelpPath    = $this->getSkinUrl('M2ePro/images/i_logo.png');
                $toolTipIconPath = $this->getSkinUrl('M2ePro/images/i_icon.png');

                $noticeText = Mage::helper('M2ePro')->__(
                    'The value is calculated as minimum price of all Child Products.'
                );

                $noticeHtml = <<<HTML
<img class="tool-tip-image" style="vertical-align: middle;" src="{$toolTipIconPath}">
<span class="tool-tip-message" style="display:none; text-align: left; width: 110px; background: #E3E3E3;">
    <img src="{$iconHelpPath}">
    <span style="color:gray;">
        {$noticeText}
    </span>
</span>
&nbsp;
HTML;
            }
        }

        return $noticeHtml .
            '<span class="product-price-value">' .
            Mage::app()->getLocale()->currency($currency)->toCurrency($onlineCurrentPrice) .
            '</span>';
    }

    //########################################
}
