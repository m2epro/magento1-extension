<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Template_SellingFormat_Diff extends Ess_M2ePro_Model_ActiveRecord_Diff
{
    //########################################

    public function isDifferent()
    {
        return $this->isQtyDifferent() ||
               $this->isLagTimeDifferent() ||
               $this->isPriceDifferent() ||
               $this->isPromotionsDifferent() ||
               $this->isDetailsDifferent();
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

    public function isLagTimeDifferent()
    {
        $keys = array(
            'lag_time_mode',
            'lag_time_value',
            'lag_time_custom_attribute',
        );

        return $this->isSettingsDifferent($keys);
    }

    public function isPriceDifferent()
    {
        $keys = array(
            'price_mode',
            'price_coefficient',
            'price_custom_attribute',
            'price_variation_mode',
            'price_vat_percent',
        );

        return $this->isSettingsDifferent($keys);
    }

    public function isPromotionsDifferent()
    {
        $keys = array(
            'promotions'
        );

        return $this->isSettingsDifferent($keys);
    }

    public function isDetailsDifferent()
    {
        $keys = array(
            'sale_time_start_date_mode',
            'sale_time_start_date_value',
            'sale_time_start_date_custom_attribute',
            'sale_time_end_date_mode',
            'sale_time_end_date_value',
            'sale_time_end_date_custom_attribute',
            'item_weight_mode',
            'item_weight_custom_value',
            'item_weight_custom_attribute',
            'must_ship_alone_mode',
            'must_ship_alone_value',
            'must_ship_alone_custom_attribute',
            'ships_in_original_packaging_mode',
            'ships_in_original_packaging_value',
            'ships_in_original_packaging_custom_attribute',
            'shipping_override_rule_mode',
            'shipping_overrides',
            'attributes_mode',
            'attributes',
        );

        return $this->isSettingsDifferent($keys);
    }

    //########################################
}
