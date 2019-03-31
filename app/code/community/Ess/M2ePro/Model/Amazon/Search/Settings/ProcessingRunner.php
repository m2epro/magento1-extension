<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Search_Settings_Processing
    extends Ess_M2ePro_Model_Connector_Command_Pending_Processing_Single_Runner
{
    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    private $listingProduct = NULL;

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

        $this->getListingProduct()->addProcessingLock(NULL, $this->getId());
        $this->getListingProduct()->addProcessingLock('in_action', $this->getId());
        $this->getListingProduct()->addProcessingLock('search_action', $this->getId());

        $this->getListingProduct()->getListing()->addProcessingLock(NULL, $this->getId());
    }

    protected function unsetLocks()
    {
        parent::unsetLocks();

        $this->getListingProduct()->deleteProcessingLocks(NULL, $this->getId());
        $this->getListingProduct()->getListing()->deleteProcessingLocks(NULL, $this->getId());
    }

    // ########################################

    private function getListingProduct()
    {
        if (!is_null($this->listingProduct)) {
            return $this->listingProduct;
        }

        $params = $this->getParams();

        $this->listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject(
            'Listing_Product', $params['listing_product_id']
        );

        return $this->listingProduct;
    }

    // ########################################
}