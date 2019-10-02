<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_ProcessingList extends Ess_M2ePro_Model_Abstract
{
    const STAGE_LIST_DETAILS              = 1;
    const STAGE_RELIST_QTY_READY          = 2;
    const STAGE_RELIST_QTY_WAITING_RESULT = 3;

    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Walmart_Listing_Product_Action_ProcessingList');
    }

    //####################################

    /**
     * @return int
     */
    public function getAccountId()
    {
        return (int)$this->getData('account_id');
    }

    /**
     * @return int
     */
    public function getListingProductId()
    {
        return (int)$this->getData('listing_product_id');
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return (string)$this->getData('sku');
    }

    /**
     * @return string
     */
    public function getStage()
    {
        return (string)$this->getData('stage');
    }

    /**
     * @return string
     */
    public function getRelistRequestPendingId()
    {
        return (int)$this->getData('relist_request_pending_single_id');
    }

    /**
     * @return array
     */
    public function getRelistRequestData()
    {
        return Mage::helper('M2ePro')->jsonDecode($this->getData('relist_request_data'));
    }

    /**
     * @return array
     */
    public function getRelistConfiguratorData()
    {
        return Mage::helper('M2ePro')->jsonDecode($this->getData('relist_configurator_data'));
    }

    //####################################
}
