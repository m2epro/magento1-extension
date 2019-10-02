<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Observer_Product_AddUpdate_Abstract extends Ess_M2ePro_Model_Observer_Product_Abstract
{
    protected $_affectedListingsProducts = array();

    //########################################

    /**
     * @return bool
     */
    public function canProcess()
    {
        return (string)$this->getEvent()->getProduct()->getSku() != '';
    }

    //########################################

    abstract protected function isAddingProductProcess();

    //########################################

    protected function areThereAffectedItems()
    {
        return !empty($this->getAffectedListingsProducts());
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing_Product[]
     */
    protected function getAffectedListingsProducts()
    {
        if (!empty($this->_affectedListingsProducts)) {
            return $this->_affectedListingsProducts;
        }

        return $this->_affectedListingsProducts = Mage::getResourceModel('M2ePro/Listing_Product')
                                                      ->getItemsByProductId($this->getProductId());
    }

    //########################################
}
