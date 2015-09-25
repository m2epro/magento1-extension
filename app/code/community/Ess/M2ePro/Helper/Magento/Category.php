<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Magento_Category extends Ess_M2ePro_Helper_Magento_Abstract
{
    // ################################

    public function getCategoriesByProduct($product, $storeId = 0, $returnType = self::RETURN_TYPE_IDS)
    {
        $productId = $this->_getIdFromInput($product);
        if ($productId === false) {
            return array();
        }

        return $this->getAllCategoriesByProducts(array($productId), $storeId, $returnType);
    }

    public function getAllCategoriesByProducts(array $products, $storeId = 0, $returnType = self::RETURN_TYPE_IDS)
    {
        $productIds = $this->_getIdsFromInput($products, 'product_id');
        if (empty($productIds)) {
            return array();
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $categoryProductTableName = Mage::getSingleton('core/resource')->getTableName('catalog/category_product');

        $dbSelect = $connRead->select()
            ->from(array('ccp' => $categoryProductTableName), 'category_id')
            ->where('ccp.product_id IN ('.implode(',', $productIds).')')
            ->group('ccp.category_id');

        if ($storeId > 0) {
            /** @var $storeModel Mage_Core_Model_Store */
            $storeModel = Mage::getModel('core/store')->load($storeId);
            if (!is_null($storeModel)) {
                $websiteId = $storeModel->getWebsiteId();
                $productWebsiteTableName = Mage::getSingleton('core/resource')->getTableName('catalog/product_website');
                $dbSelect->joinLeft(array('cpw' => $productWebsiteTableName), 'ccp.product_id = cpw.product_id')
                         ->where('cpw.website_id = ?', (int)$websiteId);
            }
        }

        $oldFetchMode = $connRead->getFetchMode();
        $connRead->setFetchMode(Zend_Db::FETCH_NUM);
        $fetchArray = $connRead->fetchAll($dbSelect);
        $connRead->setFetchMode($oldFetchMode);

        return $this->_convertFetchNumArrayToReturnType($fetchArray, $returnType, 'catalog/category');
    }

    // -------------------------------

    public function getGeneralProductsFromCategories(array $categories,
                                                     $storeId = 0,
                                                     $returnType = self::RETURN_TYPE_IDS)
    {
        $categoryIds = $this->_getIdsFromInput($categories, 'category_id');
        if (empty($categoryIds)) {
            return array();
        }

        return $this->_getProductsFromCategoryIds($categoryIds, $storeId, $returnType, true);
    }

    public function getProductsFromCategories(array $categories, $storeId = 0, $returnType = self::RETURN_TYPE_IDS)
    {
        $categoryIds = $this->_getIdsFromInput($categories, 'category_id');
        if (empty($categoryIds)) {
            return array();
        }

        return $this->_getProductsFromCategoryIds($categoryIds, $storeId, $returnType);
    }

    // -------------------------------

    public function getUncategorizedProducts($storeId = 0, $returnType = self::RETURN_TYPE_IDS)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $productTableName = Mage::getSingleton('core/resource')->getTableName('catalog/product');
        $categoryProductTableName = Mage::getSingleton('core/resource')->getTableName('catalog/category_product');

        $dbSelect = $connRead->select()
            ->from(array('cp' => $productTableName), 'entity_id')
            ->joinLeft(array('ccp' => $categoryProductTableName), 'cp.entity_id = ccp.product_id')
            ->where('ccp.category_id IS NULL');

        if ($storeId > 0) {
            /** @var $storeModel Mage_Core_Model_Store */
            $storeModel = Mage::getModel('core/store')->load($storeId);
            if (!is_null($storeModel)) {
                $websiteId = $storeModel->getWebsiteId();
                $productWebsiteTableName = Mage::getSingleton('core/resource')->getTableName('catalog/product_website');
                $dbSelect->joinLeft(array('cpw' => $productWebsiteTableName), 'cp.entity_id = cpw.product_id')
                         ->where('cpw.website_id = ?', (int)$websiteId);
            }
        }

        $oldFetchMode = $connRead->getFetchMode();
        $connRead->setFetchMode(Zend_Db::FETCH_NUM);
        $fetchArray = $connRead->fetchAll($dbSelect);
        $connRead->setFetchMode($oldFetchMode);

        return $this->_convertFetchNumArrayToReturnType($fetchArray, $returnType, 'catalog/product');
    }

    public function isProductUncategorized($product, $storeId = 0)
    {
        $productId = $this->_getIdFromInput($product);
        if ($productId === false) {
            return array();
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $categoryProductTableName = Mage::getSingleton('core/resource')->getTableName('catalog/category_product');

        $dbSelect = $connRead->select()
            ->from(array('ccp' => $categoryProductTableName), 'product_id')
            ->where('ccp.product_id = ?', $productId);

        if ($storeId > 0) {
            /** @var $storeModel Mage_Core_Model_Store */
            $storeModel = Mage::getModel('core/store')->load($storeId);
            if (!is_null($storeModel)) {
                $websiteId = $storeModel->getWebsiteId();
                $productWebsiteTableName = Mage::getSingleton('core/resource')->getTableName('catalog/product_website');
                $dbSelect->joinLeft(array('cpw' => $productWebsiteTableName), 'ccp.product_id = cpw.product_id')
                         ->where('cpw.website_id = ?', (int)$websiteId);
            }
        }

        if ($connRead->fetchOne($dbSelect) === false) {
            return true;
        }

        return false;
    }

    public function getLimitedCategoriesByProducts($productIds, $storeId = 0)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName = Mage::getSingleton('core/resource')->getTableName('catalog/category_product');

        $dbSelect = $connRead->select()
            ->from(array('ccp' => $tableName))
            ->where('ccp.product_id IN ('.implode(',', $productIds).')');

        if ($storeId > 0) {
            /** @var $storeModel Mage_Core_Model_Store */
            $storeModel = Mage::getModel('core/store')->load($storeId);
            if (!is_null($storeModel)) {
                $websiteId = $storeModel->getWebsiteId();
                $productWebsiteTableName = Mage::getSingleton('core/resource')->getTableName('catalog/product_website');
                $dbSelect->joinLeft(array('cpw' => $productWebsiteTableName), 'ccp.product_id = cpw.product_id')
                    ->where('cpw.website_id = ?', (int)$websiteId);
            }
        }

        $fetchResult = $connRead->fetchAll($dbSelect);

        $categories = array();
        $productsCount = array();
        foreach ($fetchResult as $row) {
            if (!isset($categories[$row['category_id']])) {
                $productsCount[$row['category_id']] = 1;
                $categories[$row['category_id']] = array($row['product_id'] => false);
                continue;
            }

            $productsCount[$row['category_id']]++;
            $categories[$row['category_id']][$row['product_id']] = false;
        }

        arsort($productsCount);

        $resultCategories = array();
        foreach ($productIds as $productId) {

            foreach ($productsCount as $categoryId => $count) {
                if (!isset($categories[$categoryId][$productId])) {
                    continue;
                }

                $resultCategories[] = $categoryId;
                break;
            }

        }

        return array_values(array_unique($resultCategories));
    }

    // ################################

    protected function _getProductsFromCategoryIds(array $categoryIds, $storeId, $returnType, $onlyGeneral = false)
    {
        if (empty($categoryIds)) {
            return array();
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName = Mage::getSingleton('core/resource')->getTableName('catalog/category_product');

        $dbSelect = $connRead->select()
            ->from(array('ccp' => $tableName), 'product_id')
            ->where('ccp.category_id IN ('.implode(',', $categoryIds).')')
            ->group('ccp.product_id');

        if ($onlyGeneral) {
            $dbSelect->having('count(*) = ?', count($categoryIds));
        }

        if ($storeId > 0) {
            /** @var $storeModel Mage_Core_Model_Store */
            $storeModel = Mage::getModel('core/store')->load($storeId);
            if (!is_null($storeModel)) {
                $websiteId = $storeModel->getWebsiteId();
                $productWebsiteTableName = Mage::getSingleton('core/resource')->getTableName('catalog/product_website');
                $dbSelect->joinLeft(array('cpw' => $productWebsiteTableName), 'ccp.product_id = cpw.product_id')
                    ->where('cpw.website_id = ?', (int)$websiteId);
            }
        }

        $oldFetchMode = $connRead->getFetchMode();
        $connRead->setFetchMode(Zend_Db::FETCH_NUM);
        $fetchArray = $connRead->fetchAll($dbSelect);
        $connRead->setFetchMode($oldFetchMode);

        return $this->_convertFetchNumArrayToReturnType($fetchArray, $returnType, 'catalog/product');
    }

    // ################################

    public function getPath($categoryId)
    {
        $category = Mage::getModel('catalog/category');
        $category->load($categoryId);

        if (!$category->getId()) {
            return array();
        }

        $categoryPath = array();

        $pathIds = array_reverse(explode(',', $category->getPathInStore()));
        $categories = Mage::getResourceModel('catalog/category_collection')
            ->setStore(Mage::app()->getStore())
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('url_key')
            ->addFieldToFilter('entity_id', array('in' => $pathIds))
            ->load()
            ->getItems();

        foreach ($pathIds as $categoryId) {
            if (!isset($categories[$categoryId]) || !$categories[$categoryId]->getName()) {
                continue;
            }

            $categoryPath[] = $categories[$categoryId]->getName();
        }

        return $categoryPath;
    }

    // ################################
}