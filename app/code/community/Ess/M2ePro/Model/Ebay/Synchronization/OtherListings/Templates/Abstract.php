<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Templates_Abstract
    extends Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Synchronization_Templates_Runner
     */
    protected $runner = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Templates_Inspector $inspector
     */
    protected $inspector = NULL;

    protected $cache = array();

    //####################################

    protected function processTask($taskPath)
    {
        return parent::processTask('Templates_'.$taskPath);
    }

    //------------------------------------

    public function setRunner(Ess_M2ePro_Model_Synchronization_Templates_Runner $object)
    {
        $this->runner = $object;
    }

    public function getRunner()
    {
        return $this->runner;
    }

    //------------------------------------

    public function setInspector(Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Templates_Inspector $object)
    {
        $this->inspector = $object;
    }

    public function getInspector()
    {
        return $this->inspector;
    }

    //------------------------------------

    public function setCache(array &$cache)
    {
        $this->cache = &$cache;

        if (!isset($this->cache['listings_other_products'])) {
            $this->cache['listings_other_products'] = array();
        }

        if (!isset($this->cache['listings_other_products_by_params'])) {
            $this->cache['listings_other_products_by_params'] = array();
        }
    }

    //####################################

    protected function getChangedInstances(array $attributes, $withStoreFilter = false)
    {
        $args = func_get_args();
        $cacheKey = md5(json_encode($args));

        if (isset($this->cache['listings_other_products_by_params'][$cacheKey])) {
            return $this->cache['listings_other_products_by_params'][$cacheKey];
        }

        $this->cache['listings_other_products_by_params'][$cacheKey] = array();

        $changedListingOtherProducts = Mage::getResourceModel('M2ePro/Listing_Other')->getChangedItems(
            $attributes,
            Ess_M2ePro_Helper_Component_Ebay::NICK,
            $withStoreFilter
        );

        $listingOtherProductsIds = array();
        $resultListingOtherProducts = array();

        foreach ($changedListingOtherProducts as $key => $listingOtherProductData) {

            $loId = $listingOtherProductData['id'];

            if (!isset($this->cache['listings_other_products'][$loId])) {
                $listingOtherProductsIds[$key] = $loId;
                continue;
            }

            $resultListingOtherProducts[$loId] = $this->cache['listings_other_products'][$loId];
            $resultListingOtherProducts[$loId]->addData($listingOtherProductData);
            $resultListingOtherProducts[$loId]->getMagentoProduct()->enableCache();

            $this->cache['listings_other_products_by_params'][$cacheKey][$loId] = $resultListingOtherProducts[$loId];

            unset($changedListingOtherProducts[$key]);
        }

        if (empty($changedListingOtherProducts)) {
            return $this->cache['listings_other_products_by_params'][$cacheKey] = $resultListingOtherProducts;
        }

        $listingOtherProducts = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Listing_Other')
            ->addFieldToFilter('id',array('in' => $listingOtherProductsIds))
            ->getItems();

        foreach ($listingOtherProductsIds as $key => $loId) {

            $listingOtherProducts[$loId]->addData($changedListingOtherProducts[$key]);
            $listingOtherProducts[$loId]->getMagentoProduct()->enableCache();

            $this->cache['listings_other_products'][$loId] = $listingOtherProducts[$loId];
            $this->cache['listings_other_products_by_params'][$cacheKey][$loId] = $listingOtherProducts[$loId];
        }

        return $this->cache['listings_other_products_by_params'][$cacheKey];
    }

    //####################################
}