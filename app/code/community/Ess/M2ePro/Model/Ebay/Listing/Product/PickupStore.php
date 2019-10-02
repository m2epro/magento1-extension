<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_PickupStore extends Ess_M2ePro_Model_Component_Abstract
{
    /** @var Ess_M2ePro_Model_Ebay_Account_PickupStore */
    protected $_accountPickupStore = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Listing_Product_PickupStore');
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Account_PickupStore|Mage_Core_Model_Abstract
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getAccountPickupStore()
    {
        if ($this->getId() === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        if ($this->_accountPickupStore !== null) {
            return $this->_accountPickupStore;
        }

        return $this->_accountPickupStore = Mage::getModel('M2ePro/Ebay_Account_PickupStore')
                                                ->loadInstance($this->getAccountPickupStoreId());
    }

    //########################################

    public function getListingProductId()
    {
        return (int)$this->getData('listing_product_id');
    }

    public function getAccountPickupStoreId()
    {
        return (int)$this->getData('account_pickup_store_id');
    }

    public function isProcessRequired()
    {
        return (int)$this->getData('is_process_required');
    }

    //########################################
}
