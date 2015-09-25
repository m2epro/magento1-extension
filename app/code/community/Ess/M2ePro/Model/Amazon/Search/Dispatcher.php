<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Search_Dispatcher
{
    // ########################################

    public function runCustom(Ess_M2ePro_Model_Listing_Product $listingProduct, $query)
    {
        if (empty($query)) {
            return false;
        }

        try {

            /** @var Ess_M2ePro_Model_Amazon_Search_Custom $customSearch */
            $customSearch = Mage::getModel('M2ePro/Amazon_Search_Custom');
            $customSearch->setListingProduct($listingProduct);
            $customSearch->setQuery($query);

            $searchResult = $customSearch->process();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            $searchResult = false;
        }

        return $searchResult;
    }

    public function runSettings(array $listingsProducts)
    {
        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($listingsProducts as $key => $listingProduct) {

            if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
                unset($listingsProducts[$key]);
                continue;
            }

            if (!$this->checkSearchConditions($listingProduct)) {
                unset($listingsProducts[$key]);
                continue;
            }
        }

        if (empty($listingsProducts)) {
            return false;
        }

        try {

            /** @var Ess_M2ePro_Model_Amazon_Search_Settings $settingsSearch */
            $settingsSearch = Mage::getModel('M2ePro/Amazon_Search_Settings');

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            foreach ($listingsProducts as $listingProduct) {
                $settingsSearch->setListingProduct($listingProduct);
                $settingsSearch->resetStep();
                $settingsSearch->process();
            }

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return false;
        }

        return true;
    }

    // ########################################

    private function checkSearchConditions(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();

        return $listingProduct->isNotListed() &&
               !$amazonListingProduct->isGeneralIdOwner() &&
               !$amazonListingProduct->getGeneralId();
    }

    // ########################################
}