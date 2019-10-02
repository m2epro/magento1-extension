<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Search_Settings_ProcessingRunner
    extends Ess_M2ePro_Model_Connector_Command_Pending_Processing_Single_Runner
{
    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct;

    // ########################################

    protected function eventBefore()
    {
        parent::eventBefore();

        $params = $this->getParams();

        $this->getListingProduct()->setData(
            'search_settings_status', Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_IN_PROGRESS
        );
        $this->getListingProduct()->setSettings(
            'search_settings_data', array('type' => $params['type'], 'value' => $params['value'])
        );
        $this->getListingProduct()->save();
    }

    protected function setLocks()
    {
        parent::setLocks();

        $this->getListingProduct()->addProcessingLock(null, $this->getId());
        $this->getListingProduct()->addProcessingLock('in_action', $this->getId());
        $this->getListingProduct()->addProcessingLock('search_action', $this->getId());

        $this->getListingProduct()->getListing()->addProcessingLock(null, $this->getId());
    }

    protected function unsetLocks()
    {
        parent::unsetLocks();

        $this->getListingProduct()->deleteProcessingLocks(null, $this->getId());
        $this->getListingProduct()->getListing()->deleteProcessingLocks(null, $this->getId());
    }

    // ########################################

    protected function getListingProduct()
    {
        if ($this->_listingProduct !== null) {
            return $this->_listingProduct;
        }

        $params = $this->getParams();

        $this->_listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject(
            'Listing_Product', $params['listing_product_id']
        );

        return $this->_listingProduct;
    }

    // ########################################
}