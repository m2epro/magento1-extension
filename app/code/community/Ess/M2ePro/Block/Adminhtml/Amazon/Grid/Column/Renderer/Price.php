<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Grid_Column_Renderer_Price
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    //########################################

    public function render(Varien_Object $row)
    {
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $onlineRegularPrice  = $row->getData('online_regular_price');
        $onlineBusinessPrice = $row->getData('online_business_price');

        $repricingHtml ='';

        if ($row->getData('is_repricing')) {
            $image = 'money';
            $text = Mage::helper('M2ePro')->__(
                'This Product is used by Amazon Repricing Tool, so its Price cannot be managed via M2E Pro. <br>
                 <strong>Please note</strong> that the Price value(s) shown in the grid might
                 be different from the actual one from Amazon. It is caused by the delay
                 in the values updating made via the Repricing Service'
            );

            if ((int)$row->getData('is_repricing_disabled') == 1 || (int)$row->getData('is_repricing_inactive') == 1) {
                $image = 'money_disabled';
                $text = Mage::helper('M2ePro')->__(
                    'This Item is disabled or unable to be repriced on Amazon Repricing Tool.
                     Its Price is updated via M2E Pro.'
                );
            }

            $repricingHtml = <<<HTML
<span style="float:right; text-align: left;">&nbsp;
    <img class="tool-tip-image"
         style="vertical-align: middle; width: 16px;"
         src="{$this->getSkinUrl('M2ePro/images/'.$image.'.png')}">
    <span class="tool-tip-message tool-tip-message tip-left" style="display:none;">
        <img src="{$this->getSkinUrl('M2ePro/images/i_icon.png')}">
        <span>{$text}</span>
    </span>
</span>
HTML;
        }

        if (($onlineRegularPrice === null || $onlineRegularPrice === '') &&
            ($onlineBusinessPrice === null || $onlineBusinessPrice === '')
        ) {
            return '<i style="color:gray;">receiving...</i>' . $repricingHtml;
        }

        $currency = Mage::helper('M2ePro/Component_Amazon')
            ->getCachedObject('Marketplace', $this->getColumn()->getData('marketplace_id'))
            ->getChildObject()
            ->getDefaultCurrency();

        if ((float)$onlineRegularPrice <= 0) {
            $priceValue = '<span style="color: #f00;">0</span>';
        } else {
            $priceValue = Mage::app()->getLocale()->currency($currency)->toCurrency($onlineRegularPrice);
        }

        if ($row->getData('is_repricing') &&
            !$row->getData('is_repricing_disabled') &&
            !$row->getData('is_repricing_inactive')) {
            $accountId = $this->getColumn()->getData('account_id');
            $sku = $row->getData('amazon_sku');

            $priceValue =<<<HTML
<a id="m2epro_repricing_price_value_{$sku}"
   class="m2epro-repricing-price-value"
   sku="{$sku}"
   account_id="{$accountId}"
   href="javascript:void(0)"
   onclick="AmazonListingRepricingPriceObj.showRepricingPrice()">
    {$priceValue}</a>
HTML;
        }

        $resultHtml = '';

        $salePrice = $row->getData('online_regular_sale_price');
        if ((float)$salePrice > 0) {
            /** @var Ess_M2ePro_Helper_Data $helper */
            $helper = Mage::helper('M2ePro');
            $currentTimestamp = (int)$helper->createGmtDateTime(
                $helper->getCurrentGmtDate(false, 'Y-m-d 00:00:00')
            )->format('U');

            $startDateTimestamp = (int)$helper->createGmtDateTime($row->getData('online_regular_sale_price_start_date'))
                ->format('U');
            $endDateTimestamp = (int)$helper->createGmtDateTime($row->getData('online_regular_sale_price_end_date'))
                ->format('U');

            if ($currentTimestamp <= $endDateTimestamp) {
                $iconHelpPath = $this->getSkinUrl('M2ePro/images/i_logo.png');
                $toolTipIconPath = $this->getSkinUrl('M2ePro/images/i_icon.png');

                $dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

                $fromDate = Mage::app()->getLocale()->date(
                    $row->getData('online_regular_sale_price_start_date'), $dateFormat
                )->toString($dateFormat);
                $toDate = Mage::app()->getLocale()->date(
                    $row->getData('online_regular_sale_price_end_date'), $dateFormat
                )->toString($dateFormat);

                $intervalHtml = '<span><img class="tool-tip-image"
                                 style="vertical-align: middle;"
                                 src="'.$toolTipIconPath.'"><span class="tool-tip-message" style="display:none;
                                                                  text-align: left;
                                                                  width: 120px;
                                                                  background: #E3E3E3;">
                                <img src="'.$iconHelpPath.'">
                                <span style="color:gray;">
                                    <strong>From:</strong> '.$fromDate.'<br/>
                                    <strong>To:</strong> '.$toDate.'
                                </span>
                            </span></span>';

                $salePriceValue = Mage::app()->getLocale()->currency($currency)->toCurrency($salePrice);

                if ($currentTimestamp >= $startDateTimestamp &&
                    $currentTimestamp <= $endDateTimestamp &&
                    $salePrice < (float)$onlineRegularPrice
                ) {
                    $resultHtml .= '<span style="color: grey; text-decoration: line-through;">'.$priceValue.'</span>' .
                        $repricingHtml;
                    $resultHtml .= '<br/>'.$intervalHtml.'&nbsp;'.$salePriceValue;
                } else {
                    $resultHtml .= $priceValue . $repricingHtml;
                    $resultHtml .= '<br/>'.$intervalHtml.
                        '<span style="color:gray;">'.'&nbsp;'.$salePriceValue.'</span>';
                }
            }
        }

        if (empty($resultHtml)) {
            $resultHtml = $priceValue . $repricingHtml;
        }

        if ((float)$onlineBusinessPrice > 0) {
            $businessPriceValue = '<strong>B2B:</strong> '
                .Mage::app()->getLocale()->currency($currency)->toCurrency($onlineBusinessPrice);

            $businessDiscounts = $row->getData('online_business_discounts');
            if (!empty($businessDiscounts) && $businessDiscounts = json_decode($businessDiscounts, true)) {
                $iconHelpPath = $this->getSkinUrl('M2ePro/images/i_logo.png');
                $toolTipIconPath = $this->getSkinUrl('M2ePro/images/i_icon.png');

                $discountsHtml = '';

                foreach ($businessDiscounts as $qty => $price) {
                    $price = Mage::app()->getLocale()->currency($currency)->toCurrency($price);
                    $discountsHtml .= 'QTY >= '.(int)$qty.', price '.$price.'<br />';
                }

                $discountsHtml = ' <span><img class="tool-tip-image"
                                 style="vertical-align: middle;"
                                 src="'.$toolTipIconPath.'"><span class="tool-tip-message tip-left" style="display:none;
                                                                  text-align: left;
                                                                  width: 150px;
                                                                  background: #E3E3E3;">
                                <img src="'.$iconHelpPath.'">
                                <span style="color:gray;">
                                    '.$discountsHtml.'
                                </span>
                            </span></span>';

                $businessPriceValue .= $discountsHtml;
            }

            if (!empty($resultHtml)) {
                $businessPriceValue = '<br />'.$businessPriceValue;
            }

            $resultHtml .= $businessPriceValue;
        }

        return $resultHtml;
    }

    //########################################
}
