<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Condition
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Abstract
{
    //########################################

    public function getData()
    {
        return array_merge(
            $this->getConditionData(),
            $this->getConditionNoteData()
        );
    }

    //########################################

    /**
     * @return array
     */
    protected function getConditionData()
    {
        $this->searchNotFoundAttributes();
        $data = $this->getEbayListingProduct()->getDescriptionTemplateSource()->getCondition();

        if (!$this->processNotFoundAttributes('Condition')) {
            return array();
        }

        return array(
            'item_condition' => $data
        );
    }

    /**
     * @return array
     */
    protected function getConditionNoteData()
    {
        $this->searchNotFoundAttributes();
        $data = $this->getEbayListingProduct()->getDescriptionTemplateSource()->getConditionNote();
        $this->processNotFoundAttributes('Seller Notes');

        return array(
            'item_condition_note' => $data
        );
    }
}
