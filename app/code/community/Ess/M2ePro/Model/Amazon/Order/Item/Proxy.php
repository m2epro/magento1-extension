<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Order_Item_Proxy extends Ess_M2ePro_Model_Order_Item_Proxy
{
    // ########################################

    public function getOriginalPrice()
    {
        $price = $this->item->getPrice()
            + $this->item->getGiftPrice()
            - $this->item->getDiscountAmount();

        if ($this->getProxyOrder()->isTaxModeNone() && $this->hasTax()) {
            $price += $this->item->getTaxAmount();
        }

        return $price;
    }

    public function getOriginalQty()
    {
        return $this->item->getQtyPurchased();
    }

    // ########################################

    public function getGiftMessage()
    {
        $giftMessage = $this->item->getGiftMessage();
        if (empty($giftMessage)) {
            return parent::getGiftMessage();
        }

        return array(
            'sender'    => '', //$this->item->getAmazonOrder()->getBuyerName(),
            'recipient' => '', //$this->item->getAmazonOrder()->getShippingAddress()->getData('recipient_name'),
            'message'   => $this->item->getGiftMessage()
        );
    }

    // ########################################

    public function getAdditionalData()
    {
        if (count($this->additionalData) == 0) {
            $this->additionalData[Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER]['items'][] = array(
                'order_item_id' => $this->item->getAmazonOrderItemId()
            );
        }
        return $this->additionalData;
    }

    // ########################################
}