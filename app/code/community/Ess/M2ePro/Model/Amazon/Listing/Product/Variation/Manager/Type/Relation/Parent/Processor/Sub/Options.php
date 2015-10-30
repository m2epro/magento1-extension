<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2EPro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Options
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Abstract
{
    //########################################

    /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Option $optionMatcher */
    private $optionMatcher = null;

    //########################################

    protected function check()
    {
        if (count($this->getProcessor()->getTypeModel()->getChildListingsProducts()) <= 0) {
            return;
        }

        if (!$this->getProcessor()->isGeneralIdOwner() && !$this->getProcessor()->isGeneralIdSet()) {
            foreach ($this->getProcessor()->getTypeModel()->getChildListingsProducts() as $listingProduct) {
                if ($listingProduct->isNotListed()) {
                    $this->getProcessor()->tryToRemoveChildListingProduct($listingProduct);
                    continue;
                }

                /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
                $amazonListingProduct = $listingProduct->getChildObject();
                $amazonListingProduct->getVariationManager()->getTypeModel()->unsetChannelVariation();
                $amazonListingProduct->getVariationManager()->setIndividualType();
            }

            return;
        }

        foreach ($this->getProcessor()->getTypeModel()->getChildListingsProducts() as $listingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $variationManager */
            $variationManager = $listingProduct->getChildObject()->getVariationManager();

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $typeModel */
            $typeModel = $variationManager->getTypeModel();

            if (!$typeModel->isActualProductAttributes() ||
                !$typeModel->isActualMatchedAttributes() ||
                ($typeModel->isVariationProductMatched() &&
                !$typeModel->isActualProductVariation())
            ) {
                $typeModel->resetProductVariation();
            }

            if (!$typeModel->isVariationProductMatched() && !$typeModel->isVariationChannelMatched()) {
                $this->getProcessor()->tryToRemoveChildListingProduct($listingProduct);
                continue;
            }

            if ($typeModel->isVariationProductMatched() && $typeModel->isVariationChannelMatched() &&
                count($typeModel->getProductOptions()) != count($typeModel->getChannelOptions())
            ) {
                $this->getProcessor()->tryToRemoveChildListingProduct($listingProduct);
            }
        }
    }

    protected function execute()
    {
        if (!$this->getProcessor()->isGeneralIdSet()) {
            return;
        }

        if (!$this->getProcessor()->getTypeModel()->hasMatchedAttributes()) {
            return;
        }

        $this->matchExistingChildren();
        $this->deleteBrokenChildren();
        $this->matchNewChildren();

        if ($this->canCreateNewProductChildren()) {
            $this->createNewProductChildren();
        }

        if ($this->getProcessor()->getTypeModel()->hasMatchedAttributes()) {
            $this->setMatchedAttributesToChildren();
        }
    }

    //########################################

    private function matchExistingChildren()
    {
        foreach ($this->getProcessor()->getTypeModel()->getChildListingsProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            if (!$childListingProduct->getId()) {
                continue;
            }

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if ($childTypeModel->isVariationChannelMatched() && $childTypeModel->isVariationProductMatched()) {
                continue;
            }

            if ($childTypeModel->isVariationChannelMatched()) {
                $this->matchEmptyProductOptionsChild($childListingProduct);
                continue;
            }

            if ($childListingProduct->isLocked()) {
                continue;
            }

            $this->matchEmptyChannelOptionsChild($childListingProduct);
        }
    }

    private function deleteBrokenChildren()
    {
        foreach ($this->getProcessor()->getTypeModel()->getChildListingsProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if ($childTypeModel->isVariationChannelMatched() && $childTypeModel->isVariationProductMatched()) {
                continue;
            }

            if (!$childTypeModel->isVariationChannelMatched() && !$childTypeModel->isVariationProductMatched()) {
                $this->getProcessor()->tryToRemoveChildListingProduct($childListingProduct);
                continue;
            }

            if ($childListingProduct->isLocked()
                || $childListingProduct->getStatus() != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED
            ) {
                continue;
            }

            if (!$childTypeModel->isVariationProductMatched()) {
                $this->getProcessor()->tryToRemoveChildListingProduct($childListingProduct);
                continue;
            }

            if ($this->getProcessor()->isGeneralIdOwner()) {
                continue;
            }

            $this->getProcessor()->tryToRemoveChildListingProduct($childListingProduct);
        }
    }

    private function matchNewChildren()
    {
        $channelOptions = $this->getProcessor()->getTypeModel()->getUnusedChannelOptions();
        $productOptions = $this->getProcessor()->getTypeModel()->getNotRemovedUnusedProductOptions();

        if (empty($channelOptions) || empty($productOptions)) {
            return;
        }

        $matcher = $this->getOptionMatcher();
        $matcher->setDestinationOptions($channelOptions);

        foreach ($productOptions as $productOption) {
            $generalId = $matcher->getMatchedOptionGeneralId($productOption);
            if (is_null($generalId)) {
                continue;
            }

            $this->getProcessor()->getTypeModel()->createChildListingProduct(
                $productOption, $channelOptions[$generalId], $generalId
            );
        }
    }

    private function canCreateNewProductChildren()
    {
        $channelOptions = $this->getProcessor()->getTypeModel()->getUnusedChannelOptions();
        $productOptions = $this->getProcessor()->getTypeModel()->getNotRemovedUnusedProductOptions();

        if (!$this->getProcessor()->isGeneralIdOwner() || count($channelOptions) > 0 || count($productOptions) <= 0) {
            return false;
        }

        foreach ($this->getProcessor()->getTypeModel()->getChildListingsProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if (!$childTypeModel->isVariationProductMatched()) {
                return false;
            }
        }

        return true;
    }

    private function createNewProductChildren()
    {
        $productOptions = $this->getProcessor()->getTypeModel()->getNotRemovedUnusedProductOptions();

        foreach ($productOptions as $productOption) {
            $this->getProcessor()->getTypeModel()->createChildListingProduct($productOption);
        }
    }

    private function setMatchedAttributesToChildren()
    {
        foreach ($this->getProcessor()->getTypeModel()->getChildListingsProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonChildListingProduct */
            $amazonChildListingProduct = $childListingProduct->getChildObject();
            $childTypeModel = $amazonChildListingProduct->getVariationManager()->getTypeModel();

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

    private function matchEmptyProductOptionsChild(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $typeModel */
        $typeModel = $amazonListingProduct->getVariationManager()->getTypeModel();

        $channelOptions = $typeModel->getChannelOptions();
        $productOptions = array_merge(
            $this->getProcessor()->getTypeModel()->getNotRemovedUnusedProductOptions(),
            $this->getProcessor()->getTypeModel()->getUsedProductOptions(true)
        );

        $matcher = $this->getOptionMatcher();
        $matcher->setDestinationOptions(array($amazonListingProduct->getGeneralId() => $channelOptions));

        foreach ($productOptions as $productOption) {
            $generalId = $matcher->getMatchedOptionGeneralId($productOption);

            if (is_null($generalId)) {
                continue;
            }

            $existChild = $this->findChildByProductOptions($productOption);
            if (!is_null($existChild)) {
                $this->getProcessor()->tryToRemoveChildListingProduct($existChild);
            }

            $productVariation = $this->getProcessor()->getProductVariation($productOption);
            if (empty($productVariation)) {
                continue;
            }

            $typeModel->setProductVariation($productVariation);
            $listingProduct->save();

            break;
        }
    }

    private function matchEmptyChannelOptionsChild(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $typeModel */
        $typeModel = $listingProduct->getChildObject()->getVariationManager()->getTypeModel();

        $channelOptions = array_merge(
            $this->getProcessor()->getTypeModel()->getUnusedChannelOptions(),
            $this->getProcessor()->getTypeModel()->getUsedChannelOptions(true)
        );

        if (empty($channelOptions)) {
            return;
        }

        if (!$typeModel->isVariationProductMatched()) {
            return;
        }

        $matcher = $this->getOptionMatcher();
        $matcher->setDestinationOptions($channelOptions);

        $generalId = $matcher->getMatchedOptionGeneralId($typeModel->getProductOptions());
        if (is_null($generalId)) {
            return;
        }

        $existChild = $this->findChildByChannelOptions($channelOptions[$generalId]);
        if (!is_null($existChild)) {
            $this->getProcessor()->tryToRemoveChildListingProduct($existChild);
        }

        $listingProduct->setData('general_id', $generalId);
        $typeModel->setChannelVariation($channelOptions[$generalId]);
    }

    //########################################

    private function findChildByProductOptions(array $productOptions)
    {
        return $this->findChildByOptions($productOptions, 'product');
    }

    private function findChildByChannelOptions(array $channelOptions)
    {
        return $this->findChildByOptions($channelOptions, 'channel');
    }

    private function findChildByOptions(array $options, $type)
    {
        foreach ($this->getProcessor()->getTypeModel()->getChildListingsProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if ($type == 'product' &&
                $childTypeModel->isVariationProductMatched() &&
                $options == $childTypeModel->getProductOptions()
            ) {
                return $childListingProduct;
            }

            if ($type == 'channel' &&
                $childTypeModel->isVariationChannelMatched() &&
                $options == $childTypeModel->getChannelOptions()
            ) {
                return $childListingProduct;
            }
        }

        return null;
    }

    //########################################

    private function getOptionMatcher()
    {
        if (!is_null($this->optionMatcher)) {
            return $this->optionMatcher;
        }

        $this->optionMatcher = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Matcher_Option');
        $this->optionMatcher->setMagentoProduct($this->getProcessor()->getListingProduct()->getMagentoProduct());
        $this->optionMatcher->setMatchedAttributes($this->getProcessor()->getTypeModel()->getMatchedAttributes());

        return $this->optionMatcher;
    }

    //########################################
}