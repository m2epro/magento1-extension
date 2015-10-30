<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Magento_Store extends Mage_Core_Helper_Abstract
{
    private $defaultWebsite = NULL;
    private $defaultStoreGroup = NULL;
    private $defaultStore = NULL;

    //########################################

    public function isSingleStoreMode()
    {
        return Mage::getModel('core/store')->getCollection()->getSize() <= 2;
    }

    public function isMultiStoreMode()
    {
        return !$this->isSingleStoreMode();
    }

    //########################################

    public function getDefaultWebsite()
    {
        if (is_null($this->defaultWebsite)) {
            $this->defaultWebsite = Mage::getModel('core/website')->load(1,'is_default');
            if (is_null($this->defaultWebsite->getId())) {
                $this->defaultWebsite = Mage::getModel('core/website')->load(0);
                if (is_null($this->defaultWebsite->getId())) {
                    throw new Ess_M2ePro_Model_Exception('Getting default website is failed');
                }
            }
        }
        return $this->defaultWebsite;
    }

    public function getDefaultStoreGroup()
    {
        if (is_null($this->defaultStoreGroup)) {

            $defaultWebsite = $this->getDefaultWebsite();
            $defaultStoreGroupId = $defaultWebsite->getDefaultGroupId();

            $this->defaultStoreGroup = Mage::getModel('core/store_group')->load($defaultStoreGroupId);
            if (is_null($this->defaultStoreGroup->getId())) {
                $this->defaultStoreGroup = Mage::getModel('core/store_group')->load(0);
                if (is_null($this->defaultStoreGroup->getId())) {
                    throw new Ess_M2ePro_Model_Exception('Getting default store group is failed');
                }
            }
        }
        return $this->defaultStoreGroup;
    }

    public function getDefaultStore()
    {
        if (is_null($this->defaultStore)) {

            $defaultStoreGroup = $this->getDefaultStoreGroup();
            $defaultStoreId = $defaultStoreGroup->getDefaultStoreId();

            $this->defaultStore = Mage::getModel('core/store')->load($defaultStoreId);
            if (is_null($this->defaultStore->getId())) {
                $this->defaultStore = Mage::getModel('core/store')->load(0);
                if (is_null($this->defaultStore->getId())) {
                    throw new Ess_M2ePro_Model_Exception('Getting default store is failed');
                }
            }
        }
        return $this->defaultStore;
    }

    // ---------------------------------------

    public function getDefaultWebsiteId()
    {
        return (int)$this->getDefaultWebsite()->getId();
    }

    public function getDefaultStoreGroupId()
    {
        return (int)$this->getDefaultStoreGroup()->getId();
    }

    public function getDefaultStoreId()
    {
        return (int)$this->getDefaultStore()->getId();
    }

    //########################################

    public function getStorePath($storeId)
    {
        if ($storeId == Mage_Core_Model_App::ADMIN_STORE_ID) {
            return Mage::helper('M2ePro')->__('Admin (Default Values)');
        }

        try {
            $store = Mage::app()->getStore($storeId);
        } catch (Mage_Core_Model_Store_Exception $e) {
            return '';
        }

        $path = $store->getWebsite()->getName();
        $path .= ' > ' . $store->getGroup()->getName();
        $path .= ' > ' . $store->getName();

        return $path;
    }

    //########################################

    public function getWebsite($storeId)
    {
        try {
            $store = Mage::app()->getStore($storeId);
        } catch (Mage_Core_Model_Store_Exception $e) {
            return NULL;
        }

        return $store->getWebsite();
    }

    public function getWebsiteName($storeId)
    {
        $website = $this->getWebsite($storeId);

        return $website ? $website->getName() : '';
    }

    //########################################
}