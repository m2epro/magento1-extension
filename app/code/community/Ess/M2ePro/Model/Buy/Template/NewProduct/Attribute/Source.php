<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute_Source
{
    /**
     * @var $magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProduct = null;

    /**
     * @var $newProductSpecificTemplateModel Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute
     */
    private $newProductAttributeTemplateModel = null;

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

    public function setNewProductAttributeTemplate(Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute $instance)
    {
        $this->newProductAttributeTemplateModel = $instance;
        return $this;
    }

    public function getNewProductAttributeTemplate()
    {
        return $this->newProductAttributeTemplateModel;
    }

    // ########################################

    public function getValue()
    {
        $src = $this->getNewProductAttributeTemplate()->getAttributeSource();

        switch ($src['mode']) {
            case Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_CUSTOM_VALUE:
                $value = $src['custom_value'];
                //$value = str_replace(',','^',$src['custom_value']);
                break;

            case Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_CUSTOM_ATTRIBUTE:
                $value = $this->getMagentoProduct()->getAttributeValue($src['custom_attribute']);
                $value = str_replace(',','^',$value);
                break;

            case Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_RECOMMENDED_VALUE:
                $value = $src['recommended_value'];
                is_array($value) && $value = implode('^',$value);
                break;

            default:
                $value = '';
                break;
        }

        return array($src['name'] => $value);
    }

    // ########################################
}