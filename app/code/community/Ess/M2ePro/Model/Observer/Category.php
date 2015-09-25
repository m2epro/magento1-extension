<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Category extends Ess_M2ePro_Model_Observer_Abstract
{
    //####################################

    public function process()
    {
        /** @var Mage_Catalog_Model_Category $category */
        $category = $this->getEventObserver()->getData('category');

        $categoryId = (int)$category->getId();
        $websiteId = (int)$category->getStore()->getWebsiteId();

        $changedProductsIds = $this->getEventObserver()->getData('product_ids');
        $postedProductsIds = array_keys($this->getEventObserver()->getData('category')->getData('posted_products'));

        if (!is_array($changedProductsIds) || count($changedProductsIds) <= 0) {
            return;
        }

        $websitesProductsIds = array(
            // website for default store view
            0 => $changedProductsIds
        );

        if ($websiteId == 0) {

            foreach ($changedProductsIds as $productId) {
                $productModel = Mage::getModel('M2ePro/Magento_Product')->setProductId($productId);
                foreach ($productModel->getWebsiteIds() as $websiteId) {
                    $websitesProductsIds[$websiteId][] = $productId;
                }
            }

        } else {
            $websitesProductsIds[$websiteId] = $changedProductsIds;
        }

        foreach ($websitesProductsIds as $websiteId => $productIds) {
            foreach ($productIds as $productId) {

                /** @var Mage_Catalog_Model_Product $product */
                $product = Mage::helper('M2ePro/Magento_Product')->getCachedAndLoadedProduct($productId);

                /** @var Ess_M2ePro_Model_Listing_Auto_Actions_Mode_Category $object */
                $object = Mage::getModel('M2ePro/Listing_Auto_Actions_Mode_Category');
                $object->setProduct($product);

                if (in_array($productId,$postedProductsIds)) {
                    $object->synchWithAddedCategoryId($categoryId,$websiteId);
                } else {
                    $object->synchWithDeletedCategoryId($categoryId,$websiteId);
                }
            }
        }
    }

    //####################################
}