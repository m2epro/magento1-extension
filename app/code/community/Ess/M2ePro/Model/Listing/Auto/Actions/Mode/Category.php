<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Listing_Auto_Actions_Mode_Category
    extends Ess_M2ePro_Model_Listing_Auto_Actions_Mode_Abstract
{
    private $cacheLoadedListings = array();
    private $cacheAutoCategoriesByCategoryId = array();

    //########################################

    public function synchWithAddedCategoryId($categoryId, $websiteId)
    {
        foreach ($this->getAutoCategoriesByCategory($categoryId) as $autoCategory) {

            /** @var $autoCategory Ess_M2ePro_Model_Listing_Auto_Category */

            if ($autoCategory->getGroup()->isAddingModeNone()) {
                continue;
            }

            $listing = $this->getLoadedListing($autoCategory->getGroup()->getListingId());

            if ((int)$listing->getData('store_website_id') != $websiteId) {
                continue;
            }

            if (!$listing->isAutoModeCategory()) {
                continue;
            }

            if (!$autoCategory->getGroup()->isAddingAddNotVisibleYes()) {
                if ($this->getProduct()->getVisibility()
                    == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE) {
                    continue;
                }
            }

            $this->getListingObject($listing)->addProductByCategoryGroup(
                $this->getProduct(), $autoCategory->getGroup()
            );
        }
    }

    public function synchWithDeletedCategoryId($categoryId, $websiteId)
    {
        foreach ($this->getAutoCategoriesByCategory($categoryId) as $autoCategory) {

            /** @var $autoCategory Ess_M2ePro_Model_Listing_Auto_Category */

            if ($autoCategory->getGroup()->isDeletingModeNone()) {
                continue;
            }

            /** @var $listing Ess_M2ePro_Model_Listing */

            $listing = $this->getLoadedListing($autoCategory->getGroup()->getListingId());

            if ((int)$listing->getData('store_website_id') != $websiteId) {
                continue;
            }

            if (!$listing->isAutoModeCategory()) {
                continue;
            }

            $this->getListingObject($listing)->deleteProduct(
                $this->getProduct(),
                $autoCategory->getGroup()->getDeletingMode()
            );
        }
    }

    //########################################

    private function getLoadedListing($listing)
    {
        if ($listing instanceof Ess_M2ePro_Model_Listing) {
            return $listing;
        }

        $listingId = (int)$listing;

        if (isset($this->cacheLoadedListings[$listingId])) {
            return $this->cacheLoadedListings[$listingId];
        }

        /** @var $listing Ess_M2ePro_Model_Listing */
        $listing = Mage::helper('M2ePro/Component')->getCachedUnknownObject('Listing',$listingId);

        /** @var $listingStoreObject Mage_Core_Model_Store */
        $listingStoreObject = Mage::getModel('core/store')->load($listing->getStoreId());
        $listing->setData('store_website_id',$listingStoreObject->getWebsite()->getId());

        return $this->cacheLoadedListings[$listingId] = $listing;
    }

    private function getAutoCategoriesByCategory($categoryId)
    {
        if (isset($this->cacheAutoCategoriesByCategoryId[$categoryId])) {
            return $this->cacheAutoCategoriesByCategoryId[$categoryId];
        }

        return $this->cacheAutoCategoriesByCategoryId[$categoryId] =
                                Mage::getModel('M2ePro/Listing_Auto_Category')
                                        ->getCollection()
                                        ->addFieldToFilter('category_id', $categoryId)
                                        ->getItems();
    }

    //########################################
}