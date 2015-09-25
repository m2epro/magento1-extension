<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Account extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Account');
    }

    // ########################################

    public function isLocked($onlyMainConditions = false)
    {
        if (!$onlyMainConditions && parent::isLocked()) {
            return true;
        }

        if ($this->isComponentModeEbay() && $this->getChildObject()->isModeSandbox()) {
            return false;
        }

        return (bool)Mage::getModel('M2ePro/Listing')
                            ->getCollection()
                            ->addFieldToFilter('account_id', $this->getId())
                            ->getSize();
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

        if ($this->isComponentModeEbay() && $this->getChildObject()->isModeSandbox()) {
            $listings = $this->getRelatedComponentItems('Listing', 'account_id', true);
            foreach ($listings as $listing) {
                $listing->deleteInstance();
            }
        }

        $orders = $this->getOrders(true);
        foreach ($orders as $order) {
            $order->deleteInstance();
        }

        $this->deleteChildInstance();
        $this->delete();

        return true;
    }

    // ########################################

    public function getOtherListings($asObjects = false, array $filters = array())
    {
        $otherListings = $this->getRelatedComponentItems('Listing_Other','account_id',$asObjects,$filters);

        if ($asObjects) {
            foreach ($otherListings as $otherListing) {
                /** @var $otherListing Ess_M2ePro_Model_Listing_Other */
                $otherListing->setAccount($this);
            }
        }

        return $otherListings;
    }

    public function getOrders($asObjects = false, array $filters = array())
    {
        $orders = $this->getRelatedComponentItems('Order','account_id',$asObjects,$filters);

        if ($asObjects) {
            foreach ($orders as $order) {
                /** @var $order Ess_M2ePro_Model_Order */
                $order->setAccount($this);
            }
        }

        return $orders;
    }

    // ########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function isSingleAccountMode()
    {
        return Mage::getModel('M2ePro/Account')->getCollection()->getSize() <= 1;
    }

    // ########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('account');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('account');
        return parent::delete();
    }

    // ########################################
}