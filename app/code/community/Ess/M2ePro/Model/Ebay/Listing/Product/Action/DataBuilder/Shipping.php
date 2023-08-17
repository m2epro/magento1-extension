<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Shipping
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Abstract
{
    const SHIPPING_TYPE_FLAT       = 'flat';
    const SHIPPING_TYPE_CALCULATED = 'calculated';
    const SHIPPING_TYPE_FREIGHT    = 'freight';
    const SHIPPING_TYPE_LOCAL      = 'local';

    const MEASUREMENT_SYSTEM_ENGLISH = 'English';
    const MEASUREMENT_SYSTEM_METRIC  = 'Metric';

    const CROSS_BORDER_TRADE_NONE           = 'None';
    const CROSS_BORDER_TRADE_NORTH_AMERICA  = 'North America';
    const CROSS_BORDER_TRADE_UNITED_KINGDOM = 'UK';

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    protected $_shippingTemplate = null;

    protected $_calculatedShippingData = null;

    //########################################

    public function getData()
    {
        $data = array(
            'country'     => $this->getShippingSource()->getCountry(),
            'address'     => $this->getShippingSource()->getAddress(),
            'postal_code' => $this->getShippingSource()->getPostalCode()
        );

        if ($this->getShippingTemplate()->isLocalShippingFlatEnabled() ||
            $this->getShippingTemplate()->isLocalShippingCalculatedEnabled()) {
            $data['dispatch_time'] = $this->getShippingSource()->getDispatchTime();

            // there are permissions by marketplace (interface management)
            if ($this->getShippingTemplate()->isCrossBorderTradeNorthAmerica()) {
                $data['cross_border_trade'] = self::CROSS_BORDER_TRADE_NORTH_AMERICA;
            } else {
                if ($this->getShippingTemplate()->isCrossBorderTradeUnitedKingdom()) {
                    $data['cross_border_trade'] = self::CROSS_BORDER_TRADE_UNITED_KINGDOM;
                } else {
                    $data['cross_border_trade'] = self::CROSS_BORDER_TRADE_NONE;
                }
            }

            $data['excluded_locations'] = array();
            foreach ($this->getShippingTemplate()->getExcludedLocations() as $location) {
                $data['excluded_locations'][] = $location['code'];
            }

            // there are permissions by marketplace (interface management)
            $data['global_shipping_program'] = $this->getShippingTemplate()->isGlobalShippingProgramEnabled();
        }

        return array(
            'shipping' => array_merge(
                $data,
                $this->getShippingData()
            )
        );
    }

    //########################################

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getShippingData()
    {
        $shippingData = array();

        $shippingData['local'] = $this->getLocalShippingData();

        if ($this->getShippingTemplate()->isLocalShippingLocalEnabled() ||
            $this->getShippingTemplate()->isLocalShippingFreightEnabled()) {
            return $shippingData;
        }

        if ($this->getShippingTemplate()->isInternationalShippingFlatEnabled() ||
            $this->getShippingTemplate()->isInternationalShippingCalculatedEnabled()) {
            $shippingData['international'] = $this->getInternationalShippingData();
        }

        if ($calculatedData = $this->getCalculatedData()) {
            $shippingData['calculated'] = $calculatedData;
        }

        return $shippingData;
    }

    /**
     * @return array
     */
    protected function getCalculatedData()
    {
        if ($this->_calculatedShippingData !== null) {
            return $this->_calculatedShippingData;
        }

        if ($this->getCalculatedShippingTemplate() === null) {
            return array();
        }

        $data = array();

        if ($this->getCalculatedShippingTemplate()->isPackageSizeSet()) {
            $data['package_size'] = $this->getCalculatedShippingSource()->getPackageSize();
        }

        if ($this->getCalculatedShippingTemplate()->isDimensionSet()) {
            $data['dimensions'] = $this->getCalculatedShippingSource()->getDimension();
        }

        if ($this->getCalculatedShippingTemplate()->isWeightSet()) {
            $data['weight'] = $this->getCalculatedShippingSource()->getWeight();
        }

        if (!empty($data)) {
            switch ($this->getCalculatedShippingTemplate()->getMeasurementSystem()) {
                case Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::MEASUREMENT_SYSTEM_ENGLISH:
                    $data['measurement_system'] = self::MEASUREMENT_SYSTEM_ENGLISH;
                    break;
                case Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::MEASUREMENT_SYSTEM_METRIC:
                    $data['measurement_system'] = self::MEASUREMENT_SYSTEM_METRIC;
                    break;
            }
        }

        return $this->_calculatedShippingData = $data;
    }

    //########################################

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getLocalShippingData()
    {
        $data = array(
            'type' => $this->getLocalType()
        );

        if ($this->getShippingTemplate()->isLocalShippingLocalEnabled() ||
            $this->getShippingTemplate()->isLocalShippingFreightEnabled()) {
            return $data;
        }

        $data['discount_promotional_enabled'] = $this->getShippingTemplate()
            ->isLocalShippingDiscountPromotionalEnabled();
        $data['discount_combined_profile_id'] = $this->getShippingTemplate()
            ->getLocalShippingDiscountCombinedProfileId(
                $this->getListingProduct()->getListing()->getAccountId()
            );

        if ($this->getShippingTemplate()->isLocalShippingFlatEnabled() &&
            $this->getShippingTemplate()->isLocalShippingRateTableEnabled($this->getAccount())) {
            $data['rate_table_mode'] = $this->getShippingTemplate()
                ->getLocalShippingRateTableMode($this->getAccount());
            $data['rate_table_enabled'] = $this->getShippingTemplate()
                ->isLocalShippingRateTableEnabled($this->getAccount());
            $data['rate_table_id'] = $this->getShippingTemplate()->getLocalShippingRateTableId($this->getAccount());
        }

        if ($this->getEbayAccount()->isRateTablesExist()) {
            $data['account_moved_to_rate_table_id'] = 1;
        }

        if ($this->getShippingTemplate()->isLocalShippingCalculatedEnabled()) {
            $data['handing_cost'] = $this->getCalculatedShippingTemplate()->getLocalHandlingCost();
        }

        $data['methods'] = $this->getLocalServices();

        return $data;
    }

    // ---------------------------------------

    protected function getLocalType()
    {
        if ($this->getShippingTemplate()->isLocalShippingLocalEnabled()) {
            return self::SHIPPING_TYPE_LOCAL;
        }

        if ($this->getShippingTemplate()->isLocalShippingFreightEnabled()) {
            return self::SHIPPING_TYPE_FREIGHT;
        }

        if ($this->getShippingTemplate()->isLocalShippingFlatEnabled()) {
            return self::SHIPPING_TYPE_FLAT;
        }

        if ($this->getShippingTemplate()->isLocalShippingCalculatedEnabled()) {
            return self::SHIPPING_TYPE_CALCULATED;
        }

        throw new Ess_M2ePro_Model_Exception_Logic('Unknown local shipping type.');
    }

    protected function getLocalServices()
    {
        $services = array();

        foreach ($this->getShippingTemplate()->getServices(true) as $service) {

            /** @var $service Ess_M2ePro_Model_Ebay_Template_Shipping_Service */

            if (!$service->isShippingTypeLocal()) {
                continue;
            }

            $tempDataMethod = array(
                'service' => $service->getShippingValue()
            );

            if ($this->getShippingTemplate()->isLocalShippingFlatEnabled()) {
                $store = $this->getListing()->getStoreId();
                $tempDataMethod['cost'] = $service->getSource($this->getMagentoProduct())
                    ->getCost($store);

                $tempDataMethod['cost_additional'] = $service->getSource($this->getMagentoProduct())
                    ->getCostAdditional($store);
            }

            if ($this->getShippingTemplate()->isLocalShippingCalculatedEnabled()) {
                $tempDataMethod['is_free'] = $service->isCostModeFree();
            }

            $services[] = $tempDataMethod;
        }

        return $services;
    }

    //########################################

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getInternationalShippingData()
    {
        $data = array(
            'type' => $this->getInternationalType()
        );

        $data['discount_promotional_enabled'] = $this->getShippingTemplate()
            ->isInternationalShippingDiscountPromotionalEnabled();
        $data['discount_combined_profile_id'] = $this->getShippingTemplate()
            ->getInternationalShippingDiscountCombinedProfileId(
                $this->getListingProduct()->getListing()->getAccountId()
            );

        if ($this->getShippingTemplate()->isInternationalShippingFlatEnabled() &&
            $this->getShippingTemplate()->isInternationalShippingRateTableEnabled($this->getAccount())
        ) {
            $data['rate_table_mode'] = $this->getShippingTemplate()
                ->getInternationalShippingRateTableMode($this->getAccount());
            $data['rate_table_enabled'] = $this->getShippingTemplate()
                ->isInternationalShippingRateTableEnabled($this->getAccount());
            $data['rate_table_id'] = $this->getShippingTemplate()
                ->getInternationalShippingRateTableId($this->getAccount());
        }

        if ($this->getEbayAccount()->isRateTablesExist()) {
            $data['account_moved_to_rate_table_id'] = 1;
        }

        if ($this->getShippingTemplate()->isInternationalShippingCalculatedEnabled()) {
            $data['handing_cost'] = $this->getCalculatedShippingTemplate()->getInternationalHandlingCost();
        }

        $data['methods'] = $this->getInternationalServices();

        return $data;
    }

    // ---------------------------------------

    protected function getInternationalType()
    {
        if ($this->getShippingTemplate()->isInternationalShippingFlatEnabled()) {
            return self::SHIPPING_TYPE_FLAT;
        }

        if ($this->getShippingTemplate()->isInternationalShippingCalculatedEnabled()) {
            return self::SHIPPING_TYPE_CALCULATED;
        }

        throw new Ess_M2ePro_Model_Exception_Logic('Unknown international shipping type.');
    }

    protected function getInternationalServices()
    {
        $services = array();

        foreach ($this->getShippingTemplate()->getServices(true) as $service) {

            /** @var $service Ess_M2ePro_Model_Ebay_Template_Shipping_Service */

            if (!$service->isShippingTypeInternational()) {
                continue;
            }

            $tempDataMethod = array(
                'service'   => $service->getShippingValue(),
                'locations' => $service->getLocations()
            );

            if ($this->getShippingTemplate()->isInternationalShippingFlatEnabled()) {
                $store = $this->getListing()->getStoreId();
                $tempDataMethod['cost'] = $service->getSource($this->getMagentoProduct())
                    ->getCost($store);

                $tempDataMethod['cost_additional'] = $service->getSource($this->getMagentoProduct())
                    ->getCostAdditional($store);
            }

            $services[] = $tempDataMethod;
        }

        return $services;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    protected function getShippingTemplate()
    {
        if ($this->_shippingTemplate === null) {
            $this->_shippingTemplate = $this->getEbayListingProduct()->getShippingTemplate();
        }

        return $this->_shippingTemplate;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping_Source | null
     */
    protected function getShippingSource()
    {
        if (($shippingTemplate = $this->getShippingTemplate()) !== null) {
            return $shippingTemplate->getSource($this->getMagentoProduct());
        }

        return null;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated | null
     */
    protected function getCalculatedShippingTemplate()
    {
        return $this->getShippingTemplate()->getCalculatedShipping();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated_Source | null
     */
    protected function getCalculatedShippingSource()
    {
        if (($calculatedShipping = $this->getCalculatedShippingTemplate()) !== null) {
            return $calculatedShipping->getSource($this->getMagentoProduct());
        }

        return null;
    }

    //########################################
}
