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

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Category');
    }

    //########################################

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
     * @param array $filters
     * @return array|Ess_M2ePro_Model_Ebay_Template_Category_Specific[]
     */
    public function getSpecifics($asObjects = false, array $filters = array())
    {
        $specifics = $this->getRelatedSimpleItems(
            'Ebay_Template_Category_Specific', 'template_category_id',
            $asObjects, $filters
        );

        if ($asObjects) {
            /** @var Ess_M2ePro_Model_Ebay_Template_Category_Specific $specific */
            foreach ($specifics as $specific) {
                $specific->setCategoryTemplate($this);
            }
        }

        return $specifics;
    }

    //########################################

    /**
     * @return int
     */
    public function getCategoryMainId()
    {
        return (int)$this->getData('category_main_id');
    }

    /**
     * @return int
     */
    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
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

    /**
     * @return array
     */
    public function getCategoryMainSource()
    {
        return array(
            'mode'      => $this->getData('category_main_mode'),
            'value'     => $this->getData('category_main_id'),
            'path'      => $this->getData('category_main_path'),
            'attribute' => $this->getData('category_main_attribute')
        );
    }

    /**
     * @param Ess_M2ePro_Model_Listing $listing
     * @param bool $withId
     * @return string
     */
    public function getCategoryPath(Ess_M2ePro_Model_Listing $listing, $withId = true)
    {
        $src = $this->getCategoryMainSource();

        $data = array(
            'category_main_id'        => $src['value'],
            'category_main_mode'      => $src['mode'],
            'category_main_path'      => $src['path'],
            'category_main_attribute' => $src['attribute'],
        );

        Mage::helper('M2ePro/Component_Ebay_Category')->fillCategoriesPaths($data, $listing);

        $path = $data['category_main_path'];
        if ($withId && $src['mode'] == self::CATEGORY_MODE_EBAY) {
            $path .= ' ('.$src['value'].')';
        }

        return $path;
    }

    //########################################

    /**
     * @return array
     */
    public function getMainCategoryAttributes()
    {
        $usedAttributes = array();

        $categoryMainSrc = $this->getCategoryMainSource();

        if ($categoryMainSrc['mode'] == self::CATEGORY_MODE_ATTRIBUTE) {
            $usedAttributes[] = $categoryMainSrc['attribute'];
        }

        foreach ($this->getSpecifics(true) as $specificModel) {
            $usedAttributes = array_merge($usedAttributes, $specificModel->getValueAttributes());
        }

        return array_values(array_unique($usedAttributes));
    }

    //########################################

    /**
     * @return array
     */
    public function getDefaultSettings()
    {
        return array(
            'category_main_id' => 0,
            'category_main_path' => '',
            'category_main_mode' => self::CATEGORY_MODE_EBAY,
            'category_main_attribute' => ''
        );
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
