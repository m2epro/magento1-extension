<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Template_Description_Diff extends Ess_M2ePro_Model_ActiveRecord_Diff
{
    //########################################

    public function isDifferent()
    {
        return $this->isDetailsDifferent();
    }

    //########################################

    public function isDetailsDifferent()
    {
        $mainKeys = array(
            'title_mode',
            'title_template',
            'brand_custom_value',
            'brand_custom_attribute',
            'count_per_pack_mode',
            'count_per_pack_custom_value',
            'count_per_pack_custom_attribute',
            'multipack_quantity_mode',
            'multipack_quantity_custom_value',
            'multipack_quantity_custom_attribute',
            'description_mode',
            'description_template',
            'key_features_mode',
            'key_features',
            'other_features_mode',
            'other_features',
            'attributes_mode',
            'attributes',
            'manufacturer_mode',
            'manufacturer_custom_value',
            'manufacturer_custom_attribute',
            'manufacturer_part_number_mode',
            'manufacturer_part_number_custom_value',
            'manufacturer_part_number_custom_attribute',
            'msrp_rrp_mode',
            'msrp_rrp_custom_attribute',
            'image_main_mode',
            'image_main_attribute',
            'image_variation_difference_mode',
            'image_variation_difference_attribute',
            'gallery_images_mode',
            'gallery_images_attribute',
            'gallery_images_limit',
        );

        return $this->isSettingsDifferent($mainKeys);
    }

    //########################################
}
