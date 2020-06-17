<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Auto_Actions_Listing extends Ess_M2ePro_Model_Listing_Auto_Actions_Listing
{
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

        /** @var Ess_M2ePro_Model_Ebay_Listing_Auto_Category_Group $group */
        $group = $categoryGroup->getChildObject();

        $params = array(
            'template_category_id'                 => $group->getAddingTemplateCategoryId(),
            'template_category_secondary_id'       => $group->getAddingTemplateCategorySecondaryId(),
            'template_store_category_id'           => $group->getAddingTemplateStoreCategoryId(),
            'template_store_category_secondary_id' => $group->getAddingTemplateStoreCategorySecondaryId(),
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

        /** @var Ess_M2ePro_Model_Ebay_Listing $eListing */
        $eListing = $listing->getChildObject();

        $params = array(
            'template_category_id'                 => $eListing->getAutoGlobalAddingTemplateCategoryId(),
            'template_category_secondary_id'       => $eListing->getAutoGlobalAddingTemplateCategorySecondaryId(),
            'template_store_category_id'           => $eListing->getAutoGlobalAddingTemplateStoreCategoryId(),
            'template_store_category_secondary_id' => $eListing->getAutoGlobalAddingTemplateStoreCategorySecondaryId(),
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

        /** @var Ess_M2ePro_Model_Ebay_Listing $eListing */
        $eListing = $listing->getChildObject();

        $params = array(
            'template_category_id'                 => $eListing->getAutoWebsiteAddingTemplateCategoryId(),
            'template_category_secondary_id'       => $eListing->getAutoWebsiteAddingTemplateCategorySecondaryId(),
            'template_store_category_id'           => $eListing->getAutoWebsiteAddingTemplateStoreCategoryId(),
            'template_store_category_secondary_id' => $eListing->getAutoWebsiteAddingTemplateStoreCategorySecondaryId(),
        );

        $this->processAddedListingProduct($listingProduct, $params);
    }

    //########################################

    protected function processAddedListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params)
    {
        $keys = array(
            'template_category_id',
            'template_category_secondary_id',
            'template_store_category_id',
            'template_store_category_secondary_id'
        );

        foreach ($keys as $key) {
            !empty($params[$key]) && $listingProduct->setData($key, $params[$key]);
        }

        $listingProduct->save();
    }

    //########################################
}