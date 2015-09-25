<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Stop_Validator
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Validator
{
    // ########################################

    public function validate()
    {
        $params = $this->getParams();

        if (!$this->getListingProduct()->isStoppable()) {

            if (empty($params['remove'])) {

                // M2ePro_TRANSLATIONS
                // Item is not Listed or not available
                $this->addMessage('Item is not active or not available');

            } else {
                $this->getListingProduct()->deleteInstance();
                $this->getListingProduct()->isDeleted(true);
            }

            return false;
        }

        if (!$this->validateSku()) {
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

        return true;
    }

    // ########################################
}