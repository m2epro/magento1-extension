<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_List_Validator_General
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Validator
{
    // ########################################

    public function validate()
    {
        if (!$this->getListingProduct()->isListable()) {

            // M2ePro_TRANSLATIONS
            // Item is already on Rakuten.com, or not available.
            $this->addMessage('Item is already on Rakuten.com, or not available.');

            return false;
        }

        if ($this->getVariationManager()->isVariationProduct() && !$this->validateVariationProductMatching()) {
            return false;
        }

        if (!$this->validateQty()) {
            return false;
        }

        if (!$this->validatePrice()) {
            return false;
        }

        return true;
    }

    // ########################################
}