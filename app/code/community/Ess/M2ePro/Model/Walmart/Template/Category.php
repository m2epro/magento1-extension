<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Mysql4_Walmart_Template_Category getResource()
 */
class Ess_M2ePro_Model_Walmart_Template_Category extends Ess_M2ePro_Model_Component_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    private $marketplaceModel = NULL;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Walmart_Template_Category');
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

        $collection = Mage::getModel('M2ePro/Walmart_Listing')->getCollection();
        $collection->getSelect()
            ->where("main_table.auto_global_adding_category_template_id = {$this->getId()} OR
                     main_table.auto_website_adding_category_template_id = {$this->getId()}");

        return (bool)Mage::getModel('M2ePro/Walmart_Listing_Product')
                            ->getCollection()
                            ->addFieldToFilter('template_category_id', $this->getId())
                            ->getSize() ||
               (bool)Mage::getModel('M2ePro/Walmart_Listing_Auto_Category_Group')->getCollection()
                            ->addFieldToFilter('adding_category_template_id', $this->getId())
                            ->getSize() ||
               (bool)$collection->getSize();
    }

    public function isLockedForCategoryChange()
    {
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product')
            ->addFieldToFilter('second_table.template_category_id', $this->getId());

        if ($collection->getSize() <= 0) {
            return false;
        }

        // todo check not empty variation_group_id or locked for list

        return false;
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        foreach ($this->getSpecifics(true) as $specific) {
            $specific->deleteInstance();
        }

        $this->delete();
        return true;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        if (is_null($this->marketplaceModel)) {

            $this->marketplaceModel = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
                'Marketplace', $this->getMarketplaceId()
            );
        }

        return $this->marketplaceModel;
    }

    /**
     * @param Ess_M2ePro_Model_Marketplace $instance
     */
    public function setMarketplace(Ess_M2ePro_Model_Marketplace $instance)
    {
        $this->marketplaceModel = $instance;
    }

    //########################################

    /**
     * @param bool $asObjects
     * @param array $filters
     * @return array|Ess_M2ePro_Model_Walmart_Template_Category_Specific[]
     */
    public function getSpecifics($asObjects = false, array $filters = array())
    {
        $specifics = $this->getRelatedSimpleItems('Walmart_Template_Category_Specific','template_category_id',
            $asObjects, $filters);
        if ($asObjects) {
            /** @var Ess_M2ePro_Model_Walmart_Template_Category_Specific $specific */
            foreach ($specifics as $specific) {
                $specific->setDescriptionTemplate($this->getParentObject());
            }
        }

        if (!$asObjects) {
            foreach ($specifics as &$specific) {
                $specific['attributes'] = (array)Mage::helper('M2ePro')->jsonDecode($specific['attributes']);
            }
        }

        return $specifics;
    }

    //########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
    }

    public function getProductDataNick()
    {
        return $this->getData('product_data_nick');
    }

    public function getCategoryPath()
    {
        return $this->getData('category_path');
    }

    public function getBrowsenodeId()
    {
        return $this->getData('browsenode_id');
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_category');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_category');
        return parent::delete();
    }

    //########################################
}