<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Auto_Actions_Listing extends Ess_M2ePro_Model_Listing_Auto_Actions_Listing
{
    //########################################

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param $deletingMode
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function deleteProduct(Mage_Catalog_Model_Product $product, $deletingMode)
    {
        if ($deletingMode == Ess_M2ePro_Model_Listing::DELETING_MODE_NONE) {
            return;
        }

        $listingsProducts = $this->getListing()->getProducts(true, array('product_id'=>(int)$product->getId()));

        if (empty($listingsProducts)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Listing_Product[] $parentsForRemove */
        $parentsForRemove = array();

        foreach ($listingsProducts as $listingProduct) {
            if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
                return;
            }

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
            $walmartListingProduct = $listingProduct->getChildObject();

            if ($walmartListingProduct->getVariationManager()->isRelationParentType() &&
                $deletingMode == Ess_M2ePro_Model_Listing::DELETING_MODE_STOP_REMOVE
            ) {
                $parentsForRemove[$listingProduct->getId()] = $listingProduct;
                continue;
            }

            try {
                $instructionType = self::INSTRUCTION_TYPE_STOP;

                if ($deletingMode == Ess_M2ePro_Model_Listing::DELETING_MODE_STOP_REMOVE) {
                    $instructionType = self::INSTRUCTION_TYPE_STOP_AND_REMOVE;
                }

                $instruction = Mage::getModel('M2ePro/Listing_Product_Instruction');
                $instruction->setData(
                    array(
                    'listing_product_id' => $listingProduct->getId(),
                    'component'          => $listingProduct->getComponentMode(),
                    'type'               => $instructionType,
                    'initiator'          => self::INSTRUCTION_INITIATOR,
                    'priority'           => $listingProduct->isStoppable() ? 60 : 0,
                    )
                );
                $instruction->save();
            } catch (Exception $exception) {
            }
        }

        if (empty($parentsForRemove)) {
            return;
        }

        foreach ($parentsForRemove as $parentListingProduct) {
            $parentListingProduct->setData('status', Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED);
            $parentListingProduct->deleteInstance();
        }
    }

    //########################################

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param Ess_M2ePro_Model_Listing_Auto_Category_Group $categoryGroup
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function addProductByCategoryGroup(
        Mage_Catalog_Model_Product $product,
        Ess_M2ePro_Model_Listing_Auto_Category_Group $categoryGroup
    ) {
        $logData = array(
            'reason'     => __METHOD__,
            'rule_id'    => $categoryGroup->getId(),
            'rule_title' => $categoryGroup->getTitle(),
        );
        $listingProduct = $this->getListing()->addProduct(
            $product, Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
            false, true, $logData
        );

        if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Walmart_Listing_Auto_Category_Group $walmartCategoryGroup */
        $walmartCategoryGroup = $categoryGroup->getChildObject();

        $params = array(
            'template_category_id' => $walmartCategoryGroup->getAddingCategoryTemplateId(),
        );

        $this->processAddedListingProduct($listingProduct, $params);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param Ess_M2ePro_Model_Listing $listing
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function addProductByGlobalListing(Mage_Catalog_Model_Product $product, Ess_M2ePro_Model_Listing $listing)
    {
        $logData = array(
            'reason' => __METHOD__,
        );
        $listingProduct = $this->getListing()->addProduct(
            $product, Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
            false, true, $logData
        );

        if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
            return;
        }

        $this->logAddedToMagentoProduct($listingProduct);

        /** @var Ess_M2ePro_Model_Walmart_Listing $walmartListing */
        $walmartListing = $listing->getChildObject();

        $params = array(
            'template_category_id' => $walmartListing->getAutoGlobalAddingCategoryTemplateId(),
        );

        $this->processAddedListingProduct($listingProduct, $params);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param Ess_M2ePro_Model_Listing $listing
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function addProductByWebsiteListing(Mage_Catalog_Model_Product $product, Ess_M2ePro_Model_Listing $listing)
    {
        $logData = array(
            'reason' => __METHOD__,
        );
        $listingProduct = $this->getListing()->addProduct(
            $product, Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
            false, true, $logData
        );

        if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Walmart_Listing $walmartListing */
        $walmartListing = $listing->getChildObject();

        $params = array(
            'template_category_id' => $walmartListing->getAutoWebsiteAddingCategoryTemplateId(),
        );

        $this->processAddedListingProduct($listingProduct, $params);
    }

    //########################################

    protected function processAddedListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params)
    {
        if (empty($params['template_category_id'])) {
            return;
        }

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $listingProduct->getChildObject();

        $listingProduct->setData('template_category_id', $params['template_category_id']);
        $listingProduct->save();

        if ($walmartListingProduct->getVariationManager()->isRelationParentType()) {
            $walmartListingProduct->addVariationAttributes();
        }
    }

    //########################################
}