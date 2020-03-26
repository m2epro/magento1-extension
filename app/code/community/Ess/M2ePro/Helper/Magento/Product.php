<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Magento_Product as ProductModel;

class Ess_M2ePro_Helper_Magento_Product extends Mage_Core_Helper_Abstract
{
    const TYPE_SIMPLE       = 'simple';
    const TYPE_DOWNLOADABLE = 'downloadable';
    const TYPE_CONFIGURABLE = 'configurable';
    const TYPE_BUNDLE       = 'bundle';
    const TYPE_GROUPED      = 'grouped';

    const SKU_MAX_LENGTH = 64;

    protected $_cacheLoadedProducts = array();

    //########################################

    public function isSimpleType($originType)
    {
        return in_array($originType, $this->getOriginKnownTypes(self::TYPE_SIMPLE));
    }

    public function isDownloadableType($originType)
    {
        return in_array($originType, $this->getOriginKnownTypes(self::TYPE_DOWNLOADABLE));
    }

    public function isConfigurableType($originType)
    {
        return in_array($originType, $this->getOriginKnownTypes(self::TYPE_CONFIGURABLE));
    }

    public function isBundleType($originType)
    {
        return in_array($originType, $this->getOriginKnownTypes(self::TYPE_BUNDLE));
    }

    public function isGroupedType($originType)
    {
        return in_array($originType, $this->getOriginKnownTypes(self::TYPE_GROUPED));
    }

    // ---------------------------------------

    public function getOriginKnownTypes($byLogicType = null)
    {
        if ($byLogicType && !in_array($byLogicType, $this->getLogicTypes())) {
            throw new Ess_M2ePro_Model_Exception('Unknown logic type.');
        }

        $cache = Mage::helper('M2ePro/Data_Cache_Runtime');

        if (!$byLogicType) {
            if ($cache->getValue(__METHOD__)) {
                return $cache->getValue(__METHOD__);
            }

            $originTypes = $this->getOriginTypes();
            foreach ($this->getLogicTypes() as $logicType) {
                $originTypes = array_merge($originTypes, $this->getOriginCustomTypes($logicType));
            }

            $originTypes = array_unique($originTypes);
            $cache->setValue(__METHOD__, $originTypes);

            return $originTypes;
        }

        if ($cache->getValue(__METHOD__ . $byLogicType)) {
            return $cache->getValue(__METHOD__ . $byLogicType);
        }

        $associatedTypes = array(
            self::TYPE_SIMPLE       => array(ProductModel::TYPE_SIMPLE_ORIGIN),
            self::TYPE_DOWNLOADABLE => array(ProductModel::TYPE_DOWNLOADABLE_ORIGIN),
            self::TYPE_CONFIGURABLE => array(ProductModel::TYPE_CONFIGURABLE_ORIGIN),
            self::TYPE_BUNDLE       => array(ProductModel::TYPE_BUNDLE_ORIGIN),
            self::TYPE_GROUPED      => array(ProductModel::TYPE_GROUPED_ORIGIN)
        );

        $originTypes = array_unique(
            array_merge(
                $associatedTypes[$byLogicType],
                $this->getOriginCustomTypes($byLogicType)
            )
        );

        $cache->setValue(__METHOD__ . $byLogicType, $originTypes);

        return $originTypes;
    }

    // ---------------------------------------

    public function getOriginCustomTypes($byLogicType)
    {
        if (!in_array($byLogicType, $this->getLogicTypes())) {
            throw new Ess_M2ePro_Model_Exception('Unknown logic type.');
        }

        $customTypes = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            "/magento/product/{$byLogicType}_type/",
            "custom_types"
        );

        if (empty($customTypes)) {
            return array();
        }

        $customTypes = explode(',', $customTypes);

        return !empty($customTypes) ? array_map('trim', $customTypes) : array();
    }

    // ---------------------------------------

    public function getLogicTypes()
    {
        return array(
            self::TYPE_SIMPLE,
            self::TYPE_DOWNLOADABLE,
            self::TYPE_CONFIGURABLE,
            self::TYPE_BUNDLE,
            self::TYPE_GROUPED
        );
    }

    public function getOriginTypes()
    {
        return array(
            ProductModel::TYPE_SIMPLE_ORIGIN,
            ProductModel::TYPE_CONFIGURABLE_ORIGIN,
            ProductModel::TYPE_BUNDLE_ORIGIN,
            ProductModel::TYPE_GROUPED_ORIGIN,
            ProductModel::TYPE_DOWNLOADABLE_ORIGIN
        );
    }

    //########################################

    public function getCachedAndLoadedProduct($product, $storeId = null)
    {
        if ($product instanceof Mage_Catalog_Model_Product) {
            return $product;
        }

        $productId = (int)$product;
        $cacheKey = $productId.'_'.(string)$storeId;

        if (isset($this->_cacheLoadedProducts[$cacheKey])) {
            return $this->_cacheLoadedProducts[$cacheKey];
        }

        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product');
        $storeId !== null && $product->setStoreId((int)$storeId);
        $product->load($productId);

        return $this->_cacheLoadedProducts[$cacheKey] = $product;
    }

    /**
     * @param array $associatedProducts
     * @param Ess_M2ePro_Model_Magento_Product $product
     * @return array|mixed
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function prepareAssociatedProducts(array $associatedProducts, Ess_M2ePro_Model_Magento_Product $product)
    {
        $productType = $product->getTypeId();
        $productId   = $product->getProductId();

        if ($this->isSimpleType($productType) ||
            $this->isDownloadableType($productType)) {
            return array($productId);
        }

        if ($this->isBundleType($productType)) {
            $bundleAssociatedProducts = array();

            foreach ($associatedProducts as $key => $productIds) {
                $bundleAssociatedProducts[$key] = reset($productIds);
            }

            return $bundleAssociatedProducts;
        }

        if ($this->isConfigurableType($productType)) {
            $configurableAssociatedProducts = array();

            foreach ($associatedProducts as $productIds) {
                if (empty($configurableAssociatedProducts)) {
                    $configurableAssociatedProducts = $productIds;
                } else {
                    $configurableAssociatedProducts = array_intersect($configurableAssociatedProducts, $productIds);
                }
            }

            if (count($configurableAssociatedProducts) != 1) {
                throw new Ess_M2ePro_Model_Exception_Logic(
                    'There is no associated Product found for
                    Configurable Product.'
                );
            }

            return $configurableAssociatedProducts;
        }

        if ($this->isGroupedType($productType)) {
            return array_values($associatedProducts);
        }

        return array();
    }

    //########################################

    public function getTypesOptionArray()
    {
        $magentoProductTypes = Mage::getSingleton('catalog/product_type')->getOptionArray();
        $knownTypes = Mage::helper('M2ePro/Magento_Product')->getOriginKnownTypes();

        foreach ($magentoProductTypes as $type => $magentoProductTypeLabel) {
            if (in_array($type, $knownTypes)) {
                continue;
            }

            unset($magentoProductTypes[$type]);
        }

        return $magentoProductTypes;
    }

    //########################################
}
