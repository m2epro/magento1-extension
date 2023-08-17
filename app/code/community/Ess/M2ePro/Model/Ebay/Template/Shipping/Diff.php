<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Shipping_Diff extends Ess_M2ePro_Model_ActiveRecord_Diff
{
    //########################################

    public function isDifferent()
    {
        return $this->isShippingDifferent();
    }

    //########################################

    public function isShippingDifferent()
    {
        $keys = array(
            'country_mode',
            'country_custom_value',
            'country_custom_attribute',
            'postal_code_mode',
            'postal_code_custom_value',
            'postal_code_custom_attribute',
            'address_mode',
            'address_custom_value',
            'address_custom_attribute',
            'global_shipping_program',
            'dispatch_time_mode',
            'dispatch_time_value',
            'dispatch_time_attribute',
            'local_shipping_mode',
            'local_shipping_discount_promotional_mode',
            'local_shipping_discount_combined_profile_id',
            'international_shipping_mode',
            'international_shipping_discount_promotional_mode',
            'international_shipping_discount_combined_profile_id',
            'excluded_locations',
            'cross_border_trade',
            'local_shipping_rate_table_mode',
            'local_shipping_rate_table',
            'international_shipping_rate_table_mode',
            'international_shipping_rate_table',
            'services',
            'calculated_shipping',
        );

        return $this->isSettingsDifferent($keys);
    }

    //########################################
}
