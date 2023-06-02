<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Account as AmazonAccount;
use Ess_M2ePro_Model_Ebay_Account as EbayAccount;
use Ess_M2ePro_Model_Walmart_Account as WalmartAccount;

/**
 * @method AmazonAccount|EbayAccount|WalmartAccount getChildObject()
 */
class Ess_M2ePro_Model_Account extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    /** @var Ess_M2ePro_Model_ActiveRecord_Factory */
    protected $_activeRecordFactory;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Account');
        $this->_activeRecordFactory = Mage::getSingleton('M2ePro/ActiveRecord_Factory');
    }

    //########################################

    /**
     * @param bool $onlyMainConditions
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isLocked($onlyMainConditions = false)
    {
        if ($this->isComponentModeEbay() && $this->getChildObject()->isModeSandbox()) {
            return false;
        }

        if (!$onlyMainConditions && parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Listing')
                            ->getCollection()
                            ->addFieldToFilter('account_id', $this->getId())
                            ->getSize();
    }

    //########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function getAdditionalData()
    {
        return $this->getData('additional_data');
    }

    /**
     * @return bool
     */
    public function isSingleAccountMode()
    {
        return Mage::getModel('M2ePro/Account')->getCollection()->getSize() <= 1;
    }

    //########################################

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

    //########################################
}
