<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Listing_Auto_Actions_Listing
{
    /**
     * @var null|Ess_M2ePro_Model_Listing
     */
    private $listing = NULL;

    //########################################

    public function setListing(Ess_M2ePro_Model_Listing $listing)
    {
        $this->listing = $listing;
    }

    /**
     * @return Ess_M2ePro_Model_Listing
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getListing()
    {
        if (!($this->listing instanceof Ess_M2ePro_Model_Listing)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Property "Listing" should be set first.');
        }

        return $this->listing;
    }

    //########################################

    public function deleteProduct(Mage_Catalog_Model_Product $product, $deletingMode)
    {
        if ($deletingMode == Ess_M2ePro_Model_Listing::DELETING_MODE_NONE) {
            return;
        }

        $listingsProducts = $this->getListing()->getProducts(true,array('product_id'=>(int)$product->getId()));

        if (count($listingsProducts) <= 0) {
            return;
        }

        foreach ($listingsProducts as $listingProduct) {

            if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
                return;
            }

            try {

                if ($deletingMode == Ess_M2ePro_Model_Listing::DELETING_MODE_STOP) {
                    $listingProduct->isStoppable() && Mage::getModel('M2ePro/StopQueue')->add($listingProduct);
                }

                if ($deletingMode == Ess_M2ePro_Model_Listing::DELETING_MODE_STOP_REMOVE) {
                    $listingProduct->isStoppable() && Mage::getModel('M2ePro/StopQueue')->add($listingProduct);
                    $listingProduct->addData(array('status'=>Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED))->save();
                    $listingProduct->deleteInstance();
                }

            } catch (Exception $exception) {}
        }
    }

    //########################################

    abstract public function addProductByCategoryGroup(Mage_Catalog_Model_Product $product,
                                                       Ess_M2ePro_Model_Listing_Auto_Category_Group $categoryGroup);

    abstract public function addProductByGlobalListing(Mage_Catalog_Model_Product $product,
                                                       Ess_M2ePro_Model_Listing $listing);

    abstract public function addProductByWebsiteListing(Mage_Catalog_Model_Product $product,
                                                        Ess_M2ePro_Model_Listing $listing);

    //########################################
}