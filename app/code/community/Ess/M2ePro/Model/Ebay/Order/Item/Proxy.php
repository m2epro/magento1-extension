<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Order_Item_Proxy extends Ess_M2ePro_Model_Order_Item_Proxy
{
    //########################################

    /**
     * @return float
     */
    public function getOriginalPrice()
    {
        $price = $this->item->getPrice();

        if (($this->getProxyOrder()->isTaxModeNone() && $this->hasTax()) || $this->isVatTax()) {
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
     * @return float
     */
    public function getTaxRate()
    {
        return $this->item->getTaxRate();
    }

    //########################################

    /**
     * @return array
     */
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

    //########################################
}