<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Account_PickupStore_State extends Ess_M2ePro_Model_Component_Abstract
{
    const IN_STOCK      = 'IN_STOCK';
    const OUT_OF_STOCK  = 'OUT_OF_STOCK';

    /** @var Ess_M2ePro_Model_Ebay_Account_PickupStore $_accountPickupStore */
    protected $_accountPickupStore = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Account_PickupStore_State');
    }

    //########################################

    public function getAccountPickupStore()
    {
        if ($this->_accountPickupStore !== null) {
            return $this->_accountPickupStore;
        }

        return $this->_accountPickupStore = Mage::helper('M2ePro')->getCachedObject(
            'Ebay_Account_PickupStore', $this->getAccountPickupStoreId()
        );
    }

    //########################################

    public function getAccountPickupStoreId()
    {
        return (int)$this->getData('account_pickup_store_id');
    }

    public function isInProcessing()
    {
        return (bool)$this->getData('is_in_processing');
    }

    public function getSku()
    {
        return (string)$this->getData('sku');
    }

    public function getOnlineQty()
    {
        return (int)$this->getData('online_qty');
    }

    public function getTargetQty()
    {
        return (int)$this->getData('target_qty');
    }

    public function isAdded()
    {
        return (bool)$this->getData('is_added');
    }

    public function isDeleted()
    {
        return (bool)$this->getData('is_deleted');
    }

    //########################################
}
