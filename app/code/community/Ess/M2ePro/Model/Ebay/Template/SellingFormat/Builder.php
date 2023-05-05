<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Template_SellingFormat as SellingFormat;

class Ess_M2EPro_Model_Ebay_Template_SellingFormat_Builder
    extends Ess_M2ePro_Model_Ebay_Template_AbstractBuilder
{
    //########################################

    protected function prepareData()
    {
        $data = parent::prepareData();

        $data = array_merge($this->getDefaultData(), $data);

        if (isset($this->_rawData['listing_type'])) {
            $data['listing_type'] = (int)$this->_rawData['listing_type'];
        }

        if (isset($this->_rawData['listing_is_private'])) {
            $data['listing_is_private'] = (int)(bool)$this->_rawData['listing_is_private'];
        }

        if (isset($this->_rawData['listing_type_attribute'])) {
            $data['listing_type_attribute'] = $this->_rawData['listing_type_attribute'];
        }

        if (isset($this->_rawData['duration_mode'])) {
            $data['duration_mode'] = (int)$this->_rawData['duration_mode'];
        }

        if (isset($this->_rawData['duration_attribute'])) {
            $data['duration_attribute'] = $this->_rawData['duration_attribute'];
        }

        if (isset($this->_rawData['qty_mode'])) {
            $data['qty_mode'] = (int)$this->_rawData['qty_mode'];
        }

        if (isset($this->_rawData['qty_custom_value'])) {
            $data['qty_custom_value'] = (int)$this->_rawData['qty_custom_value'];
        }

        if (isset($this->_rawData['qty_custom_attribute'])) {
            $data['qty_custom_attribute'] = $this->_rawData['qty_custom_attribute'];
        }

        if (isset($this->_rawData['qty_percentage'])) {
            $data['qty_percentage'] = (int)$this->_rawData['qty_percentage'];
        }

        if (isset($this->_rawData['qty_modification_mode'])) {
            $data['qty_modification_mode'] = (int)$this->_rawData['qty_modification_mode'];
        }

        if (isset($this->_rawData['qty_min_posted_value'])) {
            $data['qty_min_posted_value'] = (int)$this->_rawData['qty_min_posted_value'];
        }

        if (isset($this->_rawData['qty_max_posted_value'])) {
            $data['qty_max_posted_value'] = (int)$this->_rawData['qty_max_posted_value'];
        }

        if (isset($this->_rawData['lot_size_mode'])) {
            $data['lot_size_mode'] = (int)$this->_rawData['lot_size_mode'];
        }

        if (isset($this->_rawData['lot_size_custom_value'])) {
            $data['lot_size_custom_value'] = (int)$this->_rawData['lot_size_custom_value'];
        }

        if (isset($this->_rawData['lot_size_attribute'])) {
            $data['lot_size_attribute'] = $this->_rawData['lot_size_attribute'];
        }

        if (isset($this->_rawData['vat_mode'])) {
            $data['vat_mode'] = (int)$this->_rawData['vat_mode'];
        }

        if (isset($this->_rawData['vat_percent'])) {
            $data['vat_percent'] = (float)$this->_rawData['vat_percent'];
        }

        if (isset($this->_rawData['tax_table_mode'])) {
            $data['tax_table_mode'] = (int)$this->_rawData['tax_table_mode'];
        }

        if (isset($this->_rawData['tax_category_mode'])) {
            $data['tax_category_mode'] = (int)$this->_rawData['tax_category_mode'];
        }

        if (isset($this->_rawData['tax_category_value'])) {
            $data['tax_category_value'] = $this->_rawData['tax_category_value'];
        }

        if (isset($this->_rawData['tax_category_attribute'])) {
            $data['tax_category_attribute'] = $this->_rawData['tax_category_attribute'];
        }

        if (isset($this->_rawData['price_variation_mode'])) {
            $data['price_variation_mode'] = (int)$this->_rawData['price_variation_mode'];
        }

        // ---------------------------------------

        if (isset($this->_rawData['fixed_price_mode'])) {
            $data['fixed_price_mode'] = (int)$this->_rawData['fixed_price_mode'];
        }

        if (isset($this->_rawData['fixed_price_coefficient'], $this->_rawData['fixed_price_coefficient_mode'])) {
            $data['fixed_price_coefficient'] = $this->getFormattedPriceCoefficient(
                $this->_rawData['fixed_price_coefficient'], $this->_rawData['fixed_price_coefficient_mode']
            );
        }

        if (isset($this->_rawData['fixed_price_custom_attribute'])) {
            $data['fixed_price_custom_attribute'] = $this->_rawData['fixed_price_custom_attribute'];
        }

        // ---------------------------------------

        if (isset($this->_rawData['start_price_mode'])) {
            $data['start_price_mode'] = (int)$this->_rawData['start_price_mode'];
        }

        if (isset($this->_rawData['start_price_coefficient'], $this->_rawData['start_price_coefficient_mode'])) {
            $data['start_price_coefficient'] = $this->getFormattedPriceCoefficient(
                $this->_rawData['start_price_coefficient'], $this->_rawData['start_price_coefficient_mode']
            );
        }

        if (isset($this->_rawData['start_price_custom_attribute'])) {
            $data['start_price_custom_attribute'] = $this->_rawData['start_price_custom_attribute'];
        }

        // ---------------------------------------

        if (isset($this->_rawData['reserve_price_mode'])) {
            $data['reserve_price_mode'] = (int)$this->_rawData['reserve_price_mode'];
        }

        if (isset($this->_rawData['reserve_price_coefficient'], $this->_rawData['reserve_price_coefficient_mode'])) {
            $data['reserve_price_coefficient'] = $this->getFormattedPriceCoefficient(
                $this->_rawData['reserve_price_coefficient'], $this->_rawData['reserve_price_coefficient_mode']
            );
        }

        if (isset($this->_rawData['reserve_price_custom_attribute'])) {
            $data['reserve_price_custom_attribute'] = $this->_rawData['reserve_price_custom_attribute'];
        }

        // ---------------------------------------

        if (isset($this->_rawData['buyitnow_price_mode'])) {
            $data['buyitnow_price_mode'] = (int)$this->_rawData['buyitnow_price_mode'];
        }

        if (isset($this->_rawData['buyitnow_price_coefficient'], $this->_rawData['buyitnow_price_coefficient_mode'])) {
            $data['buyitnow_price_coefficient'] = $this->getFormattedPriceCoefficient(
                $this->_rawData['buyitnow_price_coefficient'], $this->_rawData['buyitnow_price_coefficient_mode']
            );
        }

        if (isset($this->_rawData['buyitnow_price_custom_attribute'])) {
            $data['buyitnow_price_custom_attribute'] = $this->_rawData['buyitnow_price_custom_attribute'];
        }

        // ---------------------------------------

        if (isset($this->_rawData['price_discount_stp_mode'])) {
            $data['price_discount_stp_mode'] = (int)$this->_rawData['price_discount_stp_mode'];
        }

        if (isset($this->_rawData['price_discount_stp_attribute'])) {
            $data['price_discount_stp_attribute'] = $this->_rawData['price_discount_stp_attribute'];
        }

        if (isset($this->_rawData['price_discount_stp_type'])) {
            $data['price_discount_stp_type'] = (int)$this->_rawData['price_discount_stp_type'];
        }

        // ---------------------------------------

        if (isset($this->_rawData['price_discount_map_mode'])) {
            $data['price_discount_map_mode'] = (int)$this->_rawData['price_discount_map_mode'];
        }

        if (isset($this->_rawData['price_discount_map_attribute'])) {
            $data['price_discount_map_attribute'] = $this->_rawData['price_discount_map_attribute'];
        }

        if (isset($this->_rawData['price_discount_map_exposure_type'])) {
            $data['price_discount_map_exposure_type'] = (int)$this->_rawData['price_discount_map_exposure_type'];
        }

        if (isset($this->_rawData['restricted_to_business'])) {
            $data['restricted_to_business'] = (int)$this->_rawData['restricted_to_business'];
        }

        // ---------------------------------------

        if (isset($this->_rawData['best_offer_mode'])) {
            $data['best_offer_mode'] = (int)$this->_rawData['best_offer_mode'];
        }

        if (isset($this->_rawData['best_offer_accept_mode'])) {
            $data['best_offer_accept_mode'] = (int)$this->_rawData['best_offer_accept_mode'];
        }

        if (isset($this->_rawData['best_offer_accept_value'])) {
            $data['best_offer_accept_value'] = $this->_rawData['best_offer_accept_value'];
        }

        if (isset($this->_rawData['best_offer_accept_attribute'])) {
            $data['best_offer_accept_attribute'] = $this->_rawData['best_offer_accept_attribute'];
        }

        if (isset($this->_rawData['best_offer_reject_mode'])) {
            $data['best_offer_reject_mode'] = (int)$this->_rawData['best_offer_reject_mode'];
        }

        if (isset($this->_rawData['best_offer_reject_value'])) {
            $data['best_offer_reject_value'] = $this->_rawData['best_offer_reject_value'];
        }

        if (isset($this->_rawData['best_offer_reject_attribute'])) {
            $data['best_offer_reject_attribute'] = $this->_rawData['best_offer_reject_attribute'];
        }

        $data['charity'] = null;

        if (!empty($this->_rawData['charity']) && !empty($this->_rawData['charity']['marketplace_id'])) {
            $charities = array();
            foreach ($this->_rawData['charity']['marketplace_id'] as $key => $marketplaceId) {
                if (empty($this->_rawData['charity']['organization_id'][$key])) {
                    continue;
                }

                $charities[$marketplaceId] = array(
                    'marketplace_id' => (int)$marketplaceId,
                    'organization_id' => (int)$this->_rawData['charity']['organization_id'][$key],
                    'organization_name' => $this->_rawData['charity']['organization_name'][$key],
                    'organization_custom' => (int)$this->_rawData['charity']['organization_custom'][$key],
                    'percentage' => (int)$this->_rawData['charity']['percentage'][$key]
                );
            }

            if (!empty($charities)) {
                $data['charity'] = Mage::helper('M2ePro')->jsonEncode($charities);
            }
        }

        if (isset($this->_rawData['ignore_variations'])) {
            $data['ignore_variations'] = (int)$this->_rawData['ignore_variations'];
        }

        return $data;
    }

    //########################################

    protected function getFormattedPriceCoefficient($priceCoeff, $priceCoeffMode)
    {
        if ($priceCoeffMode == Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_NONE) {
            return '';
        }

        $isCoefficientModeDecrease =
            $priceCoeffMode == Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_ABSOLUTE_DECREASE ||
            $priceCoeffMode == Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_PERCENTAGE_DECREASE;

        $isCoefficientModePercentage =
            $priceCoeffMode == Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_PERCENTAGE_DECREASE ||
            $priceCoeffMode == Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_COEFFICIENT_PERCENTAGE_INCREASE;

        $sign = $isCoefficientModeDecrease ? '-' : '+';
        $measuringSystem = $isCoefficientModePercentage ? '%' : '';

        return $sign . $priceCoeff . $measuringSystem;
    }

    //########################################

    public function getDefaultData()
    {
        return array(

            'listing_type' => SellingFormat::LISTING_TYPE_FIXED,
            'listing_type_attribute' => '',

            'listing_is_private' => SellingFormat::LISTING_IS_PRIVATE_NO,

            'duration_mode' => 3,
            'duration_attribute' => '',

            'qty_mode' => Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT,
            'qty_custom_value' => 1,
            'qty_custom_attribute' => '',
            'qty_percentage' => 100,
            'qty_modification_mode' => SellingFormat::QTY_MODIFICATION_MODE_OFF,
            'qty_min_posted_value' => SellingFormat::QTY_MIN_POSTED_DEFAULT_VALUE,
            'qty_max_posted_value' => SellingFormat::QTY_MAX_POSTED_DEFAULT_VALUE,

            'vat_mode'       => 0,
            'vat_percent'    => 0,
            'tax_table_mode' => 0,

            'restricted_to_business' => SellingFormat::RESTRICTED_TO_BUSINESS_DISABLED,

            'tax_category_mode'      => 0,
            'tax_category_value'     => '',
            'tax_category_attribute' => '',

            'price_variation_mode' => SellingFormat::PRICE_VARIATION_MODE_PARENT,

            'fixed_price_mode' => Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_PRODUCT,
            'fixed_price_coefficient' => '',
            'fixed_price_custom_attribute' => '',

            'start_price_mode' => Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_PRODUCT,
            'start_price_coefficient' => '',
            'start_price_custom_attribute' => '',

            'reserve_price_mode' => Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE,
            'reserve_price_coefficient' => '',
            'reserve_price_custom_attribute' => '',

            'buyitnow_price_mode' => Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE,
            'buyitnow_price_coefficient' => '',
            'buyitnow_price_custom_attribute' => '',

            'price_discount_stp_mode' => Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE,
            'price_discount_stp_attribute' => '',
            'price_discount_stp_type' => SellingFormat::PRICE_DISCOUNT_STP_TYPE_RRP,

            'price_discount_map_mode' => Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE,
            'price_discount_map_attribute' => '',
            'price_discount_map_exposure_type' => SellingFormat::PRICE_DISCOUNT_MAP_EXPOSURE_NONE,

            'best_offer_mode' => SellingFormat::BEST_OFFER_MODE_NO,

            'best_offer_accept_mode' => SellingFormat::BEST_OFFER_ACCEPT_MODE_NO,
            'best_offer_accept_value' => '',
            'best_offer_accept_attribute' => '',

            'best_offer_reject_mode' => SellingFormat::BEST_OFFER_REJECT_MODE_NO,
            'best_offer_reject_value' => '',
            'best_offer_reject_attribute' => '',

            'charity' => '',
            'ignore_variations' => 0,

            'lot_size_mode' => 0,
            'lot_size_custom_value' => '',
            'lot_size_attribute' => ''
        );
    }

    //########################################
}
