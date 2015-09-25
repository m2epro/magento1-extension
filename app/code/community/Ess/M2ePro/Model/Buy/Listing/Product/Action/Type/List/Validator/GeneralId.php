<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_List_Validator_GeneralId
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Validator
{
    // ########################################

    public function validate()
    {
        $generalId = $this->getBuyListingProduct()->getGeneralId();
        if (empty($generalId)) {
            $generalId = $this->getBuyListingProduct()->getListingSource()->getSearchGeneralId();

            if (!empty($generalId)) {
                $this->data['general_id_mode'] = $this->getBuyListing()->getGeneralIdMode();
            }
        }

        // M2ePro_TRANSLATIONS
        // Product cannot be Listed because Rakuten.com SKU is not specified.
        if (empty($generalId)) {
            $this->addMessage('Product cannot be Listed because Rakuten.com SKU is not specified.');
            return false;
        }

        $this->data['general_id'] = $generalId;

        return true;
    }

    // ########################################
}