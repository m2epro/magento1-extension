<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
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

    // ########################################

    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->magentoProduct = $magentoProduct;
        return $this;
    }

    public function getMagentoProduct()
    {
        return $this->magentoProduct;
    }

    // ----------------------------------------

    public function setShippingServiceTemplate(Ess_M2ePro_Model_Ebay_Template_Shipping_Service $instance)
    {
        $this->shippingServiceTemplateModel = $instance;
        return $this;
    }

    public function getShippingServiceTemplate()
    {
        return $this->shippingServiceTemplateModel;
    }

    // ########################################

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

    // ########################################
}