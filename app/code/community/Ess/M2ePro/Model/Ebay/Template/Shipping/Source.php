<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
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

    public function setShippingTemplate(Ess_M2ePro_Model_Ebay_Template_Shipping $instance)
    {
        $this->shippingTemplateModel = $instance;
        return $this;
    }

    public function getShippingTemplate()
    {
        return $this->shippingTemplateModel;
    }

    // ########################################

    public function getCountry()
    {
        $src = $this->getShippingTemplate()->getCountrySource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Shipping::COUNTRY_MODE_CUSTOM_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    // ----------------------------------------

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

    // ----------------------------------------

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

    // ########################################
}