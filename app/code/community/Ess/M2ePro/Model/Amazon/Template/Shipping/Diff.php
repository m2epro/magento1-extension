<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Template_Shipping_Diff extends Ess_M2ePro_Model_ActiveRecord_Diff
{
    //########################################

    public function isDifferent()
    {
        return $this->isDetailsDifferent();
    }

    //########################################

    public function isDetailsDifferent()
    {
        $keys = array(
            'template_name_mode',
            'template_name_value',
            'template_name_attribute',
        );

        return $this->isSettingsDifferent($keys);
    }

    //########################################
}
