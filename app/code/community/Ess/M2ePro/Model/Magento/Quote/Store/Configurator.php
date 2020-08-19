<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Quote_Store_Configurator
{
    /** @var $_quote Mage_Sales_Model_Quote */
    protected $_quote = null;

    /** @var $proxy Ess_M2ePro_Model_Order_Proxy */
    protected $_proxyOrder = null;

    /** @var $_taxConfig Mage_Tax_Model_Config */
    protected $_taxConfig = null;

    //########################################

    public function init(Mage_Sales_Model_Quote $quote, Ess_M2ePro_Model_Order_Proxy $proxyOrder)
    {
        // we need clear singleton stored instances, because magento caches tax rates in private properties
        Mage::unregister('_singleton/tax/calculation');
        Mage::unregister('_resource_singleton/tax/calculation');
        Mage::unregister('_singleton/sales/quote_address_total_collector');

        $this->_quote      = $quote;
        $this->_proxyOrder = $proxyOrder;
        $this->_taxConfig  = Mage::getSingleton('tax/config');
    }

    //########################################

    /**
     * @return array
     */
    public function getOriginalStoreConfig()
    {
        $keys = array(
            Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX,
            Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX,
            Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS,
            Mage_Tax_Model_Config::CONFIG_XML_PATH_BASED_ON,
            Mage_Customer_Model_Group::XML_PATH_DEFAULT_ID,
            Mage_Weee_Helper_Data::XML_PATH_FPT_ENABLED,
            Mage_Shipping_Model_Config::XML_PATH_ORIGIN_COUNTRY_ID,
            Mage_Shipping_Model_Config::XML_PATH_ORIGIN_REGION_ID,
            Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE
        );

        $config = array();

        foreach ($keys as $key) {
            $config[$key] = $this->getStoreConfig($key);
        }

        return $config;
    }

    //########################################

    public function prepareStoreConfigForOrder()
    {
        // catalog prices
        // ---------------------------------------
        // reset flag, use store config instead
        $this->_taxConfig->setNeedUsePriceExcludeTax(false);
        $this->setStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, $this->isPriceIncludesTax());
        // ---------------------------------------

        // shipping prices
        // ---------------------------------------
        $isShippingPriceIncludesTax = $this->isShippingPriceIncludesTax();
        if (method_exists($this->_taxConfig, 'setShippingPriceIncludeTax')) {
            $this->_taxConfig->setShippingPriceIncludeTax($isShippingPriceIncludesTax);
        } else {
            $this->setStoreConfig(
                Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX, $isShippingPriceIncludesTax
            );
        }

        // ---------------------------------------

        // Fixed Product Tax settings
        // ---------------------------------------
        if ($this->_proxyOrder->isTaxModeChannel() ||
            ($this->_proxyOrder->isTaxModeMixed() && $this->_proxyOrder->hasTax())
        ) {
            $this->setStoreConfig(Mage_Weee_Helper_Data::XML_PATH_FPT_ENABLED, false);
        }

        // ---------------------------------------

        // store origin address
        // ---------------------------------------
        $this->setStoreConfig(Mage_Shipping_Model_Config::XML_PATH_ORIGIN_COUNTRY_ID, $this->getOriginCountryId());
        $this->setStoreConfig(Mage_Shipping_Model_Config::XML_PATH_ORIGIN_REGION_ID, $this->getOriginRegionId());
        $this->setStoreConfig(Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE, $this->getOriginPostcode());
        // ---------------------------------------

        // ---------------------------------------
        $this->setStoreConfig(Mage_Customer_Model_Group::XML_PATH_DEFAULT_ID, $this->getDefaultCustomerGroupId());
        $this->setStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_BASED_ON, $this->getTaxCalculationBasedOn());
        // ---------------------------------------

        // store shipping tax class
        // ---------------------------------------
        $this->setStoreConfig(
            Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $this->getShippingTaxClassId()
        );
        // ---------------------------------------
    }

    //########################################

    public function isPriceIncludesTax()
    {
        if ($this->_proxyOrder->isProductPriceIncludeTax() !== null) {
            return $this->_proxyOrder->isProductPriceIncludeTax();
        }

        return (bool)$this->getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX);
    }

    public function isShippingPriceIncludesTax()
    {
        if ($this->_proxyOrder->isShippingPriceIncludeTax() !== null) {
            return $this->_proxyOrder->isShippingPriceIncludeTax();
        }

        return (bool)$this->getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX);
    }

    //########################################

    public function getShippingTaxClassId()
    {
        $proxyOrder = $this->_proxyOrder;
        $hasRatesForCountry = Mage::getSingleton('M2ePro/Magento_Tax_Helper')
            ->hasRatesForCountry($this->_quote->getShippingAddress()->getCountryId());
        $storeShippingTaxRate = Mage::getSingleton('M2ePro/Magento_Tax_Helper')
            ->getStoreShippingTaxRate($this->getStore());
        $calculationBasedOnOrigin = Mage::getSingleton('M2ePro/Magento_Tax_Helper')
            ->isCalculationBasedOnOrigin($this->getStore());
        $shippingPriceTaxRate = $proxyOrder->getShippingPriceTaxRate();

        $isTaxSourceChannel = $proxyOrder->isTaxModeChannel()
            || ($proxyOrder->isTaxModeMixed() && $shippingPriceTaxRate > 0);

        if ($proxyOrder->isTaxModeNone()
            || ($isTaxSourceChannel && $shippingPriceTaxRate <= 0)
            || ($proxyOrder->isTaxModeMagento() && !$hasRatesForCountry && !$calculationBasedOnOrigin)
        ) {
            return Ess_M2ePro_Model_Magento_Product::TAX_CLASS_ID_NONE;
        }

        if ($proxyOrder->isTaxModeMagento()
            || $proxyOrder->getShippingPriceTaxRate() <= 0
            || $shippingPriceTaxRate == $storeShippingTaxRate
        ) {
            return $this->_taxConfig->getShippingTaxClass($this->getStore());
        }

        // Create tax rule according to channel tax rate
        // ---------------------------------------
        /** @var $taxRuleBuilder Ess_M2ePro_Model_Magento_Tax_Rule_Builder */
        $taxRuleBuilder = Mage::getModel('M2ePro/Magento_Tax_Rule_Builder');
        $taxRuleBuilder->buildShippingTaxRule(
            $shippingPriceTaxRate,
            $this->_quote->getShippingAddress()->getCountryId(),
            $this->_quote->getCustomerTaxClassId()
        );

        $taxRule = $taxRuleBuilder->getRule();
        $productTaxClasses = $taxRule->getProductTaxClasses();
        // ---------------------------------------

        return array_shift($productTaxClasses);
    }

    //########################################

    /**
     * @return string|null
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getOriginCountryId()
    {
        if ($this->shouldReturnConfigValue()) {
            return $this->getStoreConfig(Mage_Shipping_Model_Config::XML_PATH_ORIGIN_COUNTRY_ID);
        }

        if ($this->shouldReturnEmptyValue()) {
            return '';
        }

        return $this->_quote->getShippingAddress()->getCountryId();
    }

    /**
     * @return mixed|string|null
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getOriginRegionId()
    {
        if ($this->shouldReturnConfigValue()) {
            return $this->getStoreConfig(Mage_Shipping_Model_Config::XML_PATH_ORIGIN_REGION_ID);
        }

        if ($this->shouldReturnEmptyValue()) {
            return '';
        }

        return $this->_quote->getShippingAddress()->getRegionId();
    }

    /**
     * @return string|null
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getOriginPostcode()
    {
        if ($this->shouldReturnConfigValue()) {
            return $this->getStoreConfig(Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE);
        }

        if ($this->shouldReturnEmptyValue()) {
            return '';
        }

        return $this->_quote->getShippingAddress()->getPostcode();
    }

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function shouldReturnConfigValue()
    {
        return $this->_proxyOrder->isTaxModeMagento() ||
               ($this->_proxyOrder->isTaxModeMixed() && !$this->_proxyOrder->hasTax());
    }

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function shouldReturnEmptyValue()
    {
        return $this->_proxyOrder->isTaxModeNone() ||
               ($this->_proxyOrder->isTaxModeChannel() && !$this->_proxyOrder->hasTax());
    }

    //########################################

    protected function getDefaultCustomerGroupId()
    {
        $defaultCustomerGroupId = $this->getStoreConfig(Mage_Customer_Model_Group::XML_PATH_DEFAULT_ID);

        if ($this->_proxyOrder->isTaxModeMagento()) {
            return $defaultCustomerGroupId;
        }

        $currentCustomerTaxClass = Mage::getSingleton('tax/calculation')->getDefaultCustomerTaxClass($this->getStore());
        $quoteCustomerTaxClass = $this->_quote->getCustomerTaxClassId();

        if ($currentCustomerTaxClass == $quoteCustomerTaxClass) {
            return $defaultCustomerGroupId;
        }

        // ugliest hack ever!
        // we have to remove exist singleton instance from the Mage registry
        // because Mage_Tax_Model_Calculation::getDefaultCustomerTaxClass() method stores the customer tax class
        // after the first call in protected variable and then it doesn't care what store was given to it
        Mage::unregister('_singleton/tax/calculation');

        // default customer tax class depends on default customer group
        // so we override store setting for this with the customer group from the quote
        // this is done to make store & address tax requests equal
        return $this->_quote->getCustomerGroupId();
    }

    //########################################

    public function getTaxCalculationBasedOn()
    {
        $basedOn = $this->getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_BASED_ON);

        if ($this->_proxyOrder->isTaxModeMagento()) {
            return $basedOn;
        }

        if ($this->_proxyOrder->isTaxModeMixed() && !$this->_proxyOrder->hasTax()) {
            return $basedOn;
        }

        return 'shipping';
    }

    //########################################

    protected function getStore()
    {
        return $this->_quote->getStore();
    }

    // ---------------------------------------

    protected function setStoreConfig($key, $value)
    {
        $this->getStore()->setConfig($key, $value);
    }

    protected function getStoreConfig($key)
    {
        return $this->getStore()->getConfig($key);
    }

    //########################################
}