<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Order_Item_Proxy extends Ess_M2ePro_Model_Order_Item_Proxy
{
    // ########################################

    public function getOriginalPrice()
    {
        $price = $this->item->getPrice();

        if (($this->getProxyOrder()->isTaxModeNone() && $this->hasTax()) || $this->isVatTax()) {
            $price += $this->item->getTaxAmount();
        }

        return $price;
    }

    public function getOriginalQty()
    {
        return $this->item->getQtyPurchased();
    }

    // ########################################

    public function getTaxRate()
    {
        return $this->item->getTaxRate();
    }

    // ########################################

    public function getAdditionalData()
    {
        if (count($this->additionalData) == 0) {
            $this->additionalData[Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER]['items'][] = array(
                'item_id' => $this->item->getItemId(),
                'transaction_id' => $this->item->getTransactionId()
            );
        }
        return $this->additionalData;
    }

    // ########################################
}