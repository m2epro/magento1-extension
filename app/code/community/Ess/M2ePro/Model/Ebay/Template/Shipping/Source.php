<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Shipping_Source
{
    /**
     * @var $_magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    protected $_magentoProduct;

    /**
     * @var $_shippingTemplateModel Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    protected $_shippingTemplateModel;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return $this
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->_magentoProduct = $magentoProduct;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->_magentoProduct;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Shipping $instance
     * @return $this
     */
    public function setShippingTemplate(Ess_M2ePro_Model_Ebay_Template_Shipping $instance)
    {
        $this->_shippingTemplateModel = $instance;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    public function getShippingTemplate()
    {
        return $this->_shippingTemplateModel;
    }

    //########################################

    /**
     * @return string
     */
    public function getCountry()
    {
        $src = $this->getShippingTemplate()->getCountrySource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Shipping::COUNTRY_MODE_CUSTOM_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    // ---------------------------------------

    /**
     * @return string
     */
    public function getPostalCode()
    {
        $src = $this->getShippingTemplate()->getPostalCodeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Shipping::ADDRESS_MODE_NONE) {
            return '';
        }

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Shipping::ADDRESS_MODE_CUSTOM_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    // ---------------------------------------

    /**
     * @return string
     */
    public function getAddress()
    {
        $src = $this->getShippingTemplate()->getAddressSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Shipping::ADDRESS_MODE_NONE) {
            return '';
        }

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Shipping::ADDRESS_MODE_CUSTOM_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    //########################################

    /**
     * @return string
     */
    public function getDispatchTime()
    {
        $src = $this->getShippingTemplate()->getDispatchTimeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Shipping::DISPATCH_TIME_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    //########################################

    /**
     * @param int $storeId
     * @return bool
     */
    public function hasAnyShippingServiceAdditionalCost($storeId)
    {
        $services = $this->getShippingTemplate()
                         ->getServices(true);

        foreach ($services as $service) {
            if (
                $service->getSource($this->getMagentoProduct())
                        ->getCostAdditional($storeId) > 0
            ) {
                return true;
            }
        }

        return false;
    }
}
