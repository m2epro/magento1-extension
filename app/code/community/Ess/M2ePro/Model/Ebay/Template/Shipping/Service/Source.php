<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Shipping_Service_Source
{
    /**
     * @var $magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProduct = null;

    /**
     * @var $shippingServiceTemplateModel Ess_M2ePro_Model_Ebay_Template_Shipping_Service
     */
    private $shippingServiceTemplateModel = null;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return $this
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->magentoProduct = $magentoProduct;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->magentoProduct;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Shipping_Service $instance
     * @return $this
     */
    public function setShippingServiceTemplate(Ess_M2ePro_Model_Ebay_Template_Shipping_Service $instance)
    {
        $this->shippingServiceTemplateModel = $instance;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping_Service
     */
    public function getShippingServiceTemplate()
    {
        return $this->shippingServiceTemplateModel;
    }

    //########################################

    /**
     * @return float
     */
    public function getCost()
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
                $result = $this->getMagentoProduct()->getAttributeValue(
                    $this->getShippingServiceTemplate()->getCostValue()
                );
                break;
        }

        is_string($result) && $result = str_replace(',','.',$result);

        return round((float)$result,2);
    }

    /**
     * @return float
     */
    public function getCostAdditional()
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
                $result = $this->getMagentoProduct()->getAttributeValue(
                    $this->getShippingServiceTemplate()->getCostAdditionalValue()
                );
                break;
        }

        is_string($result) && $result = str_replace(',','.',$result);

        return round((float)$result,2);
    }

    /**
     * @return float
     */
    public function getCostSurcharge()
    {
        $result = 0;

        switch ($this->getShippingServiceTemplate()->getCostMode()) {
            case Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_FREE:
                $result = 0;
                break;
            case Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_CUSTOM_VALUE:
                $result = $this->getShippingServiceTemplate()->getCostSurchargeValue();
                break;
            case Ess_M2ePro_Model_Ebay_Template_Shipping_Service::COST_MODE_CUSTOM_ATTRIBUTE:
                $result = $this->getMagentoProduct()->getAttributeValue(
                    $this->getShippingServiceTemplate()->getCostSurchargeValue()
                );
                break;
        }

        is_string($result) && $result = str_replace(',','.',$result);

        return round((float)$result,2);
    }

    //########################################
}