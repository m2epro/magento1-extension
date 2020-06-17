<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_ReturnPolicy_Diff extends Ess_M2ePro_Model_ActiveRecord_Diff
{
    //########################################

    public function isDifferent()
    {
        return $this->isReturnDifferent();
    }

    //########################################

    public function isReturnDifferent()
    {
        $keys = array(
            'accepted',
            'option',
            'within',
            'shipping_cost',

            'international_accepted',
            'international_option',
            'international_within',
            'international_shipping_cost',

            'description',
        );

        return $this->isSettingsDifferent($keys);
    }

    //########################################
}
