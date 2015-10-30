<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Shipping_Builder
    extends Ess_M2ePro_Model_Ebay_Template_Builder_Abstract
{
    //########################################

    public function build(array $data)
    {
        if (empty($data)) {
            return NULL;
        }

        $this->validate($data);

        $generalData = $this->prepareGeneralData($data);

        $marketplace = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Marketplace', $generalData['marketplace_id']
        );

        $template = Mage::getModel('M2ePro/Ebay_Template_Shipping');

        if (isset($generalData['id'])) {
            $template->load($generalData['id']);
        }

        $template->addData($generalData);
        $template->save();
        $template->setMarketplace($marketplace);

        if ($this->canSaveCalculatedData($data)) {
            $calculatedData = $this->prepareCalculatedData($template->getId(), $data);
            $this->createCalculated($template->getId(), $calculatedData);
        }

        $servicesData = $this->prepareServicesData($template->getId(), $data);
        $this->createServices($template->getId(), $servicesData);

        return $template;
    }

    //########################################

    protected function validate(array $data)
    {
        if (empty($data['marketplace_id'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Marketplace ID is empty.');
        }

        if ($data['country_mode'] == Ess_M2ePro_Model_Ebay_Template_Shipping::COUNTRY_MODE_CUSTOM_VALUE &&
            empty($data['country_custom_value']) ||
            $data['country_mode'] == Ess_M2ePro_Model_Ebay_Template_Shipping::COUNTRY_MODE_CUSTOM_ATTRIBUTE &&
            empty($data['country_custom_attribute'])) {

            throw new Ess_M2ePro_Model_Exception_Logic('Country is empty.');
        }

        parent::validate($data);
    }

    //########################################

    protected function prepareGeneralData(array &$data)
    {
        $prepared = parent::prepareData($data);

        $prepared['marketplace_id'] = (int)$data['marketplace_id'];

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
            'dispatch_time',
            'global_shipping_program',
            'local_shipping_rate_table_mode',
            'international_shipping_rate_table_mode',
            'local_shipping_mode',
            'local_shipping_discount_mode',
            'international_shipping_mode',
            'international_shipping_discount_mode',
            'cross_border_trade',
        );

        foreach ($keys as $key) {
            $prepared[$key] = isset($data[$key]) ? $data[$key] : '';
        }

        if (isset($data['local_shipping_discount_profile_id'])) {
            $prepared['local_shipping_discount_profile_id'] =
                json_encode(array_diff($data['local_shipping_discount_profile_id'], array('')));
        } else {
            $prepared['local_shipping_discount_profile_id'] = json_encode(array());
        }

        if (isset($data['international_shipping_discount_profile_id'])) {
            $prepared['international_shipping_discount_profile_id'] =
                json_encode(array_diff($data['international_shipping_discount_profile_id'], array('')));
        } else {
            $prepared['international_shipping_discount_profile_id'] = json_encode(array());
        }

        if (isset($data['excluded_locations'])) {
            $prepared['excluded_locations'] = $data['excluded_locations'];
        }

        if (isset($data['click_and_collect_mode'])) {
            $prepared['click_and_collect_mode'] = (int)$data['click_and_collect_mode'];
        }

        $key = 'cash_on_delivery_cost';
        $prepared[$key] = (isset($data[$key]) && $data[$key] != '') ? $data[$key] : NULL;

        $modes = array(
            'local_shipping_rate_table_mode',
            'international_shipping_rate_table_mode',
            'local_shipping_mode',
            'local_shipping_discount_mode',
            'international_shipping_mode',
            'international_shipping_discount_mode',
            'cross_border_trade',
        );

        foreach ($modes as $mode) {
            $prepared[$mode] = (int)$prepared[$mode];
        }

        return $prepared;
    }

    //########################################

    private function prepareCalculatedData($templateShippingId, array $data)
    {
        $prepared = array('template_shipping_id' => $templateShippingId);

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
            $prepared[$key] = isset($data[$key]) ? $data[$key] : '';
        }

        $nullKeys = array(
            'local_handling_cost',
            'international_handling_cost'
        );

        foreach ($nullKeys as $key) {
            $prepared[$key] = (isset($data[$key]) && $data[$key] != '') ? $data[$key] : NULL;
        }

        return $prepared;
    }

    private function canSaveCalculatedData(array $data)
    {
        if ($data['local_shipping_mode'] == Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_CALCULATED) {
            return true;
        }

        if ($data['international_shipping_mode'] == Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_CALCULATED) {
            return true;
        }

        $marketplace = Mage::helper('M2ePro/Component_Ebay')->getObject('Marketplace', $data['marketplace_id']);

        $isLocalRateTableEnabled = $marketplace->getChildObject()->isLocalShippingRateTableEnabled();
        $isInternationalRateTableEnabled = $marketplace->getChildObject()->isInternationalShippingRateTableEnabled();

        if ($isLocalRateTableEnabled
            && $data['local_shipping_mode'] == Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_FLAT
            && !empty($data['local_shipping_rate_table_mode'])
        ) {
            return true;
        }

        if ($isInternationalRateTableEnabled
            && $data['international_shipping_mode'] == Ess_M2ePro_Model_Ebay_Template_Shipping::SHIPPING_TYPE_FLAT
            && !empty($data['international_shipping_rate_table_mode'])
        ) {
            return true;
        }

        if ($marketplace->getChildObject()->isClickAndCollectEnabled() &&
            !empty($data['click_and_collect_mode'])) {
            return true;
        }

        return false;
    }

    private function createCalculated($templateShippingId, array $data)
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

    private function prepareServicesData($templateShippingId, array $data)
    {
        if (isset($data['shipping_type']['%i%'])) {
            unset($data['shipping_type']['%i%']);
        }

        if (isset($data['cost_mode']['%i%'])) {
            unset($data['cost_mode']['%i%']);
        }

        if (isset($data['shipping_priority']['%i%'])) {
            unset($data['shipping_priority']['%i%']);
        }

        if (isset($data['shipping_cost_value']['%i%'])) {
            unset($data['shipping_cost_value']['%i%']);
        }

        if (isset($data['shipping_cost_surcharge_value']['%i%'])) {
            unset($data['shipping_cost_surcharge_value']['%i%']);
        }

        if (isset($data['shipping_cost_additional_value']['%i%'])) {
            unset($data['shipping_cost_additional_value']['%i%']);
        }

        // ---------------------------------------

        $services = array();
        foreach ($data['cost_mode'] as $i => $costMode) {

            $locations = array();
            if (isset($data['shippingLocation'][$i])) {
                foreach ($data['shippingLocation'][$i] as $location) {
                    $locations[] = $location;
                }
            }

            $shippingType = $data['shipping_type'][$i] == 'local'
                ? Ess_M2ePro_Model_Ebay_Template_Shipping_Service::SHIPPING_TYPE_LOCAL
                : Ess_M2ePro_Model_Ebay_Template_Shipping_Service::SHIPPING_TYPE_INTERNATIONAL;

            if ($costMode == Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_CUSTOM_ATTRIBUTE) {

                $cost = isset($data['shipping_cost_attribute'][$i])
                    ? $data['shipping_cost_attribute'][$i]
                    : '';

                $costAdditional = isset($data['shipping_cost_additional_attribute'][$i])
                    ? $data['shipping_cost_additional_attribute'][$i]
                    : '';

            } else {

                $cost = isset($data['shipping_cost_value'][$i])
                    ? $data['shipping_cost_value'][$i]
                    : '';

                $costAdditional = isset($data['shipping_cost_additional_value'][$i])
                    ? $data['shipping_cost_additional_value'][$i]
                    : '';
            }

            if ($costMode == Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_CUSTOM_ATTRIBUTE) {

                $costSurcharge = isset($data['shipping_cost_surcharge_attribute'][$i])
                    ? $data['shipping_cost_surcharge_attribute'][$i]
                    : '';

            } else if ($costMode == Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_CUSTOM_VALUE) {

                $costSurcharge = isset($data['shipping_cost_surcharge_value'][$i])
                    ? $data['shipping_cost_surcharge_value'][$i]
                    : '';

            } else {
                $costSurcharge = '';
            }

            $services[] = array(
                'template_shipping_id'  => $templateShippingId,
                'cost_mode'             => $costMode,
                'cost_value'            => $cost,
                'shipping_value'        => $data['shipping_service'][$i],
                'shipping_type'         => $shippingType,
                'cost_additional_value' => $costAdditional,
                'cost_surcharge_value'  => $costSurcharge,
                'priority'              => $data['shipping_priority'][$i],
                'locations'             => json_encode($locations)
            );
        }

        return $services;
    }

    private function createServices($templateShippingId, array $data)
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
            $coreRes->getTableName('M2ePro/Ebay_Template_Shipping_Service'), $data
        );
    }

    //########################################
}