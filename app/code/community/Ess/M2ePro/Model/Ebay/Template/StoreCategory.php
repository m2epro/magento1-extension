<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Resource_Ebay_Template_StoreCategory getResource()
 */
class Ess_M2ePro_Model_Ebay_Template_StoreCategory extends Ess_M2ePro_Model_Component_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Account
     */
    protected $_accountModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_StoreCategory_Source[]
     */
    protected $_storeCategorySourceModels = array();

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_StoreCategory');
    }

    //########################################

    public function loadByCategoryValue($value, $mode, $accountId)
    {
        return $this->getResource()->loadByCategoryValue($this, $value, $mode, $accountId);
    }

    //########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        /** @var Ess_M2ePro_Model_Resource_Collection_Abstract $collection */
        $collection = Mage::getModel('M2ePro/Ebay_Listing_Product')->getCollection();
        $collection->getSelect()->where(
            'template_store_category_id = ? OR template_store_category_secondary_id = ?',
            $this->getId()
        );

        if ((bool)$collection->getSize()) {
            return true;
        }

        /** @var Ess_M2ePro_Model_Resource_Collection_Abstract $collection */
        $collection = Mage::getModel('M2ePro/Ebay_Listing')->getCollection();
        $collection->getSelect()->where(
            'auto_global_adding_template_store_category_id = ? OR 
             auto_global_adding_template_store_category_secondary_id = ? OR
             auto_website_adding_template_store_category_id = ? OR
             auto_website_adding_template_store_category_secondary_id = ?',
            $this->getId()
        );

        if ((bool)$collection->getSize()) {
            return true;
        }

        /** @var Ess_M2ePro_Model_Resource_Collection_Abstract $collection */
        $collection = Mage::getModel('M2ePro/Ebay_Listing_Auto_Category_Group')->getCollection();
        $collection->getSelect()->where(
            'adding_template_store_category_id = ? OR adding_template_store_category_secondary_id = ?',
            $this->getId()
        );

        if ((bool)$collection->getSize()) {
            return true;
        }

        return false;
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->_accountModel              = null;
        $this->_storeCategorySourceModels = array();

        $this->delete();
        return true;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        if ($this->_accountModel === null) {
            $this->_accountModel = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Account', $this->getAccountId()
            );
        }

        return $this->_accountModel;
    }

    /**
     * @param Ess_M2ePro_Model_Account $instance
     */
    public function setAccount(Ess_M2ePro_Model_Account $instance)
    {
         $this->_accountModel = $instance;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Ebay_Template_StoreCategory_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->_storeCategorySourceModels[$productId])) {
            return $this->_storeCategorySourceModels[$productId];
        }

        $this->_storeCategorySourceModels[$productId] = Mage::getModel('M2ePro/Ebay_Template_StoreCategory_Source');
        $this->_storeCategorySourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->_storeCategorySourceModels[$productId]->setStoreCategoryTemplate($this);

        return $this->_storeCategorySourceModels[$productId];
    }

    //########################################

    /**
     * @return int
     */
    public function getCategoryMode()
    {
        return (int)$this->getData('category_mode');
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return (int)$this->getData('category_id');
    }

    /**
     * @return string|null
     */
    public function getCategoryAttribute()
    {
        return $this->getData('category_attribute');
    }

    /**
     * @return int
     */
    public function getAccountId()
    {
        return (int)$this->getData('account_id');
    }

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    public function getUpdateDate()
    {
        return $this->getData('update_date');
    }

    //########################################

    public function isCategoryModeNone()
    {
        return $this->getCategoryMode() === Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE;
    }

    public function isCategoryModeEbay()
    {
        return $this->getCategoryMode() === Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY;
    }

    public function isCategoryModeAttribute()
    {
        return $this->getCategoryMode() === Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE;
    }

    /**
     * @return string
     */
    public function getCategoryValue()
    {
        return $this->isCategoryModeEbay() ? $this->getCategoryId() : $this->getCategoryAttribute();
    }

    //########################################

    /**
     * @return array
     */
    public function getCategorySource()
    {
        return array(
            'mode'      => $this->getData('category_mode'),
            'value'     => $this->getData('category_id'),
            'path'      => $this->getData('category_path'),
            'attribute' => $this->getData('category_attribute')
        );
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('ebay_template_storecategory');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('ebay_template_storecategory');
        return parent::delete();
    }

    //########################################
}
