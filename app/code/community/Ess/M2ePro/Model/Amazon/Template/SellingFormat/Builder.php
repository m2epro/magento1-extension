<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Template_SellingFormat as AmazonTemplateSellingFormat;

class Ess_M2ePro_Model_Amazon_Template_SellingFormat_Builder extends Ess_M2ePro_Model_ActiveRecord_AbstractBuilder
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

        if ($data['regular_sale_price_start_date_value'] === '') {
            $data['regular_sale_price_start_date_value'] = Mage::helper('M2ePro')->getCurrentGmtDate(
                false, 'Y-m-d 00:00:00'
            );
        } else {
            $data['regular_sale_price_start_date_value'] = Mage::helper('M2ePro')
                ->createGmtDateTime($data['regular_sale_price_start_date_value'])
                ->format('Y-m-d 00:00:00');
        }

        if ($data['regular_sale_price_end_date_value'] === '') {
            $data['regular_sale_price_end_date_value'] = Mage::helper('M2ePro')->getCurrentGmtDate(
                false, 'Y-m-d 00:00:00'
            );
        } else {
            $data['regular_sale_price_end_date_value'] = Mage::helper('M2ePro')
                ->createGmtDateTime($data['regular_sale_price_end_date_value'])
                ->format('Y-m-d 00:00:00');
        }

        if (empty($data['is_business_customer_allowed'])) {
            unset($data['business_price_mode']);
            unset($data['business_price_coefficient']);
            unset($data['business_price_custom_attribute']);
            unset($data['business_price_variation_mode']);
            unset($data['business_price_vat_percent']);
            unset($data['business_discounts_mode']);
            unset($data['business_discounts_tier_coefficient']);
            unset($data['business_discounts_tier_customer_group_id']);
        }

        $data['title'] = strip_tags($data['title']);

        return $data;
    }

    public function getDefaultData()
    {
        return array(
            'title' => '',

            'is_regular_customer_allowed' => 1,
            'is_business_customer_allowed' => 0,

            'qty_mode' => Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT,
            'qty_custom_value' => 1,
            'qty_custom_attribute' => '',
            'qty_percentage' => 100,
            'qty_modification_mode' => AmazonTemplateSellingFormat::QTY_MODIFICATION_MODE_OFF,
            'qty_min_posted_value' => AmazonTemplateSellingFormat::QTY_MIN_POSTED_DEFAULT_VALUE,
            'qty_max_posted_value' => AmazonTemplateSellingFormat::QTY_MAX_POSTED_DEFAULT_VALUE,

            'regular_price_mode' => Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_PRODUCT,
            'regular_price_coefficient' => '',
            'regular_price_custom_attribute' => '',

            'regular_map_price_mode' => Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE,
            'regular_map_price_custom_attribute' => '',

            'regular_list_price_mode' => AmazonTemplateSellingFormat::LIST_PRICE_MODE_NONE,
            'regular_list_price_custom_attribute' => '',

            'regular_sale_price_mode' => Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE,
            'regular_sale_price_coefficient' => '',
            'regular_sale_price_custom_attribute' => '',

            'regular_price_variation_mode' => AmazonTemplateSellingFormat::PRICE_VARIATION_MODE_PARENT,

            'regular_sale_price_start_date_mode' => AmazonTemplateSellingFormat::DATE_VALUE,
            'regular_sale_price_end_date_mode' => AmazonTemplateSellingFormat::DATE_VALUE,

            'regular_sale_price_start_date_custom_attribute' => '',
            'regular_sale_price_end_date_custom_attribute' => '',

            'regular_sale_price_start_date_value' => Mage::helper('M2ePro')->getCurrentGmtDate(false, 'Y-m-d'),
            'regular_sale_price_end_date_value' => Mage::helper('M2ePro')->getCurrentGmtDate(false, 'Y-m-d'),

            'regular_price_vat_percent' => 0,

            'business_price_mode' => Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_PRODUCT,
            'business_price_coefficient' => '',
            'business_price_custom_attribute' => '',

            'business_price_variation_mode' => AmazonTemplateSellingFormat::PRICE_VARIATION_MODE_PARENT,

            'business_price_vat_percent' => 0,

            'business_discounts_mode' => 0,
            'business_discounts_tier_coefficient' => '',
            'business_discounts_tier_customer_group_id' => null,

            'discount_rules' => array()
        );
    }

    //########################################
}
