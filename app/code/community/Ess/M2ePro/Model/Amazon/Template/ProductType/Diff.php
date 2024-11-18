<?php

class Ess_M2ePro_Model_Amazon_Template_ProductType_Diff
    extends Ess_M2ePro_Model_ActiveRecord_Diff
{
    /**
     * @return bool
     */
    public function isDifferent()
    {
        $keys = array('settings', 'dictionary_product_type_id');

        return $this->isSettingsDifferent($keys);
    }
}