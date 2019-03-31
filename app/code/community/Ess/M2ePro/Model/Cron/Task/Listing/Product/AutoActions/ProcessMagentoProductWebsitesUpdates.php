<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Listing_Product_AutoActions_ProcessMagentoProductWebsitesUpdates
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'listing/product/auto_actions/process_magento_product_websites_updates';

    const PRODUCTS_COUNT = 1000;

    //########################################

    public function performActions()
    {
        $updatingProductsIds = $this->getUpdatingProductsIds();

        $updatedProductsData = $this->getUpdatedProductsData($updatingProductsIds);

        $this->removeProcessingWebsiteUpdatesForProducts($updatingProductsIds);

        foreach ($updatedProductsData as $productId => $updateProductData) {
            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::getModel('catalog/product')->load($productId);

            /** @var Ess_M2ePro_Model_Listing_Auto_Actions_Mode_Website $autoActions */
            $autoActions = Mage::getModel('M2ePro/Listing_Auto_Actions_Mode_Website');
            $autoActions->setProduct($product);

            foreach ($updateProductData[Ess_M2ePro_Model_Magento_Product_Websites_Update::ACTION_ADD] as $websiteId) {
                if (in_array($websiteId, $product->getWebsiteIds())) {
                    $autoActions->synchWithAddedWebsiteId($websiteId);
                }
            }

            foreach ($updateProductData[Ess_M2ePro_Model_Magento_Product_Websites_Update::ACTION_REMOVE] as $websiteId){
                if (!in_array($websiteId, $product->getWebsiteIds())) {
                    $autoActions->synchWithDeletedWebsiteId($websiteId);
                }
            }
        }
    }

    //########################################

    protected function getUpdatingProductsIds()
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_magento_product_websites_update');

        $limit = self::PRODUCTS_COUNT;

        $tempQuery = <<<SQL
SELECT DISTINCT
  `product_id`
FROM (SELECT
    `main_table`.`product_id`
  FROM `{$table}` AS `main_table`
  ORDER BY `main_table`.`id` ASC) AS `t`
LIMIT {$limit};
SQL;

        return $connRead->query($tempQuery)->fetchAll(PDO::FETCH_COLUMN);
    }

    protected function removeProcessingWebsiteUpdatesForProducts($productsIds)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $coreResourceModel = Mage::getSingleton('core/resource');

        $tableWebsiteUpdate = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_magento_product_websites_update');

        $connWrite->delete($tableWebsiteUpdate, array('product_id IN (?)' => $productsIds));
    }

    private function getUpdatedProductsData($updatingProductsIds)
    {
        /** @var Ess_M2ePro_Model_Mysql4_Magento_Product_Websites_Update_Collection $websiteUpdates */
        $websiteUpdates = Mage::getModel('M2ePro/Magento_Product_Websites_Update')->getCollection();
        $websiteUpdates->getSelect()->where('product_id IN (?)', $updatingProductsIds);

        $actionAdd = Ess_M2ePro_Model_Magento_Product_Websites_Update::ACTION_ADD;
        $actionRemove = Ess_M2ePro_Model_Magento_Product_Websites_Update::ACTION_REMOVE;

        $addedWebsiteIds = array();
        $deletedWebsiteIds = array();
        $updatedProductsData = array();

        foreach ($websiteUpdates->getItems() as $websiteUpdate) {
            /** @var Ess_M2ePro_Model_Magento_Product_Websites_Update $websiteUpdate */

            if (empty($updatedProductsData[$websiteUpdate->getProductId()])) {

                $updatedProductsData[$websiteUpdate->getProductId()] = array(
                    $actionAdd => array(),
                    $actionRemove => array()
                );
            }

            $updatedProductData = &$updatedProductsData[$websiteUpdate->getProductId()];

            if ($websiteUpdate->getAction() == $actionAdd) {

                $updatedProductData[$actionAdd][] = $websiteUpdate->getWebsiteId();
                $addedWebsiteIds[] = $websiteUpdate->getWebsiteId();

            } else {

                $updatedProductData[$actionRemove][] = $websiteUpdate->getWebsiteId();
                $deletedWebsiteIds[] = $websiteUpdate->getWebsiteId();
            }
        }

        $addedWebsiteIds = array_unique($addedWebsiteIds);
        $deletedWebsiteIds = array_unique($deletedWebsiteIds);

        $addedWebsitesWithListings = $this->getWebsitesWithListingByAction($addedWebsiteIds, $actionAdd);
        $deletedWebsitesWithListings = $this->getWebsitesWithListingByAction($deletedWebsiteIds, $actionRemove);

        foreach ($updatedProductsData as $productId => &$updatedProductData) {
            $addedWebsiteIds = &$updatedProductData[$actionAdd];
            $deletedWebsiteIds = &$updatedProductData[$actionRemove];

            $addedWebsiteIds = array_intersect($addedWebsiteIds, $addedWebsitesWithListings);
            $deletedWebsiteIds = array_intersect($deletedWebsiteIds, $deletedWebsitesWithListings);

            if (empty($addedWebsiteIds) && empty($deletedWebsiteIds)) {
                unset($updatedProductsData[$productId]);
            }
        }

        return $updatedProductsData;
    }

    //########################################

    protected function getWebsitesWithListingByAction($websiteIds, $action)
    {
        $websitesWithListings = array();

        if (empty($websiteIds)) {
            return $websitesWithListings;
        }

        $websitesCollection = Mage::getModel('core/website')->getCollection()
            ->addFieldToFilter('website_id', array('in' => $websiteIds));

        $websitesCollection->getSelect()->joinLeft(
            array('cs' => Mage::getResourceModel('core/store')->getMainTable()),
            '(`cs`.`website_id` = `main_table`.`website_id`)'
        );

        if ($action == Ess_M2ePro_Model_Magento_Product_Websites_Update::ACTION_ADD) {
            $websitesCollection->getSelect()->joinLeft(
                array('ml' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
                '(`ml`.`store_id` = `cs`.`store_id` AND `ml`.`auto_website_adding_mode` != ' .
                    Ess_M2ePro_Model_Listing::ADDING_MODE_NONE . ')',
                array(
                    'listing_id' => 'id'
                )
            );
        } else if ($action == Ess_M2ePro_Model_Magento_Product_Websites_Update::ACTION_REMOVE) {
            $websitesCollection->getSelect()->joinLeft(
                array('ml' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
                '(`ml`.`store_id` = `cs`.`store_id` AND `ml`.`auto_website_deleting_mode` != ' .
                    Ess_M2ePro_Model_Listing::DELETING_MODE_NONE . ')',
                array(
                    'listing_id' => 'id'
                )
            );
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $websites = $connRead->query($websitesCollection->getSelect())->fetchAll();

        foreach ($websites as $website) {
            if (!is_null($website['listing_id'])) {
                $websitesWithListings[] = $website['website_id'];
            }
        }

        return array_unique($websitesWithListings);
    }

    //########################################
}