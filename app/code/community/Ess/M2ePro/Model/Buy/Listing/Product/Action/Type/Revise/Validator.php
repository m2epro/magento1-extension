<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Revise_Validator
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Validator
{
    //########################################

    /**
     * @return bool
     */
    public function validate()
    {
        if (!$this->validateSku()) {
            return false;
        }

        if (!$this->getListingProduct()->isRevisable()) {

            // M2ePro_TRANSLATIONS
            // Item is not Listed or not available
            $this->addMessage('Item is not Listed or not available');

            return false;
        }

        $generalId = $this->getBuyListingProduct()->getGeneralId();
        $condition = $this->getCondition();
        if (empty($generalId) || empty($condition)) {

            // M2ePro_TRANSLATIONS
            // Rakuten.com data was not received yet. Please wait and try again later.
            $this->addMessage('Rakuten.com data was not received yet. Please wait and try again later.');

            return false;
        }
        $this->data['condition'] = $condition;

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

    //########################################
}