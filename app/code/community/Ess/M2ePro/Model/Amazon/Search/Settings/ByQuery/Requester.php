<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Search_Settings_ByQuery_Requester
    extends Ess_M2ePro_Model_Connector_Amazon_Search_ByQuery_ItemsRequester
{
    private $listingProduct = NULL;

    // ########################################

    public function setProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::setProcessingLocks($processingRequest);

        $this->getListingProduct()->addObjectLock(NULL, $processingRequest->getHash());
        $this->getListingProduct()->addObjectLock('in_action', $processingRequest->getHash());
        $this->getListingProduct()->addObjectLock('search_action', $processingRequest->getHash());

        $this->getListingProduct()->getListing()->addObjectLock(NULL, $processingRequest->getHash());
    }

    public function eventBeforeProcessing()
    {
        parent::eventBeforeProcessing();

        $this->getListingProduct()->setData(
            'search_settings_status', Ess_M2ePro_Model_Amazon_Listing_Product::SEARCH_SETTINGS_STATUS_IN_PROGRESS
        );
        $this->getListingProduct()->setSettings(
            'search_settings_data', array('type' => 'string', 'value' => $this->getQuery())
        );
        $this->getListingProduct()->save();
    }

    // ########################################

    protected function getQuery()
    {
        return $this->params['query'];
    }

    protected function getVariationBadParentModifyChildToSimple()
    {
        return $this->params['variation_bad_parent_modify_child_to_simple'];
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        if (is_null($this->listingProduct)) {
            $this->listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject(
                'Listing_Product',
                $this->params['listing_product_id']
            );
        }

        return $this->listingProduct;
    }

    // ########################################
}