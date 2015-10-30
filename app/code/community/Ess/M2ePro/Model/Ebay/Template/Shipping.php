<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Mysql4_Ebay_Template_Shipping getResource()
 */
class Ess_M2ePro_Model_Ebay_Template_Shipping extends Ess_M2ePro_Model_Component_Abstract
{
    const COUNTRY_MODE_CUSTOM_VALUE         = 1;
    const COUNTRY_MODE_CUSTOM_ATTRIBUTE     = 2;

    const POSTAL_CODE_MODE_NONE             = 0;
    const POSTAL_CODE_MODE_CUSTOM_VALUE     = 1;
    const POSTAL_CODE_MODE_CUSTOM_ATTRIBUTE = 2;

    const ADDRESS_MODE_NONE                 = 0;
    const ADDRESS_MODE_CUSTOM_VALUE         = 1;
    const ADDRESS_MODE_CUSTOM_ATTRIBUTE     = 2;

    const SHIPPING_TYPE_FLAT                = 0;
    const SHIPPING_TYPE_CALCULATED          = 1;
    const SHIPPING_TYPE_FREIGHT             = 2;
    const SHIPPING_TYPE_LOCAL               = 3;
    const SHIPPING_TYPE_NO_INTERNATIONAL    = 4;

    const CROSS_BORDER_TRADE_NONE           = 0;
    const CROSS_BORDER_TRADE_NORTH_AMERICA  = 1;
    const CROSS_BORDER_TRADE_UNITED_KINGDOM = 2;

    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    private $marketplaceModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated
     */
    private $calculatedShippingModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Shipping_Source[]
     */
    private $shippingSourceModels = array();

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Shipping');
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING;
    }

    //########################################

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Ebay_Listing')
                            ->getCollection()
                            ->addFieldToFilter('template_shipping_mode',
                                                Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE)
                            ->addFieldToFilter('template_shipping_id', $this->getId())
                            ->getSize() ||
               (bool)Mage::getModel('M2ePro/Ebay_Listing_Product')
                            ->getCollection()
                            ->addFieldToFilter('template_shipping_mode',
                                                Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE)
                            ->addFieldToFilter('template_shipping_id', $this->getId())
                            ->getSize();
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $calculatedShippingObject = $this->getCalculatedShipping();
        if (!is_null($calculatedShippingObject)) {
            $calculatedShippingObject->deleteInstance();
        }

        $services = $this->getServices(true);
        foreach ($services as $service) {
            $service->deleteInstance();
        }

        $this->marketplaceModel = NULL;
        $this->calculatedShippingModel = NULL;
        $this->shippingSourceModels = array();

        $this->delete();
        return true;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        if (is_null($this->marketplaceModel)) {
            $this->marketplaceModel = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Marketplace', $this->getMarketplaceId()
            );
        }

        return $this->marketplaceModel;
    }

    /**
     * @param Ess_M2ePro_Model_Marketplace $instance
     */
    public function setMarketplace(Ess_M2ePro_Model_Marketplace $instance)
    {
        $this->marketplaceModel = $instance;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->shippingSourceModels[$productId])) {
            return $this->shippingSourceModels[$productId];
        }

        $this->shippingSourceModels[$productId] = Mage::getModel('M2ePro/Ebay_Template_Shipping_Source');
        $this->shippingSourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->shippingSourceModels[$productId]->setShippingTemplate($this);

        return $this->shippingSourceModels[$productId];
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated
     */
    public function getCalculatedShipping()
    {
        if (is_null($this->calculatedShippingModel)) {

            try {
                $this->calculatedShippingModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_Shipping_Calculated', $this->getId(), NULL, array('template')
                );

                $this->calculatedShippingModel->setShippingTemplate($this);

            } catch (Exception $exception) {
                return $this->calculatedShippingModel;
            }
        }

        return $this->calculatedShippingModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated $instance
     */
    public function setCalculatedShipping(Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated $instance)
    {
         $this->calculatedShippingModel = $instance;
    }

    //########################################

    /**
     * @param bool $asObjects
     * @param array $filters
     * @param array $sort
     * @return array|Ess_M2ePro_Model_Abstract[]
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getServices($asObjects = false, array $filters = array(),
                                array $sort = array('priority'=>Varien_Data_Collection::SORT_ORDER_ASC))
    {
        $services = $this->getRelatedSimpleItems('Ebay_Template_Shipping_Service','template_shipping_id',
                                                 $asObjects, $filters, $sort);

        if ($asObjects) {
            /** @var $service Ess_M2ePro_Model_Ebay_Template_Shipping_Service */
            foreach ($services as $service) {
                $service->setShippingTemplate($this);
            }
        }

        return $services;
    }

    //########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    /**
     * @return bool
     */
    public function isCustomTemplate()
    {
        return (bool)$this->getData('is_custom_template');
    }

    /**
     * @return int
     */
    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
    }

    // ---------------------------------------

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    public function getUpdateDate()
    {
        return $this->getData('update_date');
    }

    //########################################

    /**
     * @return int
     */
    public function getCountryMode()
    {
        return (int)$this->getData('country_mode');
    }

    public function getCountryCustomValue()
    {
        return $this->getData('country_custom_value');
    }

    public function getCountryCustomAttribute()
    {
        return $this->getData('country_custom_attribute');
    }

    /**
     * @return array
     */
    public function getCountrySource()
    {
        return array(
            'mode'      => $this->getCountryMode(),
            'value'     => $this->getCountryCustomValue(),
            'attribute' => $this->getCountryCustomAttribute()
        );
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getPostalCodeMode()
    {
        return (int)$this->getData('postal_code_mode');
    }

    public function getPostalCodeCustomValue()
    {
        return $this->getData('postal_code_custom_value');
    }

    public function getPostalCodeCustomAttribute()
    {
        return $this->getData('postal_code_custom_attribute');
    }

    /**
     * @return array
     */
    public function getPostalCodeSource()
    {
        return array(
            'mode'      => $this->getPostalCodeMode(),
            'value'     => $this->getPostalCodeCustomValue(),
            'attribute' => $this->getPostalCodeCustomAttribute()
        );
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getAddressMode()
    {
        return (int)$this->getData('address_mode');
    }

    public function getAddressCustomValue()
    {
        return $this->getData('address_custom_value');
    }

    public function getAddressCustomAttribute()
    {
        return $this->getData('address_custom_attribute');
    }

    /**
     * @return array
     */
    public function getAddressSource()
    {
        return array(
            'mode'      => $this->getAddressMode(),
            'value'     => $this->getAddressCustomValue(),
            'attribute' => $this->getAddressCustomAttribute()
        );
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isGlobalShippingProgramEnabled()
    {
        return (bool)$this->getData('global_shipping_program');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isLocalShippingRateTableEnabled()
    {
        return (bool)$this->getData('local_shipping_rate_table_mode');
    }

    /**
     * @return bool
     */
    public function isInternationalShippingRateTableEnabled()
    {
        return (bool)$this->getData('international_shipping_rate_table_mode');
    }

    //########################################

    /**
     * @return int
     */
    public function getDispatchTime()
    {
        return (int)$this->getData('dispatch_time');
    }

    //########################################

    /**
     * @return bool
     */
    public function isLocalShippingFlatEnabled()
    {
        return (int)$this->getData('local_shipping_mode') == self::SHIPPING_TYPE_FLAT;
    }

    /**
     * @return bool
     */
    public function isLocalShippingCalculatedEnabled()
    {
        return (int)$this->getData('local_shipping_mode') == self::SHIPPING_TYPE_CALCULATED;
    }

    /**
     * @return bool
     */
    public function isLocalShippingFreightEnabled()
    {
        return (int)$this->getData('local_shipping_mode') == self::SHIPPING_TYPE_FREIGHT;
    }

    /**
     * @return bool
     */
    public function isLocalShippingLocalEnabled()
    {
        return (int)$this->getData('local_shipping_mode') == self::SHIPPING_TYPE_LOCAL;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isLocalShippingDiscountEnabled()
    {
        return (bool)$this->getData('local_shipping_discount_mode');
    }

    public function getLocalShippingDiscountProfileId($accountId)
    {
        $data = $this->getData('local_shipping_discount_profile_id');

        if (is_null($data)) {
            return NULL;
        }

        $data = json_decode($data, true);

        return !isset($data[$accountId]) ? NULL : $data[$accountId];
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isClickAndCollectEnabled()
    {
        return (bool)$this->getData('click_and_collect_mode');
    }

    //########################################

    /**
     * @return bool
     */
    public function isInternationalShippingNoInternationalEnabled()
    {
        return (int)$this->getData('international_shipping_mode') == self::SHIPPING_TYPE_NO_INTERNATIONAL;
    }

    /**
     * @return bool
     */
    public function isInternationalShippingFlatEnabled()
    {
        return (int)$this->getData('international_shipping_mode') == self::SHIPPING_TYPE_FLAT;
    }

    /**
     * @return bool
     */
    public function isInternationalShippingCalculatedEnabled()
    {
        return (int)$this->getData('international_shipping_mode') == self::SHIPPING_TYPE_CALCULATED;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isInternationalShippingDiscountEnabled()
    {
        return (bool)$this->getData('international_shipping_discount_mode');
    }

    public function getInternationalShippingDiscountProfileId($accountId)
    {
        $data = $this->getData('international_shipping_discount_profile_id');

        if (is_null($data)) {
            return NULL;
        }

        $data = json_decode($data, true);

        return !isset($data[$accountId]) ? NULL : $data[$accountId];
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getExcludedLocations()
    {
        $excludedLocations = $this->getData('excluded_locations');
        is_string($excludedLocations) && $excludedLocations = json_decode($excludedLocations,true);
        return is_array($excludedLocations) ? $excludedLocations : array();
    }

    /**
     * @return float|null
     */
    public function getCashOnDeliveryCost()
    {
        $tempData = $this->getData('cash_on_delivery_cost');

        if (!empty($tempData)) {
            return (float)$tempData;
        }

        return NULL;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getCrossBorderTrade()
    {
        return (int)$this->getData('cross_border_trade');
    }

    /**
     * @return bool
     */
    public function isCrossBorderTradeNone()
    {
        return $this->getCrossBorderTrade() == self::CROSS_BORDER_TRADE_NONE;
    }

    /**
     * @return bool
     */
    public function isCrossBorderTradeNorthAmerica()
    {
        return $this->getCrossBorderTrade() == self::CROSS_BORDER_TRADE_NORTH_AMERICA;
    }

    /**
     * @return bool
     */
    public function isCrossBorderTradeUnitedKingdom()
    {
        return $this->getCrossBorderTrade() == self::CROSS_BORDER_TRADE_UNITED_KINGDOM;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping_Service[]
     */
    public function getLocalShippingServices()
    {
        $returns = array();

        $services = $this->getServices(true);
        foreach ($services as $service) {

            /** @var $service Ess_M2ePro_Model_Ebay_Template_Shipping_Service */

            if ($service->isShippingTypeLocal()) {
                $returns[] = $service;
            }
        }

        return $returns;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping_Service[]
     */
    public function getInternationalShippingServices()
    {
        $returns = array();

        $services = $this->getServices(true);
        foreach ($services as $service) {

            /** @var $service Ess_M2ePro_Model_Ebay_Template_Shipping_Service */

            if ($service->isShippingTypeInternational()) {
                $returns[] = $service;
            }
        }

        return $returns;
    }

    //########################################

    /**
     * @return array
     */
    public function getTrackingAttributes()
    {
        $attributes = array();

        $calculatedShippingObject = $this->getCalculatedShipping();
        if (!is_null($calculatedShippingObject)) {
            $attributes = array_merge($attributes,$calculatedShippingObject->getTrackingAttributes());
        }

        $services = $this->getServices(true);
        foreach ($services as $service) {
            $attributes = array_merge($attributes,$service->getTrackingAttributes());
        }

        return array_unique($attributes);
    }

    /**
     * @return array
     */
    public function getUsedAttributes()
    {
        $attributes = array();

        $calculatedShippingObject = $this->getCalculatedShipping();
        if (!is_null($calculatedShippingObject)) {
            $attributes = array_merge($attributes,$calculatedShippingObject->getUsedAttributes());
        }

        $services = $this->getServices(true);
        foreach ($services as $service) {
            $attributes = array_merge($attributes,$service->getUsedAttributes());
        }

        return array_unique($attributes);
    }

    //########################################

    public function getDataSnapshot()
    {
        $data = parent::getDataSnapshot();

        $data['services'] = $this->getServices();
        $data['calculated_shipping'] = $this->getCalculatedShipping()?$this->getCalculatedShipping()->getData():array();

        foreach ($data['services'] as &$serviceData) {
            foreach ($serviceData as &$value) {
                !is_null($value) && !is_array($value) && $value = (string)$value;
            }
        }
        unset($value);

        foreach ($data['calculated_shipping'] as &$value) {
            !is_null($value) && !is_array($value) && $value = (string)$value;
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getDefaultSettingsSimpleMode()
    {
        return $this->getDefaultSettingsAdvancedMode();
    }

    /**
     * @return array
     */
    public function getDefaultSettingsAdvancedMode()
    {
        return array(
            'country_mode' => self::COUNTRY_MODE_CUSTOM_VALUE,
            'country_custom_value' => 'US',
            'country_custom_attribute' => '',
            'postal_code_mode' => self::POSTAL_CODE_MODE_NONE,
            'postal_code_custom_value' => '',
            'postal_code_custom_attribute' => '',
            'address_mode' => self::ADDRESS_MODE_NONE,
            'address_custom_value' => '',
            'address_custom_attribute' => '',

            'dispatch_time' => 1,
            'cash_on_delivery_cost' => NULL,
            'global_shipping_program' => 0,
            'cross_border_trade' => self::CROSS_BORDER_TRADE_NONE,
            'excluded_locations' => json_encode(array()),

            'local_shipping_mode' =>  self::SHIPPING_TYPE_FLAT,
            'local_shipping_discount_mode' => 0,
            'local_shipping_discount_profile_id' => json_encode(array()),
            'local_shipping_rate_table_mode' => 0,
            'click_and_collect_mode' => 1,

            'international_shipping_mode' => self::SHIPPING_TYPE_NO_INTERNATIONAL,
            'international_shipping_discount_mode' => 0,
            'international_shipping_discount_profile_id' => json_encode(array()),
            'international_shipping_rate_table_mode' => 0,

            // CALCULATED SHIPPING
            // ---------------------------------------
            'measurement_system' => Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::MEASUREMENT_SYSTEM_ENGLISH,

            'package_size_mode' => Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::PACKAGE_SIZE_CUSTOM_VALUE,
            'package_size_value' => 'None',
            'package_size_attribute' => '',

            'dimension_mode'   => Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::DIMENSION_NONE,
            'dimension_width_value'  => '',
            'dimension_length_value' => '',
            'dimension_depth_value'  => '',
            'dimension_width_attribute'  => '',
            'dimension_length_attribute' => '',
            'dimension_depth_attribute'  => '',

            'weight_mode' => Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::WEIGHT_NONE,
            'weight_minor' => '',
            'weight_major' => '',
            'weight_attribute' => '',

            'local_handling_cost' => NULL,
            'international_handling_cost' => NULL,
            // ---------------------------------------

            'services' => array()
        );
    }

    //########################################

    /**
     * @param bool $asArrays
     * @param string|array $columns
     * @return array
     */
    public function getAffectedListingsProducts($asArrays = true, $columns = '*')
    {
        $templateManager = Mage::getModel('M2ePro/Ebay_Template_Manager');
        $templateManager->setTemplate(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING);

        $listingsProducts = $templateManager->getAffectedOwnerObjects(
            Ess_M2ePro_Model_Ebay_Template_Manager::OWNER_LISTING_PRODUCT, $this->getId(), $asArrays, $columns
        );

        $listings = $templateManager->getAffectedOwnerObjects(
            Ess_M2ePro_Model_Ebay_Template_Manager::OWNER_LISTING, $this->getId(), false
        );

        foreach ($listings as $listing) {

            $tempListingsProducts = $listing->getChildObject()
                                            ->getAffectedListingsProductsByTemplate(
                                                Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING,
                                                $asArrays, $columns
                                            );

            foreach ($tempListingsProducts as $listingProduct) {
                if (!isset($listingsProducts[$listingProduct['id']])) {
                    $listingsProducts[$listingProduct['id']] = $listingProduct;
                }
            }
        }

        return $listingsProducts;
    }

    public function setSynchStatusNeed($newData, $oldData)
    {
        $listingsProducts = $this->getAffectedListingsProducts(true, array('id'));
        if (empty($listingsProducts)) {
            return;
        }

        $this->getResource()->setSynchStatusNeed($newData,$oldData,$listingsProducts);
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('ebay_template_shipping');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('ebay_template_shipping');
        return parent::delete();
    }

    //########################################
}