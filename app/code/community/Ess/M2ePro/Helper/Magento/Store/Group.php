<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Magento_Store_Group
{
    protected $_defaultStoreGroup = null;

    //########################################

    public function isExists($entity)
    {
        if ($entity instanceof Mage_Core_Model_Store_Group) {
            return (bool)$entity->getCode();
        }

        try {
            Mage::app()->getGroup($entity);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    //########################################

    public function isChildOfWebsite($groupId, $websiteId)
    {
        $group = Mage::app()->getGroup($groupId);

        return ($group->getWebsite()->getId() == $websiteId);
    }

    //########################################

    public function getDefault()
    {
        if ($this->_defaultStoreGroup === null) {
            $defaultWebsite = Mage::helper('M2ePro/Magento_Store_Website')->getDefault();
            $defaultStoreGroupId = $defaultWebsite->getDefaultGroupId();

            $this->_defaultStoreGroup = Mage::getModel('core/store_group')->load($defaultStoreGroupId);
            if ($this->_defaultStoreGroup->getId() === null) {
                $this->_defaultStoreGroup = Mage::getModel('core/store_group')->load(0);

                if ($this->_defaultStoreGroup->getId() === null) {
                    throw new Ess_M2ePro_Model_Exception('Getting default store group is failed');
                }
            }
        }

        return $this->_defaultStoreGroup;
    }

    public function getDefaultGroupId()
    {
        return (int)$this->getDefault()->getId();
    }

    //########################################

    public function addGroup($websiteId, $name, $rootCategoryId)
    {
        if (!Mage::helper('M2ePro/Magento_Store_Website')->isExists($websiteId)) {
            $error = Mage::helper('M2ePro')->__('Website with id %value% does not exist.', (int)$websiteId);
            throw new Ess_M2ePro_Model_Exception($error);
        }

        $group = new Mage_Core_Model_Store_Group();
        $group->setId(null);
        $group->setName($name);

        $group->setWebsiteId($websiteId);
        $group->setWebsite(Mage::app()->getWebsite($websiteId));

        if (isset($rootCategoryId)) {
            $category = Mage::getModel('catalog/category')->load($rootCategoryId);

            if (!$category->hasEntityId()) {
                $error = Mage::helper('M2ePro')->__('Category with %category_id% doen\'t exist', $rootCategoryId);
                throw new Ess_M2ePro_Model_Exception($error);
            }

            if ((int)$category->getLevel() !== 1) {
                $error = Mage::helper('M2ePro')->__('Category of level 1 must be provided.');
                throw new Ess_M2ePro_Model_Exception($error);
            }

            $group->setRootCategoryId($rootCategoryId);
        }

        $group->save();

        return $group;
    }

    //########################################
}