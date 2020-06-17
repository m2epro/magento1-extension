<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Magento_Product_DetectDirectlyAdded extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'magento/product/detect_directly_added';

    //########################################

    protected function performActions()
    {
        if ($this->getLastProcessedProductId() === null) {
            $this->setLastProcessedProductId($this->getLastProductId());
        }

        $products = $this->getProducts();
        if (empty($products)) {
            return;
        }

        foreach ($products as $product) {
            $this->processCategoriesActions($product);
            $this->processGlobalActions($product);
            $this->processWebsiteActions($product);
        }

        $lastMagentoProduct = array_pop($products);
        $this->setLastProcessedProductId((int)$lastMagentoProduct->getId());
    }

    //########################################

    protected function processCategoriesActions(Mage_Catalog_Model_Product $product)
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

    protected function processGlobalActions(Mage_Catalog_Model_Product $product)
    {
        /** @var Ess_M2ePro_Model_Listing_Auto_Actions_Mode_Global $object */
        $object = Mage::getModel('M2ePro/Listing_Auto_Actions_Mode_Global');
        $object->setProduct($product);
        $object->synch();
    }

    protected function processWebsiteActions(Mage_Catalog_Model_Product $product)
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

    //########################################

    protected function getLastProductId()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
        $collection = Mage::getConfig()->getModelInstance(
            'Ess_M2ePro_Model_Resource_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource()
        );

        $collection->getSelect()->order('entity_id DESC')->limit(1);
        return (int)$collection->getLastItem()->getId();
    }

    protected function getProducts()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
        $collection = Mage::getConfig()->getModelInstance(
            'Ess_M2ePro_Model_Resource_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource()
        );

        $collection->addFieldToFilter('entity_id', array('gt' => (int)$this->getLastProcessedProductId()));
        $collection->addAttributeToSelect('visibility');
        $collection->setOrder('entity_id', 'asc');
        $collection->getSelect()->limit(100);

        return $collection->getItems();
    }

    // ---------------------------------------

    protected function getLastProcessedProductId()
    {
        return Mage::helper('M2ePro/Module')->getRegistryValue(
            '/magento/product/detect_directly_added/last_magento_product_id/'
        );
    }

    protected function setLastProcessedProductId($magentoProductId)
    {
        Mage::helper('M2ePro/Module')->setRegistryValue(
            '/magento/product/detect_directly_added/last_magento_product_id/',
            (int)$magentoProductId
        );
    }

    //########################################
}
