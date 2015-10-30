<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Marketplace extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    const STATUS_DISABLE = 0;
    const STATUS_ENABLE = 1;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Marketplace');
    }

    //########################################

    /**
     * @return bool
     */
    public function isLocked()
    {
        return true;
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $otherListings = $this->getOtherListings(true);
        foreach ($otherListings as $otherListing) {
            $otherListing->deleteInstance();
        }

        $orders = $this->getOrders(true);
        foreach ($orders as $order) {
            $order->deleteInstance();
        }

        $this->deleteChildInstance();
        $this->delete();

        return true;
    }

    //########################################

    /**
     * @param bool $asObjects
     * @param array $filters
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getOtherListings($asObjects = false, array $filters = array())
    {
        $otherListings = $this->getRelatedComponentItems('Listing_Other','marketplace_id',$asObjects,$filters);

        if ($asObjects) {
            foreach ($otherListings as $otherListing) {
                /** @var $otherListing Ess_M2ePro_Model_Listing_Other */
                $otherListing->setMarketplace($this);
            }
        }

        return $otherListings;
    }

    /**
     * @param bool $asObjects
     * @param array $filters
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getOrders($asObjects = false, array $filters = array())
    {
        $orders = $this->getRelatedComponentItems('Order','marketplace_id',$asObjects,$filters);

        if ($asObjects) {
            foreach ($orders as $order) {
                /** @var $order Ess_M2ePro_Model_Order */
                $order->setMarketplace($this);
            }
        }

        return $orders;
    }

    //########################################

    public function getIdByCode($code)
    {
        return $this->load($code,'code')->getId();
    }

    /**
     * @return bool
     */
    public function isStatusEnabled()
    {
        return $this->getStatus() == self::STATUS_ENABLE;
    }

    //########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function getCode()
    {
        return $this->getData('code');
    }

    public function getUrl()
    {
        return $this->getData('url');
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return (int)$this->getData('status');
    }

    public function getGroupTitle()
    {
        return $this->getData('group_title');
    }

    /**
     * @return int
     */
    public function getNativeId()
    {
        return (int)$this->getData('native_id');
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('marketplace');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('marketplace');
        return parent::delete();
    }

    //########################################
}