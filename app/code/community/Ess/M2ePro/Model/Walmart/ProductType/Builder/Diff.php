<?php

class Ess_M2ePro_Model_Walmart_ProductType_Builder_Diff extends Ess_M2ePro_Model_ActiveRecord_Diff
{
    public function isDifferent()
    {
        return $this->isSettingsDifferent(array(
            Ess_M2ePro_Model_Resource_Walmart_ProductType::COLUMN_ATTRIBUTES_SETTINGS,
            Ess_M2ePro_Model_Resource_Walmart_ProductType::COLUMN_DICTIONARY_PRODUCT_TYPE_ID,
        ));
    }
}
