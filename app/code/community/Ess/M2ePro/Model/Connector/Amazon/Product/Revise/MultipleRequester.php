<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Amazon_Product_Revise_MultipleRequester
    extends Ess_M2ePro_Model_Connector_Amazon_Product_Requester
{
    //########################################

    /**
     * @return array
     */
    public function getCommand()
    {
        return array('product','update','entities');
    }

    //########################################

    protected function getActionType()
    {
        return Ess_M2ePro_Model_Listing_Product::ACTION_REVISE;
    }

    protected function getLockIdentifier()
    {
        if (!empty($this->params['switch_to'])) {
            $switchTo = $this->params['switch_to'];
            if ($switchTo === Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Qty::FULFILLMENT_MODE_AFN) {
                return 'switch_to_afn';
            }
            if ($switchTo === Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Qty::FULFILLMENT_MODE_MFN) {
                return 'switch_to_mfn';
            }
        }

        return parent::getLockIdentifier();
    }

    protected function getLogsAction()
    {
        if (!empty($this->params['switch_to'])) {
            $switchTo = $this->params['switch_to'];
            if ($switchTo === Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Qty::FULFILLMENT_MODE_AFN) {
                return Ess_M2ePro_Model_Listing_Log::ACTION_SWITCH_TO_AFN_ON_COMPONENT;
            }
            if ($switchTo === Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Qty::FULFILLMENT_MODE_MFN) {
                return Ess_M2ePro_Model_Listing_Log::ACTION_SWITCH_TO_MFN_ON_COMPONENT;
            }
        }

        return Ess_M2ePro_Model_Listing_Log::ACTION_REVISE_PRODUCT_ON_COMPONENT;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product[] $listingProducts
     * @return Ess_M2ePro_Model_Listing_Product[]
     */
    protected function filterChildListingProductsByStatus(array $listingProducts)
    {
        $resultListingProducts = array();

        foreach ($listingProducts as $childListingProduct) {
            if (!$childListingProduct->isListed() || !$childListingProduct->isRevisable()) {
                continue;
            }

            $resultListingProducts[] = $childListingProduct;
        }

        return $resultListingProducts;
    }

    //########################################
}