<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverride_Source
{
    /**
     * @var $_magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    protected $_magentoProduct = null;

    /**
     * @var Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverride
     */
    protected $_sellingFormatShippingOverrideTemplateModel = null;

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
     * @param Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverride $instance
     * @return $this
     */
    public function setSellingFormatShippingOverrideTemplate(
        Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverride $instance
    ) {
        $this->_sellingFormatShippingOverrideTemplateModel = $instance;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverride
     */
    public function getSellingFormatShippingOverrideTemplate()
    {
        return $this->_sellingFormatShippingOverrideTemplateModel;
    }

    //########################################

    /**
     * @return float
     */
    public function getCost()
    {
        $result = 0;

        switch ($this->getSellingFormatShippingOverrideTemplate()->getCostMode()) {
            case Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverride::COST_MODE_FREE:
                $result = 0;
                break;
            case Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverride::COST_MODE_CUSTOM_VALUE:
                $result = $this->getSellingFormatShippingOverrideTemplate()->getCostValue();
                break;
            case Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverride::COST_MODE_CUSTOM_ATTRIBUTE:
                $result = $this->getMagentoProduct()->getAttributeValue(
                    $this->getSellingFormatShippingOverrideTemplate()->getCostAttribute()
                );
                break;
        }

        is_string($result) && $result = str_replace(',', '.', $result);

        return round((float)$result, 2);
    }

    //########################################
}
