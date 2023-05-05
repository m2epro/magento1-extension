<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_SellingFormat_Diff extends Ess_M2ePro_Model_ActiveRecord_Diff
{
    //########################################

    public function isDifferent()
    {
        return $this->isQtyDifferent() ||
               $this->isPriceDifferent() ||
               $this->isOtherDifferent();
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

    public function isPriceDifferent()
    {
        $keys = array(
            'price_variation_mode',
            'fixed_price_mode',
            'fixed_price_coefficient',
            'fixed_price_custom_attribute',
            'start_price_mode',
            'start_price_coefficient',
            'start_price_custom_attribute',
            'reserve_price_mode',
            'reserve_price_coefficient',
            'reserve_price_custom_attribute',
            'buyitnow_price_mode',
            'buyitnow_price_coefficient',
            'buyitnow_price_custom_attribute',
            'price_discount_stp_mode',
            'price_discount_stp_attribute',
            'price_discount_stp_type',
            'price_discount_map_mode',
            'price_discount_map_attribute',
            'price_discount_map_exposure_type',
        );

        return $this->isSettingsDifferent($keys);
    }

    public function isOtherDifferent()
    {
        $keys = array(
            'best_offer_mode',
            'best_offer_accept_mode',
            'best_offer_accept_value',
            'best_offer_accept_attribute',
            'best_offer_reject_mode',
            'best_offer_reject_value',
            'best_offer_reject_attribute',
            'charity',
            'vat_mode',
            'vat_percent',
            'tax_table_mode',
            'tax_category_mode',
            'tax_category_value',
            'tax_category_attribute',
            'lot_size_mode',
            'lot_size_custom_value',
            'lot_size_attribute'
        );

        return $this->isSettingsDifferent($keys);
    }

    //########################################
}
