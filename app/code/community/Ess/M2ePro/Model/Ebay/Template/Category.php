<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Resource_Ebay_Template_Category getResource()
 */
class Ess_M2ePro_Model_Ebay_Template_Category extends Ess_M2ePro_Model_Component_Abstract
{
    const CATEGORY_MODE_NONE       = 0;
    const CATEGORY_MODE_EBAY       = 1;
    const CATEGORY_MODE_ATTRIBUTE  = 2;

    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    protected $_marketplaceModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Category_Source[]
     */
    protected $_categorySourceModels = array();

    /** @var Ess_M2ePro_Model_ActiveRecord_Factory */
    protected $_activeRecordFactory;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Category');
        $this->_activeRecordFactory = Mage::getSingleton('M2ePro/ActiveRecord_Factory');
    }

    //########################################

    public function loadByCategoryValue($value, $mode, $marketplaceId, $isCustomTemplate = null)
    {
        return $this->getResource()->loadByCategoryValue($this, $value, $mode, $marketplaceId, $isCustomTemplate);
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
            'template_category_id = ? OR template_category_secondary_id = ?',
            $this->getId()
        );

        if ((bool)$collection->getSize()) {
            return true;
        }

        /** @var Ess_M2ePro_Model_Resource_Collection_Abstract $collection */
        $collection = Mage::getModel('M2ePro/Ebay_Listing')->getCollection();
        $collection->getSelect()->where(
            'auto_global_adding_template_category_id = ? OR 
             auto_global_adding_template_category_secondary_id = ? OR
             auto_website_adding_template_category_id = ? OR
             auto_website_adding_template_category_secondary_id = ?',
            $this->getId()
        );

        if ((bool)$collection->getSize()) {
            return true;
        }

        /** @var Ess_M2ePro_Model_Resource_Collection_Abstract $collection */
        $collection = Mage::getModel('M2ePro/Ebay_Listing_Auto_Category_Group')->getCollection();
        $collection->getSelect()->where(
            'adding_template_category_id = ? OR adding_template_category_secondary_id = ?',
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

        $specifics = $this->getSpecifics(true);
        foreach ($specifics as $specific) {
            $specific->deleteInstance();
        }

        $this->_marketplaceModel     = null;
        $this->_categorySourceModels = array();

        $this->delete();
        return true;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        if ($this->_marketplaceModel === null) {
            $this->_marketplaceModel = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Marketplace', $this->getMarketplaceId()
            );
        }

        return $this->_marketplaceModel;
    }

    /**
     * @param Ess_M2ePro_Model_Marketplace $instance
     */
    public function setMarketplace(Ess_M2ePro_Model_Marketplace $instance)
    {
         $this->_marketplaceModel = $instance;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Ebay_Template_Category_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->_categorySourceModels[$productId])) {
            return $this->_categorySourceModels[$productId];
        }

        $this->_categorySourceModels[$productId] = Mage::getModel('M2ePro/Ebay_Template_Category_Source');
        $this->_categorySourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->_categorySourceModels[$productId]->setCategoryTemplate($this);

        return $this->_categorySourceModels[$productId];
    }

    //########################################

    /**
     * @param bool $asObjects
     * @return array|Ess_M2ePro_Model_Ebay_Template_Category_Specific[]
     */
    public function getSpecifics($asObjects = false)
    {
        $collection = $this->_activeRecordFactory->getObjectCollection('Ebay_Template_Category_Specific');
        $collection->addFieldToFilter('template_category_id', $this->getId());

        /** @var Ess_M2ePro_Model_Ebay_Template_Category_Specific $specific */
        foreach ($collection->getItems() as $specific) {
            $specific->setCategoryTemplate($this);
        }

        if (!$asObjects) {
            $result = $collection->toArray();
            return $result['items'];
        }

        return $collection->getItems();
    }

    //########################################

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return (int)$this->getData('category_id');
    }

    /**
     * @return int
     */
    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
    }

    /**
     * @return int
     */
    public function getIsCustomTemplate()
    {
        return $this->getData('is_custom_template');
    }

    //---------------------------------------

    /**
     * @return int
     */
    public function getCategoryMode()
    {
        return (int)$this->getData('category_mode');
    }

    public function isCategoryModeNone()
    {
        return $this->getCategoryMode() === self::CATEGORY_MODE_NONE;
    }

    public function isCategoryModeEbay()
    {
        return $this->getCategoryMode() === self::CATEGORY_MODE_EBAY;
    }

    public function isCategoryModeAttribute()
    {
        return $this->getCategoryMode() === self::CATEGORY_MODE_ATTRIBUTE;
    }

    //---------------------------------------

    /**
     * @return string|null
     */
    public function getCategoryAttribute()
    {
        return $this->getData('category_attribute');
    }

    //---------------------------------------

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    public function getUpdateDate()
    {
        return $this->getData('update_date');
    }

    //########################################

    /**
     * @return string
     */
    public function getCategoryValue()
    {
        return $this->isCategoryModeEbay() ? $this->getCategoryId() : $this->getCategoryAttribute();
    }

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

    /**
     * @param Ess_M2ePro_Model_Listing $listing
     * @param bool $withId
     * @return string
     */
    public function getCategoryPath(Ess_M2ePro_Model_Listing $listing, $withId = true)
    {
        $src = $this->getCategorySource();

        $data = array(
            'category_id'        => $src['value'],
            'category_mode'      => $src['mode'],
            'category_path'      => $src['path'],
            'category_attribute' => $src['attribute'],
        );

        Mage::helper('M2ePro/Component_Ebay_Category')->fillCategoriesPaths($data, $listing);

        $path = $data['category_path'];
        if ($withId && $src['mode'] == self::CATEGORY_MODE_EBAY) {
            $path .= ' ('.$src['value'].')';
        }

        return $path;
    }

    //########################################

    /**
     * @return array
     */
    public function getCategoryAttributes()
    {
        $usedAttributes = array();

        $categoryMainSrc = $this->getCategorySource();

        if ($categoryMainSrc['mode'] == self::CATEGORY_MODE_ATTRIBUTE) {
            $usedAttributes[] = $categoryMainSrc['attribute'];
        }

        foreach ($this->getSpecifics(true) as $specificModel) {
            $usedAttributes = array_merge($usedAttributes, $specificModel->getValueAttributes());
        }

        return array_values(array_unique($usedAttributes));
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('ebay_template_category');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('ebay_template_category');
        return parent::delete();
    }

    //########################################
}
