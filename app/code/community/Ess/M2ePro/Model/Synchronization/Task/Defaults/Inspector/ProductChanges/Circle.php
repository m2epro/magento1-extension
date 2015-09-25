<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Synchronization_Task_Defaults_Inspector_ProductChanges_Circle
    extends Ess_M2ePro_Model_Synchronization_Task_Defaults_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/inspector/product_changes/circle/';
    }

    protected function getTitle()
    {
        return 'Product Changes';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 40;
    }

    protected function getPercentsEnd()
    {
        return 80;
    }

    //####################################

    protected function performActions()
    {
        $this->prepareBaseValues();
        $listingsProducts = $this->getNextListingsProducts();

        if ($listingsProducts === false) {
            return;
        }

        if (count($listingsProducts) <= 0) {

            $lastTime = strtotime($this->getLastTimeStartCircle());
            $interval = $this->getMinIntervalBetweenCircles();

            if ($lastTime + $interval < Mage::helper('M2ePro')->getCurrentGmtDate(true)) {
                $this->setLastListingProductId(0);
                $this->resetLastTimeStartCircle();
            }

            return;
        }

        $tempIndex = 0;
        $totalItems = count($listingsProducts);

        foreach ($listingsProducts as $listingProduct) {

            $this->updateListingsProductChange($listingProduct);

            if ((++$tempIndex)%20 == 0) {
                $percentsPerOneItem = $this->getPercentsInterval()/$totalItems;
                $this->getActualLockItem()->setPercents($percentsPerOneItem*$tempIndex);
                $this->getActualLockItem()->activate();
            }
        }

        $listingProduct = array_pop($listingsProducts);
        $this->setLastListingProductId($listingProduct->getId());
    }

    //####################################

    private function prepareBaseValues()
    {
        if (is_null($this->getLastListingProductId())) {
            $this->setLastListingProductId(0);
        }

        if (is_null($this->getLastTimeStartCircle())) {
            $this->resetLastTimeStartCircle();
        }
    }

    // ------------------------------------

    private function getMinIntervalBetweenCircles()
    {
        return (int)$this->getConfigValue($this->getFullSettingsPath(),'min_interval_between_circles');
    }

    private function getMaxCountTimesForFullCircle()
    {
        return (int)$this->getConfigValue($this->getFullSettingsPath(),'max_count_times_for_full_circle');
    }

    // ------------------------------------

    private function getMinCountItemsPerOneTime()
    {
        return (int)$this->getConfigValue($this->getFullSettingsPath(),'min_count_items_per_one_time');
    }

    private function getMaxCountItemsPerOneTime()
    {
        return (int)$this->getConfigValue($this->getFullSettingsPath(),'max_count_items_per_one_time');
    }

    // ------------------------------------

    private function getLastListingProductId()
    {
        return $this->getConfigValue($this->getFullSettingsPath(),'last_listing_product_id');
    }

    private function setLastListingProductId($listingProductId)
    {
        $this->setConfigValue($this->getFullSettingsPath(),'last_listing_product_id',(int)$listingProductId);
    }

    // ------------------------------------

    private function getLastTimeStartCircle()
    {
        return $this->getConfigValue($this->getFullSettingsPath(),'last_time_start_circle');
    }

    private function resetLastTimeStartCircle()
    {
        $this->setConfigValue(
            $this->getFullSettingsPath(),'last_time_start_circle',Mage::helper('M2ePro')->getCurrentGmtDate()
        );
    }

    //####################################

    private function getCountItemsPerOneTime()
    {
        $totalCount = (int)Mage::getModel('M2ePro/Listing_Product')->getCollection()->getSize();
        $perOneTime = (int)($totalCount / $this->getMaxCountTimesForFullCircle());

        if ($perOneTime < $this->getMinCountItemsPerOneTime()) {
            $perOneTime = $this->getMinCountItemsPerOneTime();
        }

        if ($perOneTime > $this->getMaxCountItemsPerOneTime()) {
            $perOneTime = $this->getMaxCountItemsPerOneTime();
        }

        return $perOneTime;
    }

    private function getNextListingsProducts()
    {
        $countOfProductChanges = Mage::getModel('M2ePro/ProductChange')->getCollection()->getSize();
        $productChangeMaxPerOneTime = $this->getConfigValue('/settings/product_change/', 'max_count_per_one_time');

        $limit = $productChangeMaxPerOneTime - $countOfProductChanges;

        if ($limit <= 0) {
            return false;
        }

        $limit > $this->getCountItemsPerOneTime() && $limit = $this->getCountItemsPerOneTime();

        $collection = Mage::getModel('M2ePro/Listing_Product')->getCollection();
        $collection->getSelect()
                   ->where("`id` > ".(int)$this->getLastListingProductId())
                   ->order(array('id ASC'))
                   ->limit($limit);

        return $collection->getItems();
    }

    //####################################

    private function updateListingsProductChange(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        Mage::getModel('M2ePro/ProductChange')
                    ->addUpdateAction($listingProduct->getProductId(),
                                       Ess_M2ePro_Model_ProductChange::INITIATOR_INSPECTOR);

        foreach ($listingProduct->getVariations(true) as $variation) {

            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */

            foreach ($variation->getOptions(true) as $option) {

                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */

                Mage::getModel('M2ePro/ProductChange')
                        ->addUpdateAction($option->getProductId(),
                                          Ess_M2ePro_Model_ProductChange::INITIATOR_INSPECTOR);
            }
        }
    }

    //####################################
}