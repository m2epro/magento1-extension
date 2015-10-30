<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Order_Item_Proxy extends Ess_M2ePro_Model_Order_Item_Proxy
{
    //########################################

    public function getOriginalPrice()
    {
        return $this->item->getPrice();
    }

    public function getOriginalQty()
    {
        return $this->item->getQtyPurchased();
    }

    //########################################

    public function getAdditionalData()
    {
        if (count($this->additionalData) == 0) {
            $this->additionalData[Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER]['items'][] = array(
                'order_item_id' => $this->item->getBuyOrderItemId()
            );
        }
        return $this->additionalData;
    }

    //########################################
}