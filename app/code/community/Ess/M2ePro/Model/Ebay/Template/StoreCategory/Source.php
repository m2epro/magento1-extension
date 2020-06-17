<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_StoreCategory_Source
{
    /**
     * @var Ess_M2ePro_Model_Magento_Product
     */
    protected $_magentoProduct = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_StoreCategory
     */
    protected $_storeCategoryTemplateModel = null;

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
     * @param Ess_M2ePro_Model_Ebay_Template_StoreCategory $instance
     * @return $this
     */
    public function setStoreCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_StoreCategory $instance)
    {
        $this->_storeCategoryTemplateModel = $instance;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_StoreCategory
     */
    public function getStoreCategoryTemplate()
    {
        return $this->_storeCategoryTemplateModel;
    }

    //########################################

    /**
     * @return int|string
     */
    public function getCategoryId()
    {
        $src = $this->getStoreCategoryTemplate()->getCategorySource();

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
