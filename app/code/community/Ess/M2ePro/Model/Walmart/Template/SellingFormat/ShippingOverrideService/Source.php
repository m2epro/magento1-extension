<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverrideService_Source
{
    /**
     * @var $magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProduct = null;

    /**
     * @var Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverrideService
     */
    private $sellingFormatShippingServiceTemplateModel = null;

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
     * @param Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverrideService $instance
     * @return $this
     */
    public function setSellingFormatShipingServiceTemplate(
        Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverrideService $instance)
    {
        $this->sellingFormatShippingServiceTemplateModel = $instance;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverrideService
     */
    public function getSellingFormatShippingServiceTemplate()
    {
        return $this->sellingFormatShippingServiceTemplateModel;
    }

    //########################################

    /**
     * @return float
     */
    public function getCost()
    {
        $result = 0;

        switch ($this->getSellingFormatShippingServiceTemplate()->getCostMode()) {
            case Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverrideService::COST_MODE_FREE:
                $result = 0;
                break;
            case Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverrideService::COST_MODE_CUSTOM_VALUE:
                $result = $this->getSellingFormatShippingServiceTemplate()->getCostValue();
                break;
            case Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverrideService::COST_MODE_CUSTOM_ATTRIBUTE:
                $result = $this->getMagentoProduct()->getAttributeValue(
                    $this->getSellingFormatShippingServiceTemplate()->getCostAttribute()
                );
                break;
        }

        is_string($result) && $result = str_replace(',','.',$result);

        return round((float)$result,2);
    }

    //########################################
}