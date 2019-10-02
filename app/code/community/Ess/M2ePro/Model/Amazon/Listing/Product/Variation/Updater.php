<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Updater
    extends Ess_M2ePro_Model_Listing_Product_Variation_Updater
{
    protected $_parentListingsProductsForProcessing = array();

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
        foreach ($this->_parentListingsProductsForProcessing as $listingProduct) {
            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();
            $amazonListingProduct->getVariationManager()->getTypeModel()->getProcessor()->process();
        }
    }

    //########################################

    protected function checkChangeAsVariationProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();
        $magentoProduct = $listingProduct->getMagentoProduct();

        if (!$magentoProduct->isProductWithVariations() || $variationManager->isVariationProduct()) {
            return false;
        }

        if ($magentoProduct->isSimpleTypeWithCustomOptions() ||
            $magentoProduct->isBundleType() ||
            $magentoProduct->isDownloadableTypeWithSeparatedLinks()
        ) {
            $listingProduct->setData(
                'is_general_id_owner', Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_NO
            );
            $listingProduct->setData('template_description_id', null);
        }

        $listingProduct->setData('is_variation_product', 1);
        $variationManager->setIndividualType();
        $variationManager->getTypeModel()->resetProductVariation();

        return true;
    }

    protected function checkChangeAsNotVariationProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
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
            $listingProduct->isDeleted(true);
        } else {
            $variationManager->setSimpleType();
        }

        return true;
    }

    // ---------------------------------------

    protected function checkVariationStructureChanges(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();

        if ($variationManager->isRelationParentType()) {
            $this->_parentListingsProductsForProcessing[$listingProduct->getId()] = $listingProduct;
            return;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_PhysicalUnit $typeModel */
        $typeModel = $variationManager->getTypeModel();

        if (!$listingProduct->getMagentoProduct()->isSimpleType() &&
            !$listingProduct->getMagentoProduct()->isDownloadableType()
        ) {
            $typeModel->inspectAndFixProductOptionsIds();
        }

        if (!$typeModel->isActualProductAttributes()) {
            if ($variationManager->isRelationChildType()) {
                /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $typeModel */
                $this->_parentListingsProductsForProcessing[$typeModel->getParentListingProduct()->getId()]
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
                $this->_parentListingsProductsForProcessing[$typeModel->getParentListingProduct()->getId()]
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
                $this->_parentListingsProductsForProcessing[$typeModel->getParentListingProduct()->getId()]
                    = $typeModel->getParentListingProduct();
            }
        }
    }

    //########################################
}
