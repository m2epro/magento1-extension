<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
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

    //########################################

    protected function processTask($taskPath)
    {
        return parent::processTask('Templates_'.$taskPath);
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Synchronization_Templates_Runner $object
     */
    public function setRunner(Ess_M2ePro_Model_Synchronization_Templates_Runner $object)
    {
        $this->runner = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Synchronization_Templates_Runner
     */
    public function getRunner()
    {
        return $this->runner;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Templates_Inspector $object
     */
    public function setInspector(Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Templates_Inspector $object)
    {
        $this->inspector = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Templates_Inspector
     */
    public function getInspector()
    {
        return $this->inspector;
    }

    // ---------------------------------------

    /**
     * @param array $cache
     */
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

    //########################################

    /**
     * @param array $attributes
     * @param bool $withStoreFilter
     * @return array
     */
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

    //########################################
}