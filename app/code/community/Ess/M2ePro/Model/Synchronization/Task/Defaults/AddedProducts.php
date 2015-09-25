<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Synchronization_Task_Defaults_AddedProducts
    extends Ess_M2ePro_Model_Synchronization_Task_Defaults_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/added_products/';
    }

    protected function getTitle()
    {
        return 'Auto Add Rules';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 80;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    //####################################

    protected function performActions()
    {
        if (is_null($this->getLastProcessedProductId())) {
            $this->setLastProcessedProductId($this->getLastProductId());
        }

        if (count($products = $this->getProducts()) <= 0) {
            return;
        }

        $tempIndex = 0;
        $totalItems = count($products);

        foreach ($products as $product) {

            $this->processCategoriesActions($product);
            $this->processGlobalActions($product);
            $this->processWebsiteActions($product);

            if ((++$tempIndex)%20 == 0) {
                $percentsPerOneItem = $this->getPercentsInterval()/$totalItems;
                $this->getActualLockItem()->setPercents($percentsPerOneItem*$tempIndex);
                $this->getActualLockItem()->activate();
            }
        }

        $lastMagentoProduct = array_pop($products);
        $this->setLastProcessedProductId((int)$lastMagentoProduct->getId());
    }

    //####################################

    private function processCategoriesActions(Mage_Catalog_Model_Product $product)
    {
        $productCategories = $product->getCategoryIds();

        $categoriesByWebsite = array(
            0 => $productCategories // website for admin values
        );

        foreach ($product->getWebsiteIds() as $websiteId) {
            $categoriesByWebsite[$websiteId] = $productCategories;
        }

        /** @var Ess_M2ePro_Model_Listing_Auto_Actions_Mode_Category $autoActionsCategory */
        $autoActionsCategory = Mage::getModel('M2ePro/Listing_Auto_Actions_Mode_Category');
        $autoActionsCategory->setProduct($product);

        foreach ($categoriesByWebsite as $websiteId => $categoryIds) {
            foreach ($categoryIds as $categoryId) {
                $autoActionsCategory->synchWithAddedCategoryId($categoryId, $websiteId);
            }
        }
    }

    private function processGlobalActions(Mage_Catalog_Model_Product $product)
    {
        /** @var Ess_M2ePro_Model_Listing_Auto_Actions_Mode_Global $object */
        $object = Mage::getModel('M2ePro/Listing_Auto_Actions_Mode_Global');
        $object->setProduct($product);
        $object->synch();
    }

    private function processWebsiteActions(Mage_Catalog_Model_Product $product)
    {
        /** @var Ess_M2ePro_Model_Listing_Auto_Actions_Mode_Website $object */
        $object = Mage::getModel('M2ePro/Listing_Auto_Actions_Mode_Website');
        $object->setProduct($product);

        // website for admin values
        $websiteIds = $product->getWebsiteIds();
        $websiteIds[] = 0;

        foreach ($websiteIds as $websiteId) {
            $object->synchWithAddedWebsiteId($websiteId);
        }
    }

    //####################################

    private function getLastProductId()
    {
        /** @var Mage_Core_Model_Mysql4_Collection_Abstract $collection */
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->getSelect()->order('entity_id DESC')->limit(1);
        return (int)$collection->getLastItem()->getId();
    }

    private function getProducts()
    {
        /** @var Mage_Core_Model_Mysql4_Collection_Abstract $collection */
        $collection = Mage::getModel('catalog/product')->getCollection();

        $collection->addFieldToFilter('entity_id', array('gt' => (int)$this->getLastProcessedProductId()));
        $collection->setOrder('entity_id','asc');
        $collection->getSelect()->limit(100);

        return $collection->getItems();
    }

    // ------------------------------------

    private function getLastProcessedProductId()
    {
        return $this->getConfigValue($this->getFullSettingsPath(),'last_magento_product_id');
    }

    private function setLastProcessedProductId($magentoProductId)
    {
        $this->setConfigValue($this->getFullSettingsPath(),'last_magento_product_id',(int)$magentoProductId);
    }

    //####################################
}