<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
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
        $table = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_amazon_listing_product');

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
        $table = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_amazon_listing_product');

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
        $table = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_listing_product');

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
        $table = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_processing_lock');

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

    /**
     * @param bool $skipSimpleWithCustomOptions Whether to skip simple products with custom options
     *
     * @return string|int[]
     */
    public function filterProductsByMagentoProductType(
        $listingProductsIds,
        $skipSimpleWithCustomOptions = false
    ) {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableListingProduct = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_listing_product');
        $tableProductEntity = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('catalog_product_entity');
        $tableProductOption = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('catalog_product_option');
        $tableProductEntityInt = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('catalog_product_entity_int');

        $linksPurchasedSeparatelyAttribute = Mage::getSingleton('eav/config')->getAttribute(
            Mage_Catalog_Model_Product::ENTITY, 'links_purchased_separately'
        );

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
                ->where('cpe.entity_id IN (?)', $listingProductToProductIds);

            $select->joinLeft(
                array('cpo' => $tableProductOption),
                'cpe.entity_id=cpo.product_id',
                array(
                    'option_id'         => 'option_id',
                    'option_is_require' => 'is_require',
                    'option_type'       => 'type'
                )
            );

            $linkAttributeId = $linksPurchasedSeparatelyAttribute->getAttributeId();
            $select->joinLeft(
                array('cpei' => $tableProductEntityInt),
                'cpe.entity_id=cpei.entity_id AND cpei.attribute_id = '.$linkAttributeId,
                array(
                    'is_links_purchased_separately' => 'value',
                )
            );

            $select->group('cpe.entity_id');

            $productsData = Mage::getResourceModel('core/config')->getReadConnection()->fetchAll($select);

            $productToListingProductIds = array_flip($listingProductToProductIds);

            foreach ($productsData as $product) {
                if (Mage::helper('M2ePro/Magento_Product')->isBundleType($product['type_id'])) {
                    unset($productToListingProductIds[$product['entity_id']]);
                }

                if (Mage::helper('M2ePro/Magento_Product')->isDownloadableType($product['type_id']) &&
                    $product['is_links_purchased_separately']) {
                    unset($productToListingProductIds[$product['entity_id']]);
                }

                if (
                    !$skipSimpleWithCustomOptions
                    && Mage::helper('M2ePro/Magento_Product')->isSimpleType($product['type_id'])
                    && !empty($product['option_id']) && $product['option_is_require'] == 1
                    && in_array($product['option_type'], array('drop_down', 'radio', 'multiple', 'checkbox'))
                ) {
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

    public function filterProductsByAvailableWorldwideIdentifiers(array $listingProductsIds)
    {
        $worldwideIdSeemsLikeAvailable = array();
        $parentWithChildWithoutWorldwideId = array();

        $productsIdsChunks = array_chunk($listingProductsIds, 1000);
        foreach ($productsIdsChunks as $chunk) {
            $idsCondition = implode(',', $chunk);
            $listingProductResource = Mage::getResourceModel('M2ePro/Listing_Product');
            $listingProductResource->setChildMode(Ess_M2ePro_Helper_Component_Amazon::NICK);

            $collection = Mage::getResourceModel('M2ePro/Listing_Product_Collection', $listingProductResource);
            $collection->getSelect()
                ->where("id IN($idsCondition) OR variation_parent_id IN($idsCondition)");

            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            foreach ($collection->getItems() as $listingProduct) {
                $amazonListingProduct = $listingProduct->getChildObject();
                $variationManager = $amazonListingProduct->getVariationManager();
                $identifiers = $amazonListingProduct->getIdentifiers();

                if ($variationManager->isRelationParentType()) {
                    // variation parent does not require worldwide id,
                    // but variation children will be checked for worldwide id availability
                    $id = $listingProduct->getId();
                    $worldwideIdSeemsLikeAvailable[$id] = true;
                } elseif ($variationManager->isRelationChildType()) {
                    if ($identifiers->getWorldwideId() === null) {
                        $id = $variationManager->getVariationParentId();
                        $parentWithChildWithoutWorldwideId[$id] = true;
                    }
                } else {
                    if ($identifiers->getWorldwideId() !== null) {
                        $id = $listingProduct->getId();
                        $worldwideIdSeemsLikeAvailable[$id] = true;
                    }
                }
            }
        }

        return array_diff(
            array_keys($worldwideIdSeemsLikeAvailable),
            array_keys($parentWithChildWithoutWorldwideId)
        );
    }

    public function filterProductsByProductType($productsIds)
    {
        $productsIdsChunks = array_chunk($productsIds, 1000);
        $productsIds = array();

        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableAmazonListingProduct = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_amazon_listing_product');

        foreach ($productsIdsChunks as $productsIdsChunk) {
            $select = $connRead->select();
            $select->from(array('alp' => $tableAmazonListingProduct), array('listing_product_id'))
                ->where('listing_product_id IN (?)', $productsIdsChunk)
                ->where('template_product_type_id IS NOT NULL');

            $productsIds = array_merge(
                $productsIds,
                Mage::getResourceModel('core/config')->getReadConnection()->fetchCol($select)
            );
        }

        return $productsIds;
    }

    public function filterParentProductsByVariationTheme($productsIds)
    {
        foreach ($productsIds as $key => $productId) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            if (!$amazonListingProduct->getVariationManager()->isRelationParentType()) {
                continue;
            }

            $dictionaryProductTypeModel = $amazonListingProduct->getProductTypeTemplate()
                                                               ->getDictionary();

            $themes = $dictionaryProductTypeModel->getVariationThemes();

            if (empty($themes)) {
                unset($productsIds[$key]);
            }
        }

        return $productsIds;
    }

    //########################################

    public function increaseThemeUsageCount($theme, $marketplaceId)
    {
        $data = Mage::helper('M2ePro/Module')->getRegistry()->getValueFromJson(self::DATA_REGISTRY_KEY);

        if (empty($data[$marketplaceId][$theme])) {
            $data[$marketplaceId][$theme] = 0;
        }

        $data[$marketplaceId][$theme]++;

        arsort($data[$marketplaceId]);

        Mage::helper('M2ePro/Module')->getRegistry()->setValue(self::DATA_REGISTRY_KEY, $data);

        $this->removeThemeUsageDataCache();
    }

    // ---------------------------------------

    public function getThemesUsageData()
    {
        $cacheData = $this->getThemeUsageDataCache();
        if (is_array($cacheData)) {
            return $cacheData;
        }

        $data = Mage::helper('M2ePro/Module')->getRegistry()->getValueFromJson(self::DATA_REGISTRY_KEY);

        $this->setThemeUsageDataCache($data);

        return $data;
    }

    //########################################

    protected function getThemeUsageDataCache()
    {
        $cacheKey = __CLASS__.self::DATA_REGISTRY_KEY;
        return Mage::helper('M2ePro/Data_Cache_Permanent')->getValue($cacheKey);
    }

    // ---------------------------------------

    protected function setThemeUsageDataCache(array $data)
    {
        $cacheKey = __CLASS__.self::DATA_REGISTRY_KEY;
        Mage::helper('M2ePro/Data_Cache_Permanent')->setValue($cacheKey, $data);
    }

    // ---------------------------------------

    protected function removeThemeUsageDataCache()
    {
        $cacheKey = __CLASS__.self::DATA_REGISTRY_KEY;
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeValue($cacheKey);
    }

    //########################################
}
