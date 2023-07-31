<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Template_SellingFormat as WalmartTemplateSellingFormat;


class Ess_M2ePro_Model_Walmart_Template_SellingFormat_Builder extends Ess_M2ePro_Model_ActiveRecord_AbstractBuilder
{
    //########################################

    protected function prepareData()
    {
        $data = array();

        $keys = array_keys($this->getDefaultData());

        foreach ($keys as $key) {
            if (isset($this->_rawData[$key])) {
                $data[$key] = $this->_rawData[$key];
            }
        }

        $data['title'] = strip_tags($data['title']);

        if ($data['sale_time_start_date_value'] === '') {
            $data['sale_time_start_date_value'] = Mage::helper('M2ePro')->getCurrentGmtDate(
                false, 'Y-m-d 00:00:00'
            );
        } else {
            $data['sale_time_start_date_value'] = Mage::helper('M2ePro')
                ->createGmtDateTime($data['sale_time_start_date_value'])
                ->format('Y-m-d 00:00:00');
        }

        if ($data['sale_time_end_date_value'] === '') {
            $data['sale_time_end_date_value'] = Mage::helper('M2ePro')->getCurrentGmtDate(
                false, 'Y-m-d 00:00:00'
            );
        } else {
            $data['sale_time_end_date_value'] = Mage::helper('M2ePro')
                ->createGmtDateTime($data['sale_time_end_date_value'])
                ->format('Y-m-d 00:00:00');
        }

        $data['attributes'] = Mage::helper('M2ePro')->jsonEncode(
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
            'title' => '',
            'marketplace_id' => '',

            'qty_mode' => Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT,
            'qty_custom_value' => 1,
            'qty_custom_attribute' => '',
            'qty_percentage' => 100,
            'qty_modification_mode' => WalmartTemplateSellingFormat::QTY_MODIFICATION_MODE_OFF,
            'qty_min_posted_value' => WalmartTemplateSellingFormat::QTY_MIN_POSTED_DEFAULT_VALUE,
            'qty_max_posted_value' => WalmartTemplateSellingFormat::QTY_MAX_POSTED_DEFAULT_VALUE,

            'price_mode' => Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_PRODUCT,
            'price_coefficient' => '',
            'price_custom_attribute' => '',

            'price_variation_mode' => WalmartTemplateSellingFormat::PRICE_VARIATION_MODE_PARENT,

            'promotions_mode' => WalmartTemplateSellingFormat::PROMOTIONS_MODE_NO,
            'promotions' => array(),

            'sale_time_start_date_mode' => WalmartTemplateSellingFormat::DATE_NONE,
            'sale_time_end_date_mode' => WalmartTemplateSellingFormat::DATE_NONE,

            'sale_time_start_date_custom_attribute' => '',
            'sale_time_end_date_custom_attribute' => '',

            'sale_time_start_date_value' => Mage::helper('M2ePro')->getCurrentGmtDate(false, 'Y-m-d'),
            'sale_time_end_date_value' => Mage::helper('M2ePro')->getCurrentGmtDate(false, 'Y-m-d'),

            'item_weight_mode' => WalmartTemplateSellingFormat::WEIGHT_MODE_CUSTOM_ATTRIBUTE,
            'item_weight_custom_value' => '',
            'item_weight_custom_attribute' => 'weight',

            'price_vat_percent' => 0,

            'lag_time_mode' => WalmartTemplateSellingFormat::LAG_TIME_MODE_RECOMMENDED,
            'lag_time_value' => 0,
            'lag_time_custom_attribute' => '',

            'must_ship_alone_mode' => WalmartTemplateSellingFormat::MUST_SHIP_ALONE_MODE_NONE,
            'must_ship_alone_value' => '',
            'must_ship_alone_custom_attribute' => '',

            'ships_in_original_packaging_mode' => WalmartTemplateSellingFormat::SHIPS_IN_ORIGINAL_PACKAGING_MODE_NONE,
            'ships_in_original_packaging_value' => '',
            'ships_in_original_packaging_custom_attribute' => '',

            'attributes_mode' => WalmartTemplateSellingFormat::ATTRIBUTES_MODE_NONE,
            'attributes' => '',
            'attributes_name' => json_encode(array()),
            'attributes_value' => json_encode(array()),

            'shipping_override_rule_mode' => WalmartTemplateSellingFormat::SHIPPING_OVERRIDE_RULE_MODE_NO,
            'shipping_override_rule' => array()
        );
    }

    //########################################
}
