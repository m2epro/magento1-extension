<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Template_Shipping_Source
{
    /**
     * @var Ess_M2ePro_Model_Magento_Product $magentoProduct
     */
    private $magentoProduct = null;

    /**
     * @var Ess_M2ePro_Model_Amazon_Template_Shipping $shippingTemplateModel
     */
    private $shippingTemplateModel = null;

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
     * @param Ess_M2ePro_Model_Amazon_Template_Shipping $instance
     * @return $this
     */
    public function setShippingTemplate(Ess_M2ePro_Model_Amazon_Template_Shipping $instance)
    {
        $this->shippingTemplateModel = $instance;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Shipping
     */
    public function getShippingTemplate()
    {
        return $this->shippingTemplateModel;
    }

    //########################################

    /**
     * @return string
     */
    public function getTemplateName()
    {
        $result = '';

        switch ($this->getShippingTemplate()->getTemplateNameMode()) {
            case Ess_M2ePro_Model_Amazon_Template_Shipping::TEMPLATE_NAME_VALUE:
                $result = $this->getShippingTemplate()->getTemplateNameValue();
                break;

            case Ess_M2ePro_Model_Amazon_Template_Shipping::TEMPLATE_NAME_ATTRIBUTE:
                $result = $this->getMagentoProduct()->getAttributeValue(
                    $this->getShippingTemplate()->getTemplateNameAttribute()
                );
                break;
        }

        return $result;
    }

    //########################################
}