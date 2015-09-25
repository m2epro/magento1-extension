<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Request_Details
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Request_Abstract
{
    // ########################################

    public function getData()
    {
        if (!$this->getConfigurator()->isDetailsAllowed()) {
            return array();
        }

        $data = array();

        if (!isset($this->validatorsData['condition'])) {
            $condition = $this->getBuyListingProduct()->getListingSource()->getCondition();
            !is_null($condition) && ($this->validatorsData['condition'] = $condition);
        }

        if (isset($this->validatorsData['condition'])) {
            $data['condition'] = $this->validatorsData['condition'];
        }

        if (!isset($this->validatorsData['condition_note'])) {
            $conditionNote = $this->getBuyListingProduct()->getListingSource()->getConditionNote();
            !is_null($conditionNote) && ($this->validatorsData['condition_note'] = $conditionNote);
        }

        if (isset($this->validatorsData['condition_note'])) {
            $data['condition_note'] = $this->validatorsData['condition_note'];
        }

        return $data;
    }

    // ########################################
}