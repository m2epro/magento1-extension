<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Connector_Product_Relist_Requester
    extends Ess_M2ePro_Model_Amazon_Connector_Product_Requester
{
    //########################################

    public function getCommand()
    {
        return array('product','update','entities');
    }

    //########################################

    protected function getActionType()
    {
        return Ess_M2ePro_Model_Listing_Product::ACTION_RELIST;
    }

    protected function getLogsAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_RELIST_PRODUCT_ON_COMPONENT;
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
            if (!$childListingProduct->isInactive() || !$childListingProduct->isRelistable()) {
                continue;
            }

            $resultListingProducts[] = $childListingProduct;
        }

        return $resultListingProducts;
    }

    //########################################
}
