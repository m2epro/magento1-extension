<?php

class Ess_M2ePro_Model_Walmart_Listing_Diff
    extends Ess_M2ePro_Model_ActiveRecord_Diff
{
    /**
     * @return bool
     */
    public function isConditionDifferent()
    {
        return $this->isSettingsDifferent(array(
            Ess_M2ePro_Model_Resource_Walmart_Listing::COLUMN_CONDITION_MODE,
            Ess_M2ePro_Model_Resource_Walmart_Listing::COLUMN_CONDITION_VALUE,
            Ess_M2ePro_Model_Resource_Walmart_Listing::COLUMN_CONDITION_CUSTOM_ATTRIBUTE,
        ));
    }
}