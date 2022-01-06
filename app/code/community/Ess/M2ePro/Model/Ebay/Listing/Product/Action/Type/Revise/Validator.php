<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Revise_Validator
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Validator
{
    //########################################

    public function validate()
    {
        if (!$this->getListingProduct()->isRevisable()) {
            $this->addMessage('Item is not Listed or not available');

            return false;
        }

        $ebayItemIdReal = $this->getEbayListingProduct()->getEbayItemIdReal();
        if (empty($ebayItemIdReal)) {
            return false;
        }

        if (!$this->validateIsVariationProductWithoutVariations()) {
            return false;
        }

        if ($this->getEbayListingProduct()->isVariationsReady()) {
            if (!$this->validateVariationsOptions()) {
                return false;
            }
        }

        if (!$this->validateCategory()) {
            return false;
        }

        if (!$this->validatePrice()) {
            return false;
        }

        if (!$this->getEbayListingProduct()->isOutOfStockControlEnabled() && !$this->validateQty()) {
            return false;
        }

        return true;
    }

    //########################################
}
