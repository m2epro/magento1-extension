<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Category_Source
{
    /**
     * @var $magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProduct = null;

    /**
     * @var $categoryTemplateModel Ess_M2ePro_Model_Ebay_Template_Category
     */
    private $categoryTemplateModel = null;

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
     * @param Ess_M2ePro_Model_Ebay_Template_Category $instance
     * @return $this
     */
    public function setCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_Category $instance)
    {
        $this->categoryTemplateModel = $instance;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     */
    public function getCategoryTemplate()
    {
        return $this->categoryTemplateModel;
    }

    //########################################

    /**
     * @return string
     */
    public function getMainCategory()
    {
        $src = $this->getCategoryTemplate()->getCategoryMainSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    //########################################
}