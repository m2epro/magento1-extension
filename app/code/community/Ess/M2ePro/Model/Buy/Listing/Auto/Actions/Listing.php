<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Listing_Auto_Actions_Listing extends Ess_M2ePro_Model_Listing_Auto_Actions_Listing
{
    //########################################

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param Ess_M2ePro_Model_Listing_Auto_Category_Group $categoryGroup
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function addProductByCategoryGroup(Mage_Catalog_Model_Product $product,
                                              Ess_M2ePro_Model_Listing_Auto_Category_Group $categoryGroup)
    {
        $this->getListing()->addProduct($product);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param Ess_M2ePro_Model_Listing $listing
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function addProductByGlobalListing(Mage_Catalog_Model_Product $product, Ess_M2ePro_Model_Listing $listing)
    {
        $this->getListing()->addProduct($product);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param Ess_M2ePro_Model_Listing $listing
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function addProductByWebsiteListing(Mage_Catalog_Model_Product $product, Ess_M2ePro_Model_Listing $listing)
    {
        $this->getListing()->addProduct($product);
    }

    //########################################
}