<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Template_Description as Description;

class Ess_M2ePro_Model_Walmart_Template_Description_Builder extends Ess_M2ePro_Model_ActiveRecord_AbstractBuilder
{
    //########################################

    protected function prepareData()
    {
        $data = array();

        $defaultData = $this->getDefaultData();
        unset($defaultData['id']);
        $keys = array_keys($defaultData);

        foreach ($keys as $key) {
            isset($this->_rawData[$key]) && $data[$key] = $this->_rawData[$key];
        }

        $data['title'] = strip_tags($data['title']);

        $data['key_features']   = Mage::helper('M2ePro')->jsonEncode($data['key_features']);
        $data['other_features'] = Mage::helper('M2ePro')->jsonEncode($data['other_features']);
        $data['attributes']     = Mage::helper('M2ePro')->jsonEncode(
            $this->getComparedData($data, 'attributes_name', 'attributes_value')
        );

        return $data;
    }

    protected function getComparedData($data, $keyName, $valueName)
    {
        $result = array();

        if (!isset($data[$keyName]) || !isset($data[$valueName])) {
            return $result;
        }

        $keyData = array_filter($data[$keyName]);
        $valueData = array_filter($data[$valueName]);

        if (count($keyData) !== count($valueData)) {
            return $result;
        }

        foreach ($keyData as $index => $value) {
            $result[] = array('name' => $value, 'value' => $valueData[$index]);
        }

        return $result;
    }

    public function getDefaultData()
    {
        return array(
            'id'             => '',
            'title'          => '',

            'title_mode'     => Description::TITLE_MODE_PRODUCT,
            'title_template' => '',

            'brand_mode'             => Description::BRAND_MODE_CUSTOM_VALUE,
            'brand_custom_value'     => '',
            'brand_custom_attribute' => '',

            'manufacturer_mode'             => Description::MANUFACTURER_MODE_NONE,
            'manufacturer_custom_value'     => '',
            'manufacturer_custom_attribute' => '',

            'manufacturer_part_number_mode'             => Description::MANUFACTURER_PART_NUMBER_MODE_NONE,
            'manufacturer_part_number_custom_value'     => '',
            'manufacturer_part_number_custom_attribute' => '',

            // ---

            'model_number_mode'             => Description::MODEL_NUMBER_MODE_NONE,
            'model_number_custom_value'     => '',
            'model_number_custom_attribute' => '',

            'total_count_mode'             => Description::TOTAL_COUNT_MODE_NONE,
            'total_count_custom_value'     => '',
            'total_count_custom_attribute' => '',

            'count_per_pack_mode'             => Description::COUNT_PER_PACK_MODE_NONE,
            'count_per_pack_custom_value'     => '',
            'count_per_pack_custom_attribute' => '',

            'multipack_quantity_mode'             => Description::MULTIPACK_QUANTITY_MODE_NONE,
            'multipack_quantity_custom_value'     => '',
            'multipack_quantity_custom_attribute' => '',

            // ---

            'msrp_rrp_mode'             => Description::MSRP_RRP_MODE_NONE,
            'msrp_rrp_custom_attribute' => '',

            // ---

            'description_mode'     => Description::DESCRIPTION_MODE_PRODUCT,
            'description_template' => '',

            'image_main_mode'      => Description::IMAGE_MAIN_MODE_PRODUCT,
            'image_main_attribute' => '',

            'image_variation_difference_mode'      => Description::IMAGE_VARIATION_DIFFERENCE_MODE_NONE,
            'image_variation_difference_attribute' => '',

            'gallery_images_mode'      => Description::GALLERY_IMAGES_MODE_NONE,
            'gallery_images_limit'     => 1,
            'gallery_images_attribute' => '',

            'key_features_mode' => Description::KEY_FEATURES_MODE_NONE,
            'key_features'      => json_encode(array()),

            'other_features_mode' => Description::OTHER_FEATURES_MODE_NONE,
            'other_features'      => json_encode(array()),

            'attributes_mode' => Description::ATTRIBUTES_MODE_NONE,
            'attributes'      => json_encode(array()),
            'attributes_name' => json_encode(array()),
            'attributes_value' => json_encode(array())
        );
    }

    //########################################
}
