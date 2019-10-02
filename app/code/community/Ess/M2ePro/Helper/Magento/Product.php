<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Magento_Product extends Mage_Core_Helper_Abstract
{
    const SKU_MAX_LENGTH = 64;

    protected $_cacheLoadedProducts = array();

    //########################################

    public function getCachedAndLoadedProduct($product, $storeId = NULL)
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
        $productId   = $product->getProductId();

        if ($product->isSimpleType() ||
            $product->isDownloadableType()) {
            return array($productId);
        }

        if ($product->isBundleType()) {
            $bundleAssociatedProducts = array();

            foreach ($associatedProducts as $key => $productIds) {
                $bundleAssociatedProducts[$key] = reset($productIds);
            }

            return $bundleAssociatedProducts;
        }

        if ($product->isConfigurableType()) {
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

        if ($product->isGroupedType()) {
            return array_values($associatedProducts);
        }

        return array();
    }

    //########################################
}
