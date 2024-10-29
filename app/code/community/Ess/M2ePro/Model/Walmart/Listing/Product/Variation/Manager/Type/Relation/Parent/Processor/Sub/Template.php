<?php

class Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Template
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Abstract
{
    protected function check()
    {
        return null;
    }

    protected function execute()
    {
        $productTypeId = $this->getProcessor()->getWalmartListingProduct()->getProductTypeId();

        foreach ($this->getProcessor()->getTypeModel()->getChildListingsProducts() as $listingProduct) {

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
            $walmartListingProduct = $listingProduct->getChildObject();
            if ($walmartListingProduct->getProductTypeId() != $productTypeId) {
                $listingProduct->setData(
                    Ess_M2ePro_Model_Resource_Walmart_Listing_Product::COLUMN_PRODUCT_TYPE_ID,
                    $productTypeId
                );
                $listingProduct->save();
            }
        }
    }
}
