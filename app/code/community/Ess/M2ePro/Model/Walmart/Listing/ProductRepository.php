<?php

class Ess_M2ePro_Model_Walmart_Listing_ProductRepository
{
    /** @var Ess_M2ePro_Model_Resource_Walmart_Listing_Product */
    private $walmartProductResource;

    public function __construct()
    {
        $this->walmartProductResource = Mage::getResourceModel('M2ePro/Walmart_Listing_Product');
    }

    /**
     * @return void
     */
    public function save(Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct)
    {
        $this->walmartProductResource->save($walmartListingProduct);
    }
}