<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Resource_Ebay_Template_Shipping getResource()
 */
class Ess_M2ePro_Model_Ebay_Template_Shipping extends Ess_M2ePro_Model_Component_Abstract
{
    const COUNTRY_MODE_CUSTOM_VALUE     = 1;
    const COUNTRY_MODE_CUSTOM_ATTRIBUTE = 2;

    const POSTAL_CODE_MODE_NONE             = 0;
    const POSTAL_CODE_MODE_CUSTOM_VALUE     = 1;
    const POSTAL_CODE_MODE_CUSTOM_ATTRIBUTE = 2;

    const ADDRESS_MODE_NONE             = 0;
    const ADDRESS_MODE_CUSTOM_VALUE     = 1;
    const ADDRESS_MODE_CUSTOM_ATTRIBUTE = 2;

    const SHIPPING_TYPE_FLAT             = 0;
    const SHIPPING_TYPE_CALCULATED       = 1;
    const SHIPPING_TYPE_FREIGHT          = 2;
    const SHIPPING_TYPE_LOCAL            = 3;
    const SHIPPING_TYPE_NO_INTERNATIONAL = 4;

    const DISPATCH_TIME_MODE_VALUE     = 1;
    const DISPATCH_TIME_MODE_ATTRIBUTE = 2;

    const SHIPPING_RATE_TABLE_ACCEPT_MODE     = 1;
    const SHIPPING_RATE_TABLE_IDENTIFIER_MODE = 2;

    const CROSS_BORDER_TRADE_NONE           = 0;
    const CROSS_BORDER_TRADE_NORTH_AMERICA  = 1;
    const CROSS_BORDER_TRADE_UNITED_KINGDOM = 2;

    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    protected $_marketplaceModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated
     */
    protected $_calculatedShippingModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Shipping_Source[]
     */
    protected $_shippingSourceModels = array();

    /** @var Ess_M2ePro_Model_ActiveRecord_Factory */
    protected $_activeRecordFactory;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Shipping');
        $this->_activeRecordFactory = Mage::getSingleton('M2ePro/ActiveRecord_Factory');
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
                ->addFieldToFilter('template_shipping_id', $this->getId())
                ->getSize() ||
            (bool)Mage::getModel('M2ePro/Ebay_Listing_Product')
                ->getCollection()
                ->addFieldToFilter(
                    'template_shipping_mode',
                    Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE
                )
                ->addFieldToFilter('template_shipping_id', $this->getId())
                ->getSize();
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $calculatedShippingObject = $this->getCalculatedShipping();
        if ($calculatedShippingObject !== null) {
            $calculatedShippingObject->deleteInstance();
        }

        $services = $this->getServices(true);
        foreach ($services as $service) {
            $service->deleteInstance();
        }

        $this->_marketplaceModel = null;
        $this->_calculatedShippingModel = null;
        $this->_shippingSourceModels = array();

        $this->delete();

        return true;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        if ($this->_marketplaceModel === null) {
            $this->_marketplaceModel = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Marketplace',
                $this->getMarketplaceId()
            );
        }

        return $this->_marketplaceModel;
    }

    /**
     * @param Ess_M2ePro_Model_Marketplace $instance
     */
    public function setMarketplace(Ess_M2ePro_Model_Marketplace $instance)
    {
        $this->_marketplaceModel = $instance;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->_shippingSourceModels[$productId])) {
            return $this->_shippingSourceModels[$productId];
        }

        $this->_shippingSourceModels[$productId] = Mage::getModel('M2ePro/Ebay_Template_Shipping_Source');
        $this->_shippingSourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->_shippingSourceModels[$productId]->setShippingTemplate($this);

        return $this->_shippingSourceModels[$productId];
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated
     */
    public function getCalculatedShipping()
    {
        if ($this->_calculatedShippingModel === null) {
            try {
                $this->_calculatedShippingModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_Shipping_Calculated',
                    $this->getId(),
                    null,
                    array('template')
                );

                $this->_calculatedShippingModel->setShippingTemplate($this);
            } catch (Exception $exception) {
                return $this->_calculatedShippingModel;
            }
        }

        return $this->_calculatedShippingModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated $instance
     */
    public function setCalculatedShipping(Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated $instance)
    {
        $this->_calculatedShippingModel = $instance;
    }

    //########################################

    /**
     * @param bool $asObjects
     * @return array|Ess_M2ePro_Model_Abstract[]
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getServices($asObjects = false)
    {
        $collection = $this->_activeRecordFactory->getObjectCollection('Ebay_Template_Shipping_Service');
        $collection->addFieldToFilter('template_shipping_id', $this->getId());
        $collection->setOrder('priority', Varien_Data_Collection::SORT_ORDER_ASC);

        /** @var $service Ess_M2ePro_Model_Ebay_Template_Shipping_Service */
        foreach ($collection->getItems() as $service) {
            $service->setShippingTemplate($this);
        }

        if (!$asObjects) {
            $result = $collection->toArray();

            return $result['items'];
        }

        return $collection->getItems();
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

    /**
     * @return array
     */
    public function getCountryAttributes()
    {
        $attributes = array();
        $src = $this->getCountrySource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Shipping::COUNTRY_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
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

    /**
     * @return array
     */
    public function getPostalCodeAttributes()
    {
        $attributes = array();
        $src = $this->getPostalCodeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Shipping::POSTAL_CODE_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
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

    /**
     * @return array
     */
    public function getAddressAttributes()
    {
        $attributes = array();
        $src = $this->getAddressSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Shipping::ADDRESS_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
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
     * @param Ess_M2ePro_Model_Account $account
     *
     * @return bool
     */
    public function isLocalShippingRateTableEnabled(Ess_M2ePro_Model_Account $account)
    {
        try {
            $rateTable = $this->getRateTable('local', $account);
        } catch (Ess_M2ePro_Model_Exception_Logic $e) {
            return null;
        }

        return !empty($rateTable['value']) ? (bool)$rateTable['value'] : null;
    }

    /**
     * @param Ess_M2ePro_Model_Account $account
     *
     * @return int
     */
    public function getLocalShippingRateTableMode(Ess_M2ePro_Model_Account $account)
    {
        $rateTable = $this->getLocalShippingRateTable($account);

        return $rateTable['mode'];
    }

    /**
     * @param Ess_M2ePro_Model_Account $account
     *
     * @return mixed
     */
    public function getLocalShippingRateTableId(Ess_M2ePro_Model_Account $account)
    {
        $rateTable = $this->getLocalShippingRateTable($account);

        return $rateTable['value'];
    }

    /**
     * @param Ess_M2ePro_Model_Account $account
     *
     * @return bool
     */
    public function getLocalShippingRateTable(Ess_M2ePro_Model_Account $account)
    {
        return $this->getRateTable('local', $account);
    }

    /**
     * @param Ess_M2ePro_Model_Account $account
     *
     * @return bool
     */
    public function isInternationalShippingRateTableEnabled(Ess_M2ePro_Model_Account $account)
    {
        try {
            $rateTable = $this->getRateTable('international', $account);
        } catch (Ess_M2ePro_Model_Exception_Logic $e) {
            return false;
        }

        return !empty($rateTable['value']) ? (bool)$rateTable['value'] : null;
    }

    /**
     * @param Ess_M2ePro_Model_Account $account
     *
     * @return int
     */
    public function getInternationalShippingRateTableMode(Ess_M2ePro_Model_Account $account)
    {
        $rateTable = $this->getInternationalShippingRateTable($account);

        return $rateTable['mode'];
    }

    /**
     * @param Ess_M2ePro_Model_Account $account
     *
     * @return mixed
     */
    public function getInternationalShippingRateTableId(Ess_M2ePro_Model_Account $account)
    {
        $rateTable = $this->getInternationalShippingRateTable($account);

        return $rateTable['value'];
    }

    /**
     * @param Ess_M2ePro_Model_Account $account
     *
     * @return bool
     */
    public function getInternationalShippingRateTable(Ess_M2ePro_Model_Account $account)
    {
        return $this->getRateTable('international', $account);
    }

    /**
     * @param                          $type
     * @param Ess_M2ePro_Model_Account $account
     *
     * @return bool
     */
    protected function getRateTable($type, Ess_M2ePro_Model_Account $account)
    {
        $rateTables = $this->getSettings($type . '_shipping_rate_table');

        foreach ($rateTables as $accountId => $rateTableData) {
            if ($account->getId() == $accountId) {
                return $rateTableData;
            }
        }

        throw new Ess_M2ePro_Model_Exception_Logic(
            Mage::helper('M2ePro')->__(
                'Domestic or International Shipping Rate Table data is not found for this account. 
                Make sure to <a href="%url%" target="_blank">download Rate Tables from eBay</a> 
                in the M2E Pro Shipping Policy.',
                Mage::helper("M2ePro/Module_Support")->getDocumentationUrl(
                    null,
                    null,
                    "set-up-shipping-policy#6e8b3db9007740e1a87f1d2a26209a10"
                )
            )
        );
    }

    //########################################

    /**
     * @return int
     */
    public function getDispatchTimeMode()
    {
        return (int)$this->getData('dispatch_time_mode');
    }

    public function getDispatchTimeValue()
    {
        return $this->getData('dispatch_time_value');
    }

    public function getDispatchTimeAttribute()
    {
        return $this->getData('dispatch_time_attribute');
    }

    /**
     * @return array
     */
    public function getDispatchTimeSource()
    {
        return array(
            'mode'      => $this->getDispatchTimeMode(),
            'value'     => $this->getDispatchTimeValue(),
            'attribute' => $this->getDispatchTimeAttribute()
        );
    }

    /**
     * @return array
     */
    public function getDispatchTimeAttributes()
    {
        $attributes = array();
        $src = $this->getDispatchTimeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Shipping::DISPATCH_TIME_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
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
    public function isLocalShippingDiscountPromotionalEnabled()
    {
        return (bool)$this->getData('local_shipping_discount_promotional_mode');
    }

    public function getLocalShippingDiscountCombinedProfileId($accountId)
    {
        $data = $this->getData('local_shipping_discount_combined_profile_id');

        if ($data === null) {
            return null;
        }

        $data = Mage::helper('M2ePro')->jsonDecode($data);

        return !isset($data[$accountId]) ? null : $data[$accountId];
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
    public function isInternationalShippingDiscountPromotionalEnabled()
    {
        return (bool)$this->getData('international_shipping_discount_promotional_mode');
    }

    public function getInternationalShippingDiscountCombinedProfileId($accountId)
    {
        $data = $this->getData('international_shipping_discount_combined_profile_id');

        if ($data === null) {
            return null;
        }

        $data = Mage::helper('M2ePro')->jsonDecode($data);

        return !isset($data[$accountId]) ? null : $data[$accountId];
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getExcludedLocations()
    {
        $excludedLocations = $this->getData('excluded_locations');
        is_string($excludedLocations) && $excludedLocations = Mage::helper('M2ePro')->jsonDecode($excludedLocations);

        return is_array($excludedLocations) ? $excludedLocations : array();
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

    /**
     * @param \Ess_M2ePro_Model_Account $account
     * @return void
     * @throws \Ess_M2ePro_Model_Exception_Logic
     */
    public function deleteShippingRateTables(Ess_M2ePro_Model_Account $account)
    {
        $this->deleteShippingRateTable($account->getId(), 'local_shipping_rate_table');
        $this->deleteShippingRateTable($account->getId(), 'international_shipping_rate_table');
    }

    /**
     * @param int|string $accountId
     * @param string $settingsField
     * @return void
     * @throws \Ess_M2ePro_Model_Exception_Logic
     */
    private function deleteShippingRateTable($accountId, $settingsField)
    {
        $rateTables = $this->getSettings($settingsField);
        unset($rateTables[$accountId]);
        $this->setSettings($settingsField, $rateTables);
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
