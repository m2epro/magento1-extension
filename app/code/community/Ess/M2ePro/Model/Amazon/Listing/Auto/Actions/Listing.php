<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Auto_Actions_Listing extends Ess_M2ePro_Model_Listing_Auto_Actions_Listing
{
    /** @var Ess_M2ePro_Model_Amazon_Template_ProductType_Repository*/
    private $templateProductTypeRepository;

    public function __construct()
    {
        $this->templateProductTypeRepository = Mage::getModel('M2ePro/Amazon_Template_ProductType_Repository');
    }

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

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            if ($amazonListingProduct->getVariationManager()->isRelationParentType() &&
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

        /** @var Ess_M2ePro_Model_Amazon_Listing_Auto_Category_Group $amazonCategoryGroup */
        $amazonCategoryGroup = $categoryGroup->getChildObject();

        $params = array(
            'template_product_type_id' => $amazonCategoryGroup->getAddingProductTypeTemplateId(),
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

        /** @var Ess_M2ePro_Model_Amazon_Listing $amazonListing */
        $amazonListing = $listing->getChildObject();

        $params = array(
            'template_product_type_id' => $amazonListing->getAutoGlobalAddingProductTypeTemplateId(),
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

        /** @var Ess_M2ePro_Model_Amazon_Listing $amazonListing */
        $amazonListing = $listing->getChildObject();

        $params = array(
            'template_product_type_id' => $amazonListing->getAutoWebsiteAddingProductTypeTemplateId(),
        );

        $this->processAddedListingProduct($listingProduct, $params);
    }

    //########################################

    protected function processAddedListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params)
    {
        if (empty($params['template_product_type_id'])) {
            return;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();

        if (!$amazonListingProduct->getVariationManager()->isRelationParentType()) {
            $listingProduct->setData('template_product_type_id', $params['template_product_type_id']);
            $listingProduct->setData(
                'is_general_id_owner',
                Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES
            );

            $listingProduct->save();

            return;
        }

        $processor = $amazonListingProduct->getVariationManager()->getTypeModel()->getProcessor();

        if ($listingProduct->getMagentoProduct()->isBundleType() ||
            $listingProduct->getMagentoProduct()->isSimpleTypeWithCustomOptions() ||
            $listingProduct->getMagentoProduct()->isDownloadableTypeWithSeparatedLinks()
        ) {
            $processor->process();

            return;
        }

        $productTypeTemplate = $this->templateProductTypeRepository->find($params['template_product_type_id']);
        if ($productTypeTemplate === null) {
            return;
        }

        $possibleThemes = $productTypeTemplate->getDictionary()->getVariationThemes();

        $productAttributes = $amazonListingProduct->getVariationManager()
            ->getTypeModel()
            ->getProductAttributes();

        foreach ($possibleThemes as $theme) {
            if (count($theme['attributes']) != count($productAttributes)) {
                continue;
            }

            $listingProduct->setData('template_product_type_id', $params['template_product_type_id']);
            $listingProduct->setData(
                'is_general_id_owner',
                Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES
            );

            break;
        }

        $listingProduct->save();

        $processor->process();
    }

    //########################################
}