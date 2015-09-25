<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Variation_Updater
    extends Ess_M2ePro_Model_Listing_Product_Variation_Updater
{
    // ########################################

    public function process(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if ($this->checkChangeAsVariationProduct($listingProduct)) {
            return;
        }

        if ($this->checkChangeAsNotVariationProduct($listingProduct)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Buy_Listing_Product_Variation_Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();

        if (!$variationManager->isVariationProduct()) {
            return;
        }

        $this->checkVariationStructureChanges($listingProduct);
    }

    // ########################################

    private function checkChangeAsVariationProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        /** @var Ess_M2ePro_Model_Buy_Listing_Product_Variation_Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();
        $isVariationMagentoProduct = $listingProduct->getMagentoProduct()->isProductWithVariations();

        if (!$isVariationMagentoProduct || $variationManager->isVariationProduct()) {
            return false;
        }

        $listingProduct->setData('is_variation_product',1)->save();
        $variationManager->resetProductVariation();

        return true;
    }

    private function checkChangeAsNotVariationProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        /** @var Ess_M2ePro_Model_Buy_Listing_Product_Variation_Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();
        $isVariationMagentoProduct = $listingProduct->getMagentoProduct()->isProductWithVariations();

        if ($isVariationMagentoProduct || !$variationManager->isVariationProduct()) {
            return false;
        }

        $variationManager->clearVariationData();
        $listingProduct->setData('is_variation_product', 0)->save();

        return true;
    }

    // ----------------------------------------

    private function checkVariationStructureChanges(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        /** @var Ess_M2ePro_Model_Buy_Listing_Product_Variation_Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();

        if (!$variationManager->isActualProductAttributes()) {
            $variationManager->resetProductVariation();
            return;
        }

        if ($variationManager->isVariationProductMatched() && !$variationManager->isActualProductVariation()) {
            $variationManager->unsetProductVariation();
        }
    }

    // ########################################
}