<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Shipping_Source
{
    /**
     * @var $magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProduct;

    /**
     * @var $shippingTemplateModel Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    private $shippingTemplateModel;

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
     * @param Ess_M2ePro_Model_Ebay_Template_Shipping $instance
     * @return $this
     */
    public function setShippingTemplate(Ess_M2ePro_Model_Ebay_Template_Shipping $instance)
    {
        $this->shippingTemplateModel = $instance;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    public function getShippingTemplate()
    {
        return $this->shippingTemplateModel;
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
}