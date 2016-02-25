<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Synchronization_Templates_Changes
{
    private $component = NULL;
    private $cache = array();

    //########################################

    public function setComponent($component)
    {
        $this->component = $component;
    }

    public function getComponent()
    {
        return $this->component;
    }

    //########################################

    public function init()
    {
        if (!isset($this->cache['listings_products'])) {
            $this->cache['listings_products'] = array();
        }

        if (!isset($this->cache['listings_products_by_params'])) {
            $this->cache['listings_products_by_params'] = array();
        }
    }

    public function clearCache()
    {
        $this->cache = array();
    }

    //########################################

    /**
     * @param array $attributes
     * @param bool $withStoreFilter
     * @return array
     */
    public function getInstances(array $attributes, $withStoreFilter = false)
    {
        return $this->getListingProducts($attributes,
                                         $withStoreFilter,
                                         'getChangedItems');
    }

    /**
     * @param array $attributes
     * @param bool $withStoreFilter
     * @return array
     */
    public function getInstancesByListingProduct(array $attributes, $withStoreFilter = false)
    {
        return $this->getListingProducts($attributes,
                                         $withStoreFilter,
                                         'getChangedItemsByListingProduct');
    }

    /**
     * @param array $attributes
     * @param bool $withStoreFilter
     * @return array
     */
    public function getInstancesByVariationOption(array $attributes, $withStoreFilter = false)
    {
        return $this->getListingProducts($attributes,
                                         $withStoreFilter,
                                         'getChangedItemsByVariationOption');
    }

    //########################################

    private function getListingProducts(array $attributes, $withStoreFilter = false, $fetchFunction)
    {
        $args = func_get_args();
        $cacheKey = md5(json_encode($args));

        if (isset($this->cache['listings_products_by_params'][$cacheKey])) {
            return $this->cache['listings_products_by_params'][$cacheKey];
        }

        $this->cache['listings_products_by_params'][$cacheKey] = array();

        $listingProductsIds = array();
        $resultListingProducts = array();

        $changedListingsProducts =
            Mage::getResourceModel('M2ePro/'.ucfirst($this->getComponent()).'_Listing_Product')->$fetchFunction(
                $attributes,
                $withStoreFilter
            );

        foreach ($changedListingsProducts as $key => $listingProductData) {

            $lpId = $listingProductData['id'];

            if (!isset($this->cache['listings_products'][$lpId])) {
                $listingProductsIds[$key] = $lpId;
                continue;
            }

            $resultListingProducts[$lpId] = $this->cache['listings_products'][$lpId];
            $resultListingProducts[$lpId]->addData($listingProductData);
            $resultListingProducts[$lpId]->getMagentoProduct()->enableCache();

            $this->cache['listings_products_by_params'][$cacheKey][$lpId] = $resultListingProducts[$lpId];

            unset($changedListingsProducts[$key]);
        }

        if (empty($changedListingsProducts)) {
            return $this->cache['listings_products_by_params'][$cacheKey] = $resultListingProducts;
        }

        $helper = Mage::helper('M2ePro/Component_'.ucfirst($this->getComponent()));

        $listingProducts = $helper->getCollection('Listing_Product')
                                  ->addFieldToFilter('listing_product_id',array('in' => $listingProductsIds))
                                  ->getItems();

        foreach ($listingProductsIds as $key => $lpId) {

            if (!isset($listingProducts[$lpId])) {
                continue;
            }

            $listingProducts[$lpId]->addData($changedListingsProducts[$key]);
            $listingProducts[$lpId]->getMagentoProduct()->enableCache();

            $this->cache['listings_products'][$lpId] = $listingProducts[$lpId];
            $this->cache['listings_products_by_params'][$cacheKey][$lpId] = $listingProducts[$lpId];
        }

        return $this->cache['listings_products_by_params'][$cacheKey];
    }

    //########################################
}