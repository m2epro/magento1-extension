<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Helper_Data as Helper;

class Ess_M2ePro_Model_Walmart_Order_Item_Proxy extends Ess_M2ePro_Model_Order_Item_Proxy
{
    //########################################

    /**
     * @return float
     */
    public function getOriginalPrice()
    {
        $price = $this->_item->getPrice()
            + $this->_item->getGiftPrice()
            - $this->_item->getDiscountAmount();

        if ($this->getProxyOrder()->isTaxModeNone() && $this->hasTax()) {
            $price += $this->_item->getTaxAmount();
        }

        return $price;
    }

    /**
     * @return int
     */
    public function getOriginalQty()
    {
        return $this->_item->getQtyPurchased();
    }

    //########################################

    /**
     * @return array|null
     */
    public function getGiftMessage()
    {
        $giftMessage = $this->_item->getGiftMessage();
        if (empty($giftMessage)) {
            return parent::getGiftMessage();
        }

        return array(
            'sender'    => '',
            'recipient' => '',
            'message'   => $this->_item->getGiftMessage()
        );
    }

    //########################################

    /**
     * @return array
     * @throws Exception
     */
    public function getAdditionalData()
    {
        if (empty($this->_additionalData)) {
            $this->_additionalData[Helper::CUSTOM_IDENTIFIER]['pretended_to_be_simple'] = $this->pretendedToBeSimple();
            $this->_additionalData[Helper::CUSTOM_IDENTIFIER]['items'][] = array(
                'order_item_id' => $this->_item->getWalmartOrderItemId()
            );
        }

        return $this->_additionalData;
    }

    //########################################
}
