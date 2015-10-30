<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Updater
    extends Ess_M2ePro_Model_Listing_Product_Variation_Updater
{
    private $parentListingsProductsForProcessing = array();

    //########################################

    public function process(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if ($this->checkChangeAsVariationProduct($listingProduct)) {
            return;
        }

        if ($this->checkChangeAsNotVariationProduct($listingProduct)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();

        if (!$variationManager->isVariationProduct()) {
            return;
        }

        $this->checkVariationStructureChanges($listingProduct);
    }

    public function afterMassProcessEvent()
    {
        foreach ($this->parentListingsProductsForProcessing as $listingProduct) {
            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();
            $amazonListingProduct->getVariationManager()->getTypeModel()->getProcessor()->process();
        }
    }

    //########################################

    private function checkChangeAsVariationProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();
        $magentoProduct = $listingProduct->getMagentoProduct();

        if (!$magentoProduct->isProductWithVariations() || $variationManager->isVariationProduct()) {
            return false;
        }

        if ($magentoProduct->isSimpleTypeWithCustomOptions() || $magentoProduct->isBundleType()) {
            $listingProduct->setData(
                'is_general_id_owner', Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_NO
            );
            $listingProduct->setData('template_description_id', null);
        }

        $listingProduct->setData('is_variation_product', 1);
        $variationManager->setRelationParentType();
        $variationManager->getTypeModel()->resetProductAttributes(false);
        $variationManager->getTypeModel()->getProcessor()->process();

        return true;
    }

    private function checkChangeAsNotVariationProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();
        $isVariationMagentoProduct = $listingProduct->getMagentoProduct()->isProductWithVariations();

        if ($isVariationMagentoProduct || !$variationManager->isVariationProduct()) {
            return false;
        }

        $variationManager->getTypeModel()->clearTypeData();

        if ($variationManager->isRelationParentType()) {
            $listingProduct->setData('general_id', NULL);
            $listingProduct->setData(
                'is_general_id_owner', Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_NO
            );
            $listingProduct->setData('status', Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED);

            $listingProduct->deleteInstance();
        } else {
            $variationManager->setSimpleType();
        }

        return true;
    }

    // ---------------------------------------

    private function checkVariationStructureChanges(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();

        if ($variationManager->isRelationParentType()) {
            $this->parentListingsProductsForProcessing[$listingProduct->getId()] = $listingProduct;
            return;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_PhysicalUnit $typeModel */
        $typeModel = $variationManager->getTypeModel();

        if (!$listingProduct->getMagentoProduct()->isSimpleType()) {
            $typeModel->inspectAndFixProductOptionsIds();
        }

        if (!$typeModel->isActualProductAttributes()) {

            if ($variationManager->isRelationChildType()) {
                /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $typeModel */
                $this->parentListingsProductsForProcessing[$typeModel->getParentListingProduct()->getId()]
                    = $typeModel->getParentListingProduct();
                return;
            }

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Individual $typeModel */

            $typeModel->resetProductVariation();

            return;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_PhysicalUnit $typeModel */

        if ($typeModel->isVariationProductMatched() && !$typeModel->isActualProductVariation()) {

            if ($variationManager->isRelationChildType()) {
                /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $typeModel */
                $this->parentListingsProductsForProcessing[$typeModel->getParentListingProduct()->getId()]
                    = $typeModel->getParentListingProduct();
                return;
            }

            $typeModel->unsetProductVariation();
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $typeModel */

        if ($variationManager->isRelationChildType() &&
            $typeModel->getParentTypeModel()->getVirtualChannelAttributes()
        ) {
            if (!$typeModel->getParentTypeModel()->isActualVirtualChannelAttributes()) {
                $this->parentListingsProductsForProcessing[$typeModel->getParentListingProduct()->getId()]
                    = $typeModel->getParentListingProduct();
            }
        }
    }

    //########################################
}