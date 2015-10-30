<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Request_Details
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Request_Abstract
{
    //########################################

    /**
     * @return array
     */
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

    //########################################
}