<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Template_Shipping as Shipping;
use Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated as ShippingCalculated;

class Ess_M2EPro_Model_Ebay_Template_Shipping_Builder
    extends Ess_M2ePro_Model_Ebay_Template_AbstractBuilder
{
    //########################################

    public function build($model, array $rawData)
    {
        /** @var Shipping $model */
        $model = parent::build($model, $rawData);

        if ($this->canSaveCalculatedData()) {
            $calculatedData = $this->prepareCalculatedData($model->getId());
            $this->createCalculated($model->getId(), $calculatedData);
        }

        $servicesData = $this->prepareServicesData($model->getId());
        $this->createServices($model->getId(), $servicesData);

        return $model;
    }

    //########################################

    protected function validate()
    {
        if (empty($this->_rawData['marketplace_id'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Marketplace ID is empty.');
        }

        if ($this->_rawData['country_mode'] == Shipping::COUNTRY_MODE_CUSTOM_VALUE &&
            empty($this->_rawData['country_custom_value']) ||
            $this->_rawData['country_mode'] == Shipping::COUNTRY_MODE_CUSTOM_ATTRIBUTE &&
            empty($this->_rawData['country_custom_attribute'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Country is empty.');
        }

        parent::validate();
    }

    //########################################

    protected function prepareData()
    {
        $this->validate();

        $data = parent::prepareData();

        $data['marketplace_id'] = (int)$this->_rawData['marketplace_id'];

        $keys = array(
            'country_mode',
            'country_custom_value',
            'country_custom_attribute',
            'postal_code_mode',
            'postal_code_custom_attribute',
            'postal_code_custom_value',
            'address_mode',
            'address_custom_attribute',
            'address_custom_value',
            'dispatch_time_mode',
            'dispatch_time_value',
            'dispatch_time_attribute',
            'global_shipping_program',
            'local_shipping_mode',
            'local_shipping_discount_promotional_mode',
            'international_shipping_mode',
            'international_shipping_discount_promotional_mode',
            'cross_border_trade',
        );

        foreach ($keys as $key) {
            $data[$key] = isset($this->_rawData[$key]) ? $this->_rawData[$key] : '';
        }

        if (isset($this->_rawData['local_shipping_rate_table'])) {
            $data['local_shipping_rate_table'] = Mage::helper('M2ePro')->jsonEncode(
                $this->_rawData['local_shipping_rate_table']
            );
        } else {
            $data['local_shipping_rate_table'] = Mage::helper('M2ePro')->jsonEncode(array());
        }

        if (isset($this->_rawData['international_shipping_rate_table'])) {
            $data['international_shipping_rate_table'] = Mage::helper('M2ePro')->jsonEncode(
                $this->_rawData['international_shipping_rate_table']
            );
        } else {
            $data['international_shipping_rate_table'] = Mage::helper('M2ePro')->jsonEncode(array());
        }

        if (isset($this->_rawData['local_shipping_discount_combined_profile_id'])) {
            $data['local_shipping_discount_combined_profile_id'] = Mage::helper('M2ePro')->jsonEncode(
                array_diff($this->_rawData['local_shipping_discount_combined_profile_id'], array(''))
            );
        } else {
            $data['local_shipping_discount_combined_profile_id'] = Mage::helper('M2ePro')->jsonEncode(array());
        }

        if (isset($this->_rawData['international_shipping_discount_combined_profile_id'])) {
            $data['international_shipping_discount_combined_profile_id'] = Mage::helper('M2ePro')->jsonEncode(
                array_diff($this->_rawData['international_shipping_discount_combined_profile_id'], array(''))
            );
        } else {
            $data['international_shipping_discount_combined_profile_id']
                = Mage::helper('M2ePro')->jsonEncode(array());
        }

        if (isset($this->_rawData['excluded_locations'])) {
            $data['excluded_locations'] = $this->_rawData['excluded_locations'];
        }

        $modes = array(
            'local_shipping_mode',
            'local_shipping_discount_promotional_mode',
            'international_shipping_mode',
            'international_shipping_discount_promotional_mode',
            'cross_border_trade',
        );

        foreach ($modes as $mode) {
            $data[$mode] = (int)$data[$mode];
        }

        return $data;
    }

    //########################################

    protected function prepareCalculatedData($templateShippingId)
    {
        $data = array('template_shipping_id' => $templateShippingId);

        $keys = array(
            'measurement_system',

            'package_size_mode',
            'package_size_value',
            'package_size_attribute',

            'dimension_mode',
            'dimension_width_value',
            'dimension_length_value',
            'dimension_depth_value',
            'dimension_width_attribute',
            'dimension_length_attribute',
            'dimension_depth_attribute',

            'weight_mode',
            'weight_minor',
            'weight_major',
            'weight_attribute'
        );

        foreach ($keys as $key) {
            $data[$key] = isset($this->_rawData[$key]) ? $this->_rawData[$key] : '';
        }

        $nullKeys = array(
            'local_handling_cost',
            'international_handling_cost'
        );

        foreach ($nullKeys as $key) {
            $data[$key] = (isset($this->_rawData[$key]) && $this->_rawData[$key] != '') ? $this->_rawData[$key] : null;
        }

        return $data;
    }

    protected function canSaveCalculatedData()
    {
        if ($this->_rawData['local_shipping_mode'] == Shipping::SHIPPING_TYPE_LOCAL ||
            $this->_rawData['local_shipping_mode'] == Shipping::SHIPPING_TYPE_FREIGHT) {
            return false;
        }

        return true;
    }

    protected function createCalculated($templateShippingId, array $data)
    {
        $coreRes = Mage::getSingleton('core/resource');
        $connWrite = $coreRes->getConnection('core_write');

        $connWrite->delete(
            Mage::getResourceModel('M2ePro/Ebay_Template_Shipping_Calculated')->getMainTable(),
            array(
                'template_shipping_id = ?' => (int)$templateShippingId
            )
        );

        if (empty($data)) {
            return;
        }

        Mage::getModel('M2ePro/Ebay_Template_Shipping_Calculated')->setData($data)->save();
    }

    //########################################

    protected function prepareServicesData($templateShippingId)
    {
        if (isset($this->_rawData['shipping_type']['%i%'])) {
            unset($this->_rawData['shipping_type']['%i%']);
        }

        if (isset($this->_rawData['cost_mode']['%i%'])) {
            unset($this->_rawData['cost_mode']['%i%']);
        }

        if (isset($this->_rawData['shipping_priority']['%i%'])) {
            unset($this->_rawData['shipping_priority']['%i%']);
        }

        if (isset($this->_rawData['shipping_cost_value']['%i%'])) {
            unset($this->_rawData['shipping_cost_value']['%i%']);
        }

        if (isset($this->_rawData['shipping_cost_additional_value']['%i%'])) {
            unset($this->_rawData['shipping_cost_additional_value']['%i%']);
        }

        // ---------------------------------------

        $services = array();
        foreach ($this->_rawData['cost_mode'] as $i => $costMode) {
            $locations = array();
            if (isset($this->_rawData['shippingLocation'][$i])) {
                foreach ($this->_rawData['shippingLocation'][$i] as $location) {
                    $locations[] = $location;
                }
            }

            $shippingType = $this->_rawData['shipping_type'][$i] == 'local'
                ? Ess_M2ePro_Model_Ebay_Template_Shipping_Service::SHIPPING_TYPE_LOCAL
                : Ess_M2ePro_Model_Ebay_Template_Shipping_Service::SHIPPING_TYPE_INTERNATIONAL;

            if ($costMode == Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_CUSTOM_ATTRIBUTE) {
                $cost = isset($this->_rawData['shipping_cost_attribute'][$i])
                    ? $this->_rawData['shipping_cost_attribute'][$i]
                    : '';

                $costAdditional = isset($this->_rawData['shipping_cost_additional_attribute'][$i])
                    ? $this->_rawData['shipping_cost_additional_attribute'][$i]
                    : '';
            } else {
                $cost = isset($this->_rawData['shipping_cost_value'][$i])
                    ? $this->_rawData['shipping_cost_value'][$i]
                    : '';

                $costAdditional = isset($this->_rawData['shipping_cost_additional_value'][$i])
                    ? $this->_rawData['shipping_cost_additional_value'][$i]
                    : '';
            }

            $services[] = array(
                'template_shipping_id'  => $templateShippingId,
                'cost_mode'             => $costMode,
                'cost_value'            => $cost,
                'shipping_value'        => $this->_rawData['shipping_service'][$i],
                'shipping_type'         => $shippingType,
                'cost_additional_value' => $costAdditional,
                'priority'              => $this->_rawData['shipping_priority'][$i],
                'locations'             => Mage::helper('M2ePro')->jsonEncode($locations)
            );
        }

        return $services;
    }

    protected function createServices($templateShippingId, array $data)
    {
        $coreRes = Mage::getSingleton('core/resource');
        $connWrite = $coreRes->getConnection('core_write');

        $connWrite->delete(
            Mage::getResourceModel('M2ePro/Ebay_Template_Shipping_Service')->getMainTable(),
            array(
                'template_shipping_id = ?' => (int)$templateShippingId
            )
        );

        if (empty($data)) {
            return;
        }

        $connWrite->insertMultiple(
            Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('M2ePro/Ebay_Template_Shipping_Service'),
            $data
        );
    }

    //########################################

    protected function isRateTableEnabled(array $rateTableData)
    {
        if (empty($rateTableData)) {
            return false;
        }

        foreach ($rateTableData as $data) {
            if (!empty($data['value'])) {
                return true;
            }
        }

        return false;
    }

    //########################################

    public function getDefaultData()
    {
        return array(
            'country_mode'                 => Shipping::COUNTRY_MODE_CUSTOM_VALUE,
            'country_custom_value'         => 'US',
            'country_custom_attribute'     => '',
            'postal_code_mode'             => Shipping::POSTAL_CODE_MODE_NONE,
            'postal_code_custom_value'     => '',
            'postal_code_custom_attribute' => '',
            'address_mode'                 => Shipping::ADDRESS_MODE_NONE,
            'address_custom_value'         => '',
            'address_custom_attribute'     => '',

            'dispatch_time_mode'      => Shipping::DISPATCH_TIME_MODE_VALUE,
            'dispatch_time_value'     => 1,
            'dispatch_time_attribute' => '',
            'global_shipping_program' => 0,
            'cross_border_trade'      => Shipping::CROSS_BORDER_TRADE_NONE,
            'excluded_locations'      => Mage::helper('M2ePro')->jsonEncode(array()),

            'local_shipping_mode'                         => Shipping::SHIPPING_TYPE_FLAT,
            'local_shipping_discount_promotional_mode'    => 0,
            'local_shipping_discount_combined_profile_id' => Mage::helper('M2ePro')->jsonEncode(array()),
            'local_shipping_rate_table_mode'              => 0,
            'local_shipping_rate_table'                   => null,

            'international_shipping_mode'                         => Shipping::SHIPPING_TYPE_NO_INTERNATIONAL,
            'international_shipping_discount_promotional_mode'    => 0,
            'international_shipping_discount_combined_profile_id' => Mage::helper('M2ePro')->jsonEncode(array()),
            'international_shipping_rate_table_mode'              => 0,
            'international_shipping_rate_table'                   => null,

            // CALCULATED SHIPPING
            // ---------------------------------------
            'measurement_system'                                  => ShippingCalculated::MEASUREMENT_SYSTEM_ENGLISH,

            'package_size_mode'      => ShippingCalculated::PACKAGE_SIZE_NONE,
            'package_size_value'     => '',
            'package_size_attribute' => '',

            'dimension_mode'             => ShippingCalculated::DIMENSION_NONE,
            'dimension_width_value'      => '',
            'dimension_length_value'     => '',
            'dimension_depth_value'      => '',
            'dimension_width_attribute'  => '',
            'dimension_length_attribute' => '',
            'dimension_depth_attribute'  => '',

            'weight_mode'      => ShippingCalculated::WEIGHT_NONE,
            'weight_minor'     => '',
            'weight_major'     => '',
            'weight_attribute' => '',

            'local_handling_cost'         => null,
            'international_handling_cost' => null,
            // ---------------------------------------

            'services' => array()
        );
    }

    //########################################
}
