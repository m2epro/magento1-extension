<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Mysql4_Amazon_Template_ProductTaxCode getResource()
 */
class Ess_M2ePro_Model_Amazon_Template_ProductTaxCode extends Ess_M2ePro_Model_Component_Abstract
{
    const PRODUCT_TAX_CODE_MODE_VALUE     = 1;
    const PRODUCT_TAX_CODE_MODE_ATTRIBUTE = 2;

    /**
     * @var Ess_M2ePro_Model_Amazon_Template_ProductTaxCode_Source[]
     */
    private $productTaxCodeSourceModels = array();

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Template_ProductTaxCode');
    }

    //########################################

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Amazon_Listing_Product')
            ->getCollection()
            ->addFieldToFilter('template_product_tax_code_id', $this->getId())
            ->getSize();
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->delete();
        return true;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Amazon_Template_ProductTaxCode_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $id = $magentoProduct->getProductId();

        if (!empty($this->productTaxCodeSourceModels[$id])) {
            return $this->productTaxCodeSourceModels[$id];
        }

        $this->productTaxCodeSourceModels[$id] =
            Mage::getModel('M2ePro/Amazon_Template_ProductTaxCode_Source');

        $this->productTaxCodeSourceModels[$id]->setMagentoProduct($magentoProduct);
        $this->productTaxCodeSourceModels[$id]->setProductTaxCodeTemplate($this);

        return $this->productTaxCodeSourceModels[$id];
    }

    //########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    // ---------------------------------------

    public function getProductTaxCodeMode()
    {
        return (int)$this->getData('product_tax_code_mode');
    }

    public function isProductTaxCodeModeValue()
    {
        return $this->getProductTaxCodeMode() == self::PRODUCT_TAX_CODE_MODE_VALUE;
    }

    public function isProductTaxCodeModeAttribute()
    {
        return $this->getProductTaxCodeMode() == self::PRODUCT_TAX_CODE_MODE_ATTRIBUTE;
    }

    // ---------------------------------------

    public function getProductTaxCodeValue()
    {
        return $this->getData('product_tax_code_value');
    }

    public function getProductTaxCodeAttribute()
    {
        return $this->getData('product_tax_code_attribute');
    }

    // ---------------------------------------

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    public function getUpdateDate()
    {
        return $this->getData('update_date');
    }

    //########################################

    public function getProductTaxCodeAttributes()
    {
        $attributes = array();

        if ($this->isProductTaxCodeModeAttribute()) {
            $attributes[] = $this->getProductTaxCodeAttribute();
        }

        return $attributes;
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('amazon_template_producttaxcode');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('amazon_template_producttaxcode');
        return parent::delete();
    }

    //########################################
}