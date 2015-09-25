<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Listing_Auto_Actions_Listing extends Ess_M2ePro_Model_Listing_Auto_Actions_Listing
{
    //####################################

    public function addProductByCategoryGroup(Mage_Catalog_Model_Product $product,
                                              Ess_M2ePro_Model_Listing_Auto_Category_Group $categoryGroup)
    {
        $this->getListing()->addProduct($product);
    }

    public function addProductByGlobalListing(Mage_Catalog_Model_Product $product, Ess_M2ePro_Model_Listing $listing)
    {
        $this->getListing()->addProduct($product);
    }

    public function addProductByWebsiteListing(Mage_Catalog_Model_Product $product, Ess_M2ePro_Model_Listing $listing)
    {
        $this->getListing()->addProduct($product);
    }

    //####################################
}