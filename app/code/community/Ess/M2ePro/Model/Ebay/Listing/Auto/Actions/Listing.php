<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Auto_Actions_Listing extends Ess_M2ePro_Model_Listing_Auto_Actions_Listing
{
    //####################################

    public function addProductByCategoryGroup(Mage_Catalog_Model_Product $product,
                                              Ess_M2ePro_Model_Listing_Auto_Category_Group $categoryGroup)
    {
        $listingProduct = $this->getListing()->addProduct($product);

        if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Auto_Category_Group $ebayCategoryGroup */
        $ebayCategoryGroup = $categoryGroup->getChildObject();

        $params = array(
            'template_category_id' => $ebayCategoryGroup->getAddingTemplateCategoryId(),
            'template_other_category_id' => $ebayCategoryGroup->getAddingTemplateOtherCategoryId(),
        );

        $this->processAddedListingProduct($listingProduct, $params);
    }

    public function addProductByGlobalListing(Mage_Catalog_Model_Product $product, Ess_M2ePro_Model_Listing $listing)
    {
        $listingProduct = $this->getListing()->addProduct($product);

        if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing $ebayListing */
        $ebayListing = $listing->getChildObject();

        $params = array(
            'template_category_id' => $ebayListing->getAutoGlobalAddingTemplateCategoryId(),
            'template_other_category_id' => $ebayListing->getAutoGlobalAddingTemplateOtherCategoryId(),
        );

        $this->processAddedListingProduct($listingProduct, $params);
    }

    public function addProductByWebsiteListing(Mage_Catalog_Model_Product $product, Ess_M2ePro_Model_Listing $listing)
    {
        $listingProduct = $this->getListing()->addProduct($product);

        if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing $ebayListing */
        $ebayListing = $listing->getChildObject();

        $params = array(
            'template_category_id' => $ebayListing->getAutoWebsiteAddingTemplateCategoryId(),
            'template_other_category_id' => $ebayListing->getAutoWebsiteAddingTemplateOtherCategoryId(),
        );

        $this->processAddedListingProduct($listingProduct, $params);
    }

    //####################################

    protected function processAddedListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params)
    {
        if (!empty($params['template_category_id'])) {
            $listingProduct->setData('template_category_id',$params['template_category_id']);
        }

        if (!empty($params['template_other_category_id'])) {
            $listingProduct->setData('template_other_category_id',$params['template_other_category_id']);
        }

        $listingProduct->save();
    }

    //####################################
}