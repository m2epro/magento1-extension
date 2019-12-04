<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Options
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Abstract
{
    //########################################

    protected function check()
    {
        $products = $this->getProcessor()->getTypeModel()->getChildListingsProducts();
        if (empty($products)) {
            return;
        }

        foreach ($this->getProcessor()->getTypeModel()->getChildListingsProducts() as $listingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
            $walmartListingProduct = $listingProduct->getChildObject();

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Child $typeModel */
            $typeModel = $walmartListingProduct->getVariationManager()->getTypeModel();

            if (!$typeModel->isActualProductAttributes() ||
                !$typeModel->isActualMatchedAttributes() ||
                ($typeModel->isVariationProductMatched() &&
                !$typeModel->isActualProductVariation())
            ) {
                $typeModel->resetProductVariation();
            }

            if ($typeModel->isVariationProductMatched() &&
                count($typeModel->getProductOptions()) != count($typeModel->getChannelOptions())
            ) {
                $this->getProcessor()->tryToRemoveChildListingProduct($listingProduct);
            }
        }
    }

    protected function execute()
    {
        if (!$this->getProcessor()->getTypeModel()->hasMatchedAttributes()) {
            return;
        }

        $this->deleteBrokenChildren();

        if ($this->canCreateNewProductChildren()) {
            $this->matchNewChildren();
        }

        if ($this->getProcessor()->getTypeModel()->hasMatchedAttributes()) {
            $this->setMatchedAttributesToChildren();
        }
    }

    //########################################

    protected function canCreateNewProductChildren()
    {
        $productOptions = $this->getProcessor()->getTypeModel()->getNotRemovedUnusedProductOptions();

        if (empty($productOptions)) {
            return false;
        }

        foreach ($this->getProcessor()->getTypeModel()->getChildListingsProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if (!$childTypeModel->isVariationProductMatched()) {
                return false;
            }
        }

        return true;
    }

    protected function deleteBrokenChildren()
    {
        foreach ($this->getProcessor()->getTypeModel()->getChildListingsProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartChildListingProduct */
            $walmartChildListingProduct = $childListingProduct->getChildObject();

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $walmartChildListingProduct->getVariationManager()->getTypeModel();

            if ($childTypeModel->isVariationProductMatched()) {
                continue;
            }

            if ($childListingProduct->isLocked() || $walmartChildListingProduct->getSku()
                || $childListingProduct->getStatus() != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED
            ) {
                continue;
            }

            $this->getProcessor()->tryToRemoveChildListingProduct($childListingProduct);
        }
    }

    protected function matchNewChildren()
    {
        $productOptions    = $this->getProcessor()->getTypeModel()->getNotRemovedUnusedProductOptions();
        $matchedAttributes = $this->getProcessor()->getTypeModel()->getMatchedAttributes();

        foreach ($productOptions as $productOption) {
            $channelOption = array();
            foreach ($productOption as $attribute => $value) {
                $channelOption[$matchedAttributes[$attribute]] = $value;
            }

            $this->getProcessor()->getTypeModel()->createChildListingProduct($productOption, $channelOption);
        }
    }

    protected function setMatchedAttributesToChildren()
    {
        foreach ($this->getProcessor()->getTypeModel()->getChildListingsProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartChildListingProduct */
            $walmartChildListingProduct = $childListingProduct->getChildObject();
            $childTypeModel = $walmartChildListingProduct->getVariationManager()->getTypeModel();

            $correctMatchedAttributes = $childTypeModel->getCorrectMatchedAttributes();

            if ($childTypeModel->isActualMatchedAttributes() && !empty($correctMatchedAttributes)) {
                continue;
            }

            $childTypeModel->setCorrectMatchedAttributes(
                $this->getProcessor()->getTypeModel()->getMatchedAttributes()
            );
        }
    }

    //########################################
}
