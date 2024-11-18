<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Diff extends Ess_M2ePro_Model_ActiveRecord_Diff
{
    //########################################

    public function isDifferent()
    {
        return $this->isQtyDifferent() ||
               $this->isConditionDifferent() ||
               $this->isDetailsDifferent() ||
               $this->isSkuSettingsDifferent();
    }

    //########################################

    public function isQtyDifferent()
    {
        $keys = array(
            'handling_time_mode',
            'handling_time_value',
            'handling_time_custom_attribute',
            'restock_date_mode',
            'restock_date_value',
            'restock_date_custom_attribute',
        );

        return $this->isSettingsDifferent($keys);
    }

    public function isConditionDifferent()
    {
        $keys = array(
            'condition_mode',
            'condition_value',
            'condition_custom_attribute',
        );

        return $this->isSettingsDifferent($keys);
    }

    public function isDetailsDifferent()
    {
        $keys = array(
            'condition_note_mode',
            'condition_note_value',
            'gift_wrap_mode',
            'gift_wrap_attribute',
            'gift_message_mode',
            'gift_message_attribute',
        );

        return $this->isSettingsDifferent($keys);
    }

    public function isSkuSettingsDifferent()
    {
        $keys = array(
            'sku_mode',
            'sku_custom_attribute',
            'sku_modification_mode',
            'sku_modification_custom_value',
            'generate_sku_mode',
        );

        return $this->isSettingsDifferent($keys);
    }

    //########################################
}
