<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Template_SellingFormat_Diff extends Ess_M2ePro_Model_ActiveRecord_Diff
{
    //########################################

    public function isDifferent()
    {
        return $this->isQtyDifferent() ||
               $this->isRegularPriceDifferent() ||
               $this->isBusinessPriceDifferent();
    }

    //########################################

    public function isQtyDifferent()
    {
        $keys = array(
            'qty_mode',
            'qty_custom_value',
            'qty_custom_attribute',
            'qty_percentage',
            'qty_modification_mode',
            'qty_min_posted_value',
            'qty_max_posted_value',
        );

        return $this->isSettingsDifferent($keys);
    }

    public function isRegularPriceDifferent()
    {
        $keys = array(
            'is_regular_customer_allowed',
            'regular_price_mode',
            'regular_price_coefficient',
            'regular_price_custom_attribute',
            'regular_map_price_mode',
            'regular_map_price_custom_attribute',
            'regular_sale_price_mode',
            'regular_sale_price_coefficient',
            'regular_sale_price_custom_attribute',
            'regular_sale_price_start_date_mode',
            'regular_sale_price_start_date_value',
            'regular_sale_price_start_date_custom_attribute',
            'regular_sale_price_end_date_mode',
            'regular_sale_price_end_date_value',
            'regular_sale_price_end_date_custom_attribute',
            'regular_price_variation_mode',
            'regular_price_vat_percent',
        );

        return $this->isSettingsDifferent($keys);
    }

    /**
     * @return bool
     */
    public function isListPriceDiffered()
    {
        $keys = array(
            'regular_list_price_mode',
            'regular_list_price_custom_attribute',
        );

        return $this->isSettingsDifferent($keys);
    }

    public function isBusinessPriceDifferent()
    {
        $keys = array(
            'is_business_customer_allowed',
            'business_price_mode',
            'business_price_coefficient',
            'business_price_custom_attribute',
            'business_price_variation_mode',
            'business_price_vat_percent',
            'business_discounts_mode',
            'business_discounts_tier_coefficient',
            'business_discounts_tier_customer_group_id',
            'business_price_qty_discounts',
        );

        return $this->isSettingsDifferent($keys);
    }

    //########################################
}
