<?php

class Ess_M2ePro_Model_Walmart_Listing_Product_Provider
{
    /** @var Ess_M2ePro_Model_Walmart_Listing_Product */
    private $walmartListingProduct;

    public function __construct(Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct)
    {
        $this->walmartListingProduct = $walmartListingProduct;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Product_Provider_Condition|null
     */
    public function retrieveCondition()
    {
        $walmartListing = $this->walmartListingProduct->getWalmartListing();
        if ($walmartListing->isConditionModeNone()) {
            return null;
        }

        if ($walmartListing->isConditionModeRecommended()) {
            return Ess_M2ePro_Model_Walmart_Listing_Product_Provider_Condition::createWithValue(
                $walmartListing->getConditionRecommendedValue()
            );
        }

        $attributeCode = $walmartListing->getConditionCustomAttribute();
        $attribute = $this->walmartListingProduct->getMagentoProduct()
                                                 ->getAttributeValue($attributeCode);

        return !empty($attribute)
            ? Ess_M2ePro_Model_Walmart_Listing_Product_Provider_Condition::createWithValue($attribute)
            : Ess_M2ePro_Model_Walmart_Listing_Product_Provider_Condition::createWithoutMagentoAttribute();
    }
}
