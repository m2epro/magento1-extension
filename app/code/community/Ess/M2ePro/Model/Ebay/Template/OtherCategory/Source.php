<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_OtherCategory_Source
{
    /**
     * @var $_magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    protected $_magentoProduct = null;

    /**
     * @var $_otherCategoryTemplateModel Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    protected $_otherCategoryTemplateModel = null;

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
     * @param Ess_M2ePro_Model_Ebay_Template_OtherCategory $instance
     * @return $this
     */
    public function setOtherCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_OtherCategory $instance)
    {
        $this->_otherCategoryTemplateModel = $instance;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    public function getOtherCategoryTemplate()
    {
        return $this->_otherCategoryTemplateModel;
    }

    //########################################

    /**
     * @return int|string
     */
    public function getSecondaryCategory()
    {
        $src = $this->getOtherCategoryTemplate()->getCategorySecondarySource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE) {
            return 0;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    // ---------------------------------------

    /**
     * @return int|string
     */
    public function getStoreCategoryMain()
    {
        $src = $this->getOtherCategoryTemplate()->getStoreCategoryMainSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE) {
            return 0;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    /**
     * @return int|string
     */
    public function getStoreCategorySecondary()
    {
        $src = $this->getOtherCategoryTemplate()->getStoreCategorySecondarySource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE) {
            return 0;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    //########################################
}
