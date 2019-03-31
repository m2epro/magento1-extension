<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_PickupStore_QtyCalculator
    extends Ess_M2ePro_Model_Ebay_Listing_Product_QtyCalculator
{
    //########################################

    public function getLocationProductValue(Ess_M2ePro_Model_Ebay_Account_PickupStore $accountPickupStore,
                                            $bufferedClearValue = NULL)
    {
        if (!$accountPickupStore->isQtyModeSellingFormatTemplate()) {
            $this->source = $accountPickupStore->getQtySource();
        }

        if (!is_null($bufferedClearValue)) {
            $value = $bufferedClearValue;
        } else {
            $value = $this->getClearLocationProductValue($accountPickupStore);
        }

        $value = $this->applySellingFormatTemplateModifications($value);
        $value < 0 && $value = 0;

        return $value;
    }

    public function getLocationVariationValue(Ess_M2ePro_Model_Listing_Product_Variation $variation,
                                              Ess_M2ePro_Model_Ebay_Account_PickupStore $accountPickupStore,
                                              $bufferedClearValue = NULL)
    {
        if (!$accountPickupStore->isQtyModeSellingFormatTemplate()) {
            $this->source = $accountPickupStore->getQtySource();
        }

        if (!is_null($bufferedClearValue)) {
            $value = $bufferedClearValue;
        } else {
            $value = $this->getClearLocationVariationValue($variation, $accountPickupStore);
        }

        $value = $this->applySellingFormatTemplateModifications($value);
        $value < 0 && $value = 0;

        return $value;
    }

    //########################################

    public function getClearLocationProductValue(Ess_M2ePro_Model_Ebay_Account_PickupStore $accountPickupStore)
    {
        if (!$accountPickupStore->isQtyModeSellingFormatTemplate()) {
            $this->source = $accountPickupStore->getQtySource();
        }

        return $this->getClearProductValue();
    }

    public function getClearLocationVariationValue(Ess_M2ePro_Model_Listing_Product_Variation $variation,
                                                   Ess_M2ePro_Model_Ebay_Account_PickupStore $accountPickupStore)
    {
        if (!$accountPickupStore->isQtyModeSellingFormatTemplate()) {
            $this->source = $accountPickupStore->getQtySource();
        }

        return $this->getClearVariationValue($variation);
    }

    //########################################
}