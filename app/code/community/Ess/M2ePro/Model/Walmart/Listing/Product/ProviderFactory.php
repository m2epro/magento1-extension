<?php

class Ess_M2ePro_Model_Walmart_Listing_Product_ProviderFactory
{
    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Product_Provider
     */
    public function create(Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct)
    {
        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Provider */
        return Mage::getModel(
            'M2ePro/Walmart_Listing_Product_Provider',
            $walmartListingProduct
        );
    }
}
