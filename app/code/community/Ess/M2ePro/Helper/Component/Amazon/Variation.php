<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Amazon_Variation extends Mage_Core_Helper_Abstract
{
    const DATA_REGISTRY_KEY  = 'amazon_variation_themes_usage';

    //########################################

    public function filterProductsNotMatchingForNewAsin($productsIds)
    {
        $productsIds = $this->filterProductsByGeneralId($productsIds);
        $productsIds = $this->filterProductsByGeneralIdOwner($productsIds);
        $productsIds = $this->filterProductsByStatus($productsIds);
        $productsIds = $this->filterLockedProducts($productsIds);
        $productsIds = $this->filterProductsByMagentoProductType($productsIds);

        return $productsIds;
    }

    //########################################

    public function filterProductsByGeneralId($productsIds)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')
            ->getTableName('m2epro_amazon_listing_product');

        $select = $connRead->select();
        $select->from(array('alp' => $table), array('listing_product_id'))
            ->where('listing_product_id IN (?)', $productsIds)
            ->where('general_id IS NULL');

        return Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($select);
    }

    public function filterProductsByGeneralIdOwner($productsIds)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')
            ->getTableName('m2epro_amazon_listing_product');

        $select = $connRead->select();
        $select->from(array('alp' => $table), array('listing_product_id'))
            ->where('listing_product_id IN (?)', $productsIds)
            ->where('is_general_id_owner = 0');

        return Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($select);
    }

    public function filterProductsByStatus($productsIds)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')
            ->getTableName('m2epro_listing_product');

        $select = $connRead->select();
        $select->from(array('lp' => $table), array('id'))
            ->where('id IN (?)', $productsIds)
            ->where('status = ?', Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED);

        return Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($select);
    }

    public function filterLockedProducts($productsIds)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('m2epro_locked_object');

        $select = $connRead->select();
        $select->from(array('lo' => $table), array('object_id'))
            ->where('model_name = "M2ePro/Listing_Product"')
            ->where('object_id IN (?)', $productsIds)
            ->where('tag IS NULL');

        $lockedProducts = Mage::getResourceModel('core/config')->getReadConnection()->fetchCol($select);

        foreach ($lockedProducts as $id) {
            $key = array_search($id, $productsIds);
            if ($key !== false) {
                unset($productsIds[$key]);
            }
        }

        return $productsIds;
    }

    public function filterProductsByMagentoProductType($listingProductsIds)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableListingProduct = Mage::getSingleton('core/resource')->getTableName('m2epro_listing_product');
        $tableProductEntity = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity');
        $tableProductOption = Mage::getSingleton('core/resource')->getTableName('catalog_product_option');

        $productsIdsChunks = array_chunk($listingProductsIds, 1000);
        $listingProductsIds = array();

        foreach ($productsIdsChunks as $productsIdsChunk) {

            $select = $connRead->select();
            $select->from(array('alp' => $tableListingProduct), array('id', 'product_id'))
                ->where('id IN (?)', $productsIdsChunk);

            $listingProductToProductIds = Mage::getResourceModel('core/config')->getReadConnection()
                                                                               ->fetchPairs($select);

            $select = $connRead->select();
            $select->from(array('cpe' => $tableProductEntity), array('entity_id', 'type_id'))
                ->where('entity_id IN (?)', $listingProductToProductIds);

            $select->joinLeft(
                array('cpo' => $tableProductOption),
                'cpe.entity_id=cpo.product_id',
                array('option_id')
            );

            $select->group('entity_id');

            $productsData = Mage::getResourceModel('core/config')->getReadConnection()->fetchAll($select);

            $productToListingProductIds = array_flip($listingProductToProductIds);

            foreach ($productsData as $product) {
                if ($product['type_id'] == Ess_M2ePro_Model_Magento_Product::TYPE_BUNDLE) {
                    unset($productToListingProductIds[$product['entity_id']]);
                }

                if ($product['type_id'] == Ess_M2ePro_Model_Magento_Product::TYPE_SIMPLE &&
                    !empty($product['option_id'])) {
                    unset($productToListingProductIds[$product['entity_id']]);
                }
            }

            $productsIdsFiltered = array_flip($productToListingProductIds);

            foreach ($listingProductToProductIds as $listingProductId => $productId) {
                if (!in_array($productId, $productsIdsFiltered)) {
                    unset($listingProductToProductIds[$listingProductId]);
                }
            }

            $listingProductsIds = array_merge(
                $listingProductsIds,
                array_keys($listingProductToProductIds)
            );
        }

        return $listingProductsIds;
    }

    //########################################

    public function filterProductsByDescriptionTemplate($productsIds)
    {
        $productsIdsChunks = array_chunk($productsIds, 1000);
        $productsIds = array();

        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableAmazonListingProduct = Mage::getSingleton('core/resource')
            ->getTableName('m2epro_amazon_listing_product');
        $tableAmazonTemplateDescription = Mage::getSingleton('core/resource')
            ->getTableName('m2epro_amazon_template_description');

        foreach ($productsIdsChunks as $productsIdsChunk) {

            $select = $connRead->select();
            $select->from(array('alp' => $tableAmazonListingProduct), array('listing_product_id'))
                ->where('listing_product_id IN (?)', $productsIdsChunk);

            $select->join(
                array('atd' => $tableAmazonTemplateDescription),
                'alp.template_description_id=atd.template_description_id',
                array()
            )->where('atd.is_new_asin_accepted = 1');

            $productsIds = array_merge(
                $productsIds,
                Mage::getResourceModel('core/config')->getReadConnection()->fetchCol($select)
            );
        }

        return $productsIds;
    }

    public function filterParentProductsByVariationTheme($productsIds)
    {
        $detailsModel = Mage::getModel('M2ePro/Amazon_Marketplace_Details');

        foreach ($productsIds as $key => $productId) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            if (!$amazonListingProduct->getVariationManager()->isRelationParentType()) {
                continue;
            }

            $detailsModel->setMarketplaceId($listingProduct->getListing()->getMarketplaceId());

            $themes = $detailsModel->getVariationThemes(
                $amazonListingProduct->getAmazonDescriptionTemplate()->getProductDataNick()
            );

            if (empty($themes)) {
                unset($productsIds[$key]);
            }
        }

        return $productsIds;
    }

    //########################################

    public function increaseThemeUsageCount($theme, $marketplaceId)
    {
        /** @var Ess_M2ePro_Model_Registry $registry */
        $registry = Mage::getModel('M2ePro/Registry')->load(self::DATA_REGISTRY_KEY, 'key');

        $data = $registry->getSettings('value');

        if (empty($data[$marketplaceId][$theme])) {
            $data[$marketplaceId][$theme] = 0;
        }
        $data[$marketplaceId][$theme]++;

        arsort($data[$marketplaceId]);

        $registry->setData('key', self::DATA_REGISTRY_KEY);
        $registry->setSettings('value', $data)->save();

        $this->removeThemeUsageDataCache();
    }

    // ---------------------------------------

    public function getThemesUsageData()
    {
        $cacheData = $this->getThemeUsageDataCache();
        if (is_array($cacheData)) {
            return $cacheData;
        }

        /** @var Ess_M2ePro_Model_Registry $registry */
        $registry = Mage::getModel('M2ePro/Registry')->load(self::DATA_REGISTRY_KEY, 'key');
        $data = $registry->getSettings('value');

        $this->setThemeUsageDataCache($data);

        return $data;
    }

    //########################################

    private function getThemeUsageDataCache()
    {
        $cacheKey = __CLASS__.self::DATA_REGISTRY_KEY;
        return Mage::helper('M2ePro/Data_Cache_Permanent')->getValue($cacheKey);
    }

    // ---------------------------------------

    private function setThemeUsageDataCache(array $data)
    {
        $cacheKey = __CLASS__.self::DATA_REGISTRY_KEY;
        Mage::helper('M2ePro/Data_Cache_Permanent')->setValue($cacheKey, $data);
    }

    // ---------------------------------------

    private function removeThemeUsageDataCache()
    {
        $cacheKey = __CLASS__.self::DATA_REGISTRY_KEY;
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeValue($cacheKey);
    }

    //########################################
}
