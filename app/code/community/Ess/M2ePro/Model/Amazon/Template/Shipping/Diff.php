<?php

class Ess_M2ePro_Model_Amazon_Template_Shipping_Diff extends Ess_M2ePro_Model_ActiveRecord_Diff
{
    public function isDifferent()
    {
        return $this->isDetailsDifferent();
    }

    public function isDetailsDifferent()
    {
        $keys = array(
            Ess_M2ePro_Model_Resource_Amazon_Dictionary_TemplateShipping::COLUMN_TEMPLATE_ID,
        );

        return $this->isSettingsDifferent($keys);
    }
}
