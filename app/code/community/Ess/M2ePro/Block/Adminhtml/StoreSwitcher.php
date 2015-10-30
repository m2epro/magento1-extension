<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_StoreSwitcher extends Mage_Adminhtml_Block_Template
{
    const DISPLAY_DEFAULT_STORE_MODE_UP   = 'up';
    const DISPLAY_DEFAULT_STORE_MODE_DOWN = 'down';

    protected $_storeIds;
    protected $_hasDefaultOption = true;

    //########################################

    public function __construct($params)
    {
        parent::__construct($params);
        $this->setTemplate('M2ePro/store_switcher.phtml');
        $this->setUseConfirm(true);
        $this->setUseAjax(true);
        $this->setDefaultStoreName(Mage::helper('M2ePro')->__('Admin (Default Values)'));
    }

    //########################################

    public function isDisplayDefaultStoreModeUp()
    {
        if (!$this->getData('display_default_store_mode')) {
            return true;
        }

        return $this->getData('display_default_store_mode') == self::DISPLAY_DEFAULT_STORE_MODE_UP;
    }

    public function isDisplayDefaultStoreModeDown()
    {
        return $this->getData('display_default_store_mode') == self::DISPLAY_DEFAULT_STORE_MODE_DOWN;
    }

    //########################################

    public function isRequiredOption()
    {
        return $this->getData('required_option') === true;
    }

    public function hasEmptyOption()
    {
        return $this->getData('empty_option') === true;
    }

    //########################################

    public function getDefaultStoreName()
    {
        if ($this->getData('default_store_title')) {
            return $this->getData('default_store_title');
        }

        return parent::getDefaultStoreName();
    }

    //########################################

    public function getWebsiteCollection()
    {
        $collection = Mage::getModel('core/website')->getResourceCollection();

        $websiteIds = $this->getWebsiteIds();
        if (!is_null($websiteIds)) {
            $collection->addIdFilter($this->getWebsiteIds());
        }

        return $collection->load();
    }

    public function getWebsites()
    {
        $websites = Mage::app()->getWebsites();
        if ($websiteIds = $this->getWebsiteIds()) {
            foreach ($websites as $websiteId => $website) {
                if (!in_array($websiteId, $websiteIds)) {
                    unset($websites[$websiteId]);
                }
            }
        }
        return $websites;
    }

    //########################################

    public function getGroupCollection($website)
    {
        if (!$website instanceof Mage_Core_Model_Website) {
            $website = Mage::getModel('core/website')->load($website);
        }
        return $website->getGroupCollection();
    }

    public function getStoreGroups($website)
    {
        if (!$website instanceof Mage_Core_Model_Website) {
            $website = Mage::app()->getWebsite($website);
        }
        return $website->getGroups();
    }

    public function getStoreCollection($group)
    {
        if (!$group instanceof Mage_Core_Model_Store_Group) {
            $group = Mage::getModel('core/store_group')->load($group);
        }
        $stores = $group->getStoreCollection();
        $_storeIds = $this->getStoreIds();
        if (!empty($_storeIds)) {
            $stores->addIdFilter($_storeIds);
        }
        return $stores;
    }

    public function getStores($group)
    {
        if (!$group instanceof Mage_Core_Model_Store_Group) {
            $group = Mage::app()->getGroup($group);
        }
        $stores = $group->getStores();
        if ($storeIds = $this->getStoreIds()) {
            foreach ($stores as $storeId => $store) {
                if (!in_array($storeId, $storeIds)) {
                    unset($stores[$storeId]);
                }
            }
        }
        return $stores;
    }

    public function getSwitchUrl()
    {
        if ($url = $this->getData('switch_url')) {
            return $url;
        }
        return $this->getUrl('*/*/new', array('_current' => true, 'store' => null));
    }

    public function getStoreId()
    {
        $selected = $this->getData('selected');
        return $selected ? $selected : 0;
    }

    public function setStoreIds($storeIds)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }

    public function getStoreIds()
    {
        return $this->_storeIds;
    }

    public function getStoreSelectId()
    {
        $id = $this->getData('id');
        return $id ? $id : 'store_switcher';
    }

    public function getStoreSelectName()
    {
        $name = $this->getData('name');
        return $name ? $name : $this->getStoreSelectId();
    }

    public function hasDefaultOption($hasDefaultOption = null)
    {
        if (null !== $hasDefaultOption) {
            $this->_hasDefaultOption = $hasDefaultOption;
        }
        return $this->_hasDefaultOption;
    }

    //########################################
}