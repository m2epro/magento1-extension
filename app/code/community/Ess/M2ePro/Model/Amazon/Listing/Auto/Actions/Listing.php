<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Auto_Actions_Listing extends Ess_M2ePro_Model_Listing_Auto_Actions_Listing
{
    //####################################

    public function deleteProduct(Mage_Catalog_Model_Product $product, $deletingMode)
    {
        if ($deletingMode == Ess_M2ePro_Model_Listing::DELETING_MODE_NONE) {
            return;
        }

        $listingsProducts = $this->getListing()->getProducts(true,array('product_id'=>(int)$product->getId()));

        if (count($listingsProducts) <= 0) {
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

        if (empty($parentsForRemove)) {
            return;
        }

        foreach ($parentsForRemove as $parentListingProduct) {
            $parentListingProduct->setData('status', Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED);
            $parentListingProduct->deleteInstance();
        }
    }

    //####################################

    public function addProductByCategoryGroup(Mage_Catalog_Model_Product $product,
                                              Ess_M2ePro_Model_Listing_Auto_Category_Group $categoryGroup)
    {
        $listingProduct = $this->getListing()->addProduct($product);

        if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Auto_Category_Group $amazonCategoryGroup */
        $amazonCategoryGroup = $categoryGroup->getChildObject();

        $params = array(
            'template_description_id' => $amazonCategoryGroup->getAddingDescriptionTemplateId(),
        );

        $this->processAddedListingProduct($listingProduct, $params);
    }

    public function addProductByGlobalListing(Mage_Catalog_Model_Product $product, Ess_M2ePro_Model_Listing $listing)
    {
        $listingProduct = $this->getListing()->addProduct($product);

        if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing $amazonListing */
        $amazonListing = $listing->getChildObject();

        $params = array(
            'template_description_id' => $amazonListing->getAutoGlobalAddingDescriptionTemplateId(),
        );

        $this->processAddedListingProduct($listingProduct, $params);
    }

    public function addProductByWebsiteListing(Mage_Catalog_Model_Product $product, Ess_M2ePro_Model_Listing $listing)
    {
        $listingProduct = $this->getListing()->addProduct($product);

        if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing $amazonListing */
        $amazonListing = $listing->getChildObject();

        $params = array(
            'template_description_id' => $amazonListing->getAutoWebsiteAddingDescriptionTemplateId(),
        );

        $this->processAddedListingProduct($listingProduct, $params);
    }

    //####################################

    protected function processAddedListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params)
    {
        if (empty($params['template_description_id'])) {
            return;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();

        if (!$amazonListingProduct->getVariationManager()->isRelationParentType()) {

            $listingProduct->setData('template_description_id', $params['template_description_id']);
            $listingProduct->setData(
                'is_general_id_owner',
                Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES
            );

            $listingProduct->save();

            return;
        }

        $processor = $amazonListingProduct->getVariationManager()->getTypeModel()->getProcessor();

        if ($listingProduct->getMagentoProduct()->isBundleType() ||
            $listingProduct->getMagentoProduct()->isSimpleTypeWithCustomOptions()
        ) {
            $processor->process();
            return;
        }

        $detailsModel = Mage::getModel('M2ePro/Amazon_Marketplace_Details');
        $detailsModel->setMarketplaceId($listingProduct->getListing()->getMarketplaceId());

        /** @var Ess_M2ePro_Model_Template_Description $descriptionTemplate */
        $descriptionTemplate = Mage::helper('M2ePro/Component_Amazon')
            ->getModel('Template_Description')
            ->load($params['template_description_id']);

        /** @var Ess_M2ePro_Model_Amazon_Template_Description $amazonDescriptionTemplate */
        $amazonDescriptionTemplate = $descriptionTemplate->getChildObject();

        $possibleThemes = $detailsModel->getVariationThemes($amazonDescriptionTemplate->getProductDataNick());

        $productAttributes = $amazonListingProduct->getVariationManager()
            ->getTypeModel()
            ->getProductAttributes();

        foreach ($possibleThemes as $theme) {
            if (count($theme['attributes']) != count($productAttributes)) {
                continue;
            }

            $listingProduct->setData('template_description_id', $params['template_description_id']);
            $listingProduct->setData(
                'is_general_id_owner',
                Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES
            );

            break;
        }

        $listingProduct->save();

        $processor->process();
    }

    //####################################
}