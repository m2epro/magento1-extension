<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Order_Item_Proxy extends Ess_M2ePro_Model_Order_Item_Proxy
{
    //########################################

    /**
     * @return float
     */
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

    /**
     * @return int
     */
    public function getOriginalQty()
    {
        return $this->item->getQtyPurchased();
    }

    //########################################

    /**
     * @return array|null
     */
    public function getGiftMessage()
    {
        $giftMessage = $this->item->getGiftMessage();
        if (empty($giftMessage)) {
            return parent::getGiftMessage();
        }

        return array(
            'sender'    => '',
            'recipient' => '',
            'message'   => $this->item->getGiftMessage()
        );
    }

    //########################################

    /**
     * @return array
     */
    public function getAdditionalData()
    {
        if (count($this->additionalData) == 0) {
            $this->additionalData[Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER]['items'][] = array(
                'order_item_id' => $this->item->getAmazonOrderItemId()
            );
        }
        return $this->additionalData;
    }

    //########################################
}