<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Observer_Order_Quote_Address_Collect_Totals_After extends Ess_M2ePro_Observer_Abstract
{
    public function process()
    {
        /** @var Mage_Sales_Model_Quote_Address $address */
        $address = $this->getEvent()->getData('quote_address');

        if ($address->getQuote()->getIsM2eProQuote() && $address->getQuote()->getUseM2eProDiscount()) {
            $discountAmount = $address->getQuote()->getStore()->convertPrice($address->getQuote()->getCoinDiscount());

            if ($address->getTotalAmount('subtotal')) {
                $address->setTotalAmount('subtotal', $address->getTotalAmount('subtotal') - $discountAmount);
            }

            if ($address->getBaseTotalAmount('subtotal')) {
                $address->setTotalAmount('subtotal', $address->getBaseTotalAmount('subtotal') - $discountAmount);
            }

            if ($address->hasData('grand_total') && $address->getGrandTotal()) {
                $address->setGrandTotal($address->getGrandTotal() - $discountAmount);
            }

            if ($address->hasData('base_grand_total') && $address->getBaseGrandTotal()) {
                $address->setBaseGrandTotal($address->getBaseGrandTotal() - $discountAmount);
            }
        }
    }
}