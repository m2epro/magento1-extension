<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Magento_Store extends Mage_Core_Helper_Abstract
{
    protected $_defaultWebsite    = null;
    protected $_defaultStoreGroup = null;
    protected $_defaultStore      = null;

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
        if ($this->_defaultWebsite === null) {
            $this->_defaultWebsite = Mage::getModel('core/website')->load(1, 'is_default');
            if ($this->_defaultWebsite->getId() === null) {
                $this->_defaultWebsite = Mage::getModel('core/website')->load(0);
                if ($this->_defaultWebsite->getId() === null) {
                    throw new Ess_M2ePro_Model_Exception('Getting default website is failed');
                }
            }
        }

        return $this->_defaultWebsite;
    }

    public function getDefaultStoreGroup()
    {
        if ($this->_defaultStoreGroup === null) {
            $defaultWebsite = $this->getDefaultWebsite();
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

    public function getDefaultStore()
    {
        if ($this->_defaultStore === null) {
            $defaultStoreGroup = $this->getDefaultStoreGroup();
            $defaultStoreId = $defaultStoreGroup->getDefaultStoreId();

            $this->_defaultStore = Mage::getModel('core/store')->load($defaultStoreId);
            if ($this->_defaultStore->getId() === null) {
                $this->_defaultStore = Mage::getModel('core/store')->load(0);
                if ($this->_defaultStore->getId() === null) {
                    throw new Ess_M2ePro_Model_Exception('Getting default store is failed');
                }
            }
        }

        return $this->_defaultStore;
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

    /**
     * Multi Stock is not supported by core Magento functionality.
     * app/code/core/Mage/CatalogInventory/Model/Stock/Item.php::getStockId()
     * But by changing this method the M2e Pro can be made compatible with a custom solution
     *
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     * @return int
     */
    public function getStockId($store)
    {
        return Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID;
    }

    //########################################
}
