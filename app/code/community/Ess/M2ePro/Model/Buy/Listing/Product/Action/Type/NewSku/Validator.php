<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_NewSku_Validator
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Validator
{
    // ########################################

    public function validate()
    {
        if (!$this->getListingProduct()->isNotListed()) {

            // M2ePro_TRANSLATIONS
            // Item is already on Rakuten.com, or not available
            $this->addMessage('Item is already on Rakuten.com, or not available');

            return false;
        }

        $generalId = $this->getBuyListingProduct()->getGeneralId();
        if (!empty($generalId)) {

            // M2ePro_TRANSLATIONS
            // General id must be empty
            $this->addMessage('General id must be empty');

            return false;
        }

        if ($this->getVariationManager()->isVariationProduct() && !$this->validateVariationProductMatching()) {
            return false;
        }

        $newProductTemplateId = $this->getBuyListingProduct()->getTemplateNewProductId();
        if (empty($newProductTemplateId)) {

            // M2ePro_TRANSLATIONS
            // New SKU Policy is required
            $this->addMessage('New SKU Policy is required');

            return false;
        }

        return true;
    }

    // ########################################
}