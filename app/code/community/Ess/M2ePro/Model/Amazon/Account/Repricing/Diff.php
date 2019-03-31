<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Account_Repricing_Diff extends Ess_M2ePro_Model_Template_Diff_Abstract
{
    //########################################

    public function isDifferent()
    {
        return $this->isRepricingDifferent();
    }

    //########################################

    public function isRepricingDifferent()
    {
        $keys = array(
            'regular_price_mode',
            'regular_price_coefficient',
            'regular_price_attribute',
            'regular_price_variation_mode',
            'min_price_mode',
            'min_price_coefficient',
            'min_price_attribute',
            'min_price_value',
            'min_price_percent',
            'min_price_variation_mode',
            'max_price_mode',
            'max_price_coefficient',
            'max_price_attribute',
            'max_price_value',
            'max_price_percent',
            'max_price_variation_mode',
            'disable_mode',
            'disable_mode_attribute',
        );

        return $this->isSettingsDifferent($keys);
    }

    //########################################
}