<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Magento_Store_View
{
    private $defaultStore = NULL;

    //########################################

    public function isExits($entity)
    {
        if ($entity instanceof Mage_Core_Model_Store) {
            return (bool)$entity->getCode();
        }

        try {
            Mage::app()->getStore($entity);
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

    public function isChildOfGroup($storeId, $groupId)
    {
        $store = Mage::app()->getStore($storeId);

        return ($store->getGroup()->getId() == $groupId);
    }

    //########################################

    public function isSingleMode()
    {
        return Mage::getModel('core/store')->getCollection()->getSize() <= 2;
    }

    public function isMultiMode()
    {
        return !$this->isSingleMode();
    }

    //########################################

    public function getDefault()
    {
        if (is_null($this->defaultStore)) {
            $defaultStoreGroup = Mage::helper('M2ePro/Magento_Store_Group')->getDefault();
            $defaultStoreId = $defaultStoreGroup->getDefaultStoreId();

            $this->defaultStore = Mage::getModel('core/store')->load($defaultStoreId);
            if (is_null($this->defaultStore->getId())) {
                $this->defaultStore = Mage::getModel('core/store')->load(0);

                if (is_null($this->defaultStore->getId())) {
                    throw new Ess_M2ePro_Model_Exception('Getting default store is failed.');
                }
            }
        }

        return $this->defaultStore;
    }

    public function getDefaultStoreId()
    {
        return (int)$this->getDefault()->getId();
    }

    //########################################

    public function getPath($storeId)
    {
        if ($storeId == Mage_Core_Model_App::ADMIN_STORE_ID) {
            return Mage::helper('M2ePro')->__('Admin (Default Values)');
        }

        try {
            $store = Mage::app()->getStore($storeId);
        } catch (Mage_Core_Model_Store_Exception $e) {
            $error = Mage::helper('M2ePro')->__("Store with %store_id% doesn't exist.", $storeId);
            throw new Ess_M2ePro_Model_Exception($error);
        }

        $path = $store->getWebsite()->getName();
        $path .= ' > ' . $store->getGroup()->getName();
        $path .= ' > ' . $store->getName();

        return $path;
    }

    //########################################

    public function addStore($name, $code, $websiteId, $groupId = null)
    {
        if (!Mage::helper('M2ePro/Magento_Store_Website')->isExists($websiteId)) {
            $error = Mage::helper('M2ePro')->__('Website with id %value% does not exists.',
                $websiteId);
            throw new Ess_M2ePro_Model_Exception($error);
        }

        try {
            $store = Mage::app()->getStore($code, 'code');
            $error = Mage::helper('M2ePro')->__('Store with %code% already exists.', $code);
            throw new Ess_M2ePro_Model_Exception($error);

        } catch (Exception $e) {
            // M2ePro_TRANSLATIONS
            // Group with id %group_id% doesn't belongs to website with %site_id%.
            if ($groupId) {

                if (!Mage::helper('M2ePro/Magento_Store_Group')->isChildOfWebsite($groupId, $websiteId)) {
                    $error = Mage::helper('M2ePro')->__('Group with id %group_id% doesn\'t belong to'.
                        'website with %site_id%.',$groupId, $websiteId);
                    throw new Ess_M2ePro_Model_Exception($error);
                }
            } else {
                $groupId = Mage::app()->getWebsite($websiteId)->getDefaultGroupId();
            }

            $store = new Mage_Core_Model_Store();
            $store->setId(null);

            $store->setWebsite(Mage::app()->getWebsite($websiteId));
            $store->setWebsiteId($websiteId);

            $store->setGroup(Mage::app()->getGroup($groupId));
            $store->setGroupId($groupId);

            $store->setCode($code);
            $store->setName($name);

            $store->save();
            Mage::app()->reinitStores();

            return $store;
        }
    }

    //########################################
}