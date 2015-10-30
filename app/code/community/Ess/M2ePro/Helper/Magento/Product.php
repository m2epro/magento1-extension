<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Magento_Product extends Mage_Core_Helper_Abstract
{
    const SKU_MAX_LENGTH = 64;

    //########################################

    private $cacheLoadedProducts = array();

    //########################################

    public function getCachedAndLoadedProduct($product, $storeId = NULL)
    {
        if ($product instanceof Mage_Catalog_Model_Product) {
            return $product;
        }

        $productId = (int)$product;
        $cacheKey = $productId.'_'.(string)$storeId;

        if (isset($this->cacheLoadedProducts[$cacheKey])) {
            return $this->cacheLoadedProducts[$cacheKey];
        }

        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product');
        !is_null($storeId) && $product->setStoreId((int)$storeId);
        $product->load($productId);

        return $this->cacheLoadedProducts[$cacheKey] = $product;
    }

    //########################################
}