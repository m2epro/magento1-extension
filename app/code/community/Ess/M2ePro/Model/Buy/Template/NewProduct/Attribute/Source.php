<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
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
     * @param Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute $instance
     * @return $this
     */
    public function setNewProductAttributeTemplate(Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute $instance)
    {
        $this->newProductAttributeTemplateModel = $instance;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute
     */
    public function getNewProductAttributeTemplate()
    {
        return $this->newProductAttributeTemplateModel;
    }

    //########################################

    /**
     * @return array
     */
    public function getValue()
    {
        $src = $this->getNewProductAttributeTemplate()->getAttributeSource();

        switch ($src['mode']) {
            case Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute::ATTRIBUTE_MODE_CUSTOM_VALUE:
                $value = $src['custom_value'];
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

    //########################################
}