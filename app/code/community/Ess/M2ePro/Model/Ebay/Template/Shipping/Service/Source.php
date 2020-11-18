<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Shipping_Service_Source
{
    /**
     * @var $_magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    protected $_magentoProduct = null;

    /**
     * @var $_shippingServiceTemplateModel Ess_M2ePro_Model_Ebay_Template_Shipping_Service
     */
    protected $_shippingServiceTemplateModel = null;

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
     * @param Ess_M2ePro_Model_Ebay_Template_Shipping_Service $instance
     * @return $this
     */
    public function setShippingServiceTemplate(Ess_M2ePro_Model_Ebay_Template_Shipping_Service $instance)
    {
        $this->_shippingServiceTemplateModel = $instance;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping_Service
     */
    public function getShippingServiceTemplate()
    {
        return $this->_shippingServiceTemplateModel;
    }

    //########################################

    /**
     * @param null $storeForConvertingAttributeTypePrice
     * @return float
     */
    public function getCost($storeForConvertingAttributeTypePrice = null)
    {
        $result = 0;

        switch ($this->getShippingServiceTemplate()->getCostMode()) {
            case Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_FREE:
                $result = 0;
                break;
            case Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_CUSTOM_VALUE:
                $result = $this->getShippingServiceTemplate()->getCostValue();
                break;
            case Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_CUSTOM_ATTRIBUTE:
                $result = $this->getMagentoProductAttributeValue(
                    $this->getShippingServiceTemplate()->getCostValue(),
                    $storeForConvertingAttributeTypePrice
                );
                break;
        }

        is_string($result) && $result = str_replace(',', '.', $result);

        return round((float)$result, 2);
    }

    /**
     * @param null $storeForConvertingAttributeTypePrice
     * @return float
     */
    public function getCostAdditional($storeForConvertingAttributeTypePrice = null)
    {
        $result = 0;

        switch ($this->getShippingServiceTemplate()->getCostMode()) {
            case Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_FREE:
                $result = 0;
                break;
            case Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_CUSTOM_VALUE:
                $result = $this->getShippingServiceTemplate()->getCostAdditionalValue();
                break;
            case Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_CUSTOM_ATTRIBUTE:
                $result = $this->getMagentoProductAttributeValue(
                    $this->getShippingServiceTemplate()->getCostAdditionalValue(),
                    $storeForConvertingAttributeTypePrice
                );
                break;
        }

        is_string($result) && $result = str_replace(',', '.', $result);

        return round((float)$result, 2);
    }

    // ---------------------------------------

    protected function getMagentoProductAttributeValue($attributeCode, $store)
    {
        if ($store === null) {
            return $this->getMagentoProduct()->getAttributeValue($attributeCode);
        }

        $currency = $this->getShippingServiceTemplate()
                         ->getShippingTemplate()
                         ->getMarketplace()
                         ->getChildObject()
                         ->getCurrency();

        return Mage::helper('M2ePro/Magento_Attribute')->convertAttributeTypePriceFromStoreToMarketplace(
            $this->getMagentoProduct(),
            $attributeCode,
            $currency,
            $store
        );
    }

    //########################################
}
