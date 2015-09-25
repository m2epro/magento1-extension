<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Mass
{
    const MAX_PROCESSORS_COUNT_PER_ONE_TIME = 1000;

    // #################################

    /** @var Ess_M2ePro_Model_Listing_Product[] $listingsProducts */
    private $listingsProducts = array();

    private $forceExecuting = true;

    // #################################

    public function setListingsProducts(array $listingsProducts)
    {
        $this->listingsProducts = $listingsProducts;
        return $this;
    }

    public function setForceExecuting($forceExecuting = true)
    {
        $this->forceExecuting = $forceExecuting;
        return $this;
    }

    // #################################

    public function execute()
    {
        $uniqueProcessors = $this->getUniqueProcessors();

        $alreadyProcessed = array();

        foreach ($uniqueProcessors as $listingProductId => $processor) {
            if (!$this->forceExecuting && count($alreadyProcessed) >= self::MAX_PROCESSORS_COUNT_PER_ONE_TIME) {
                break;
            }

            $processor->process();

            $alreadyProcessed[] = $listingProductId;
        }

        if ($this->forceExecuting || count($uniqueProcessors) <= count($alreadyProcessed)) {
            return;
        }

        $notProcessedListingProductIds = array_unique(array_diff(array_keys($uniqueProcessors), $alreadyProcessed));

        $resource  = Mage::getSingleton('core/resource');
        $connWrite = $resource->getConnection('core_write');

        $connWrite->update(
            $resource->getTableName('m2epro_amazon_listing_product'),
            array('variation_parent_need_processor' => 1),
            array(
                'is_variation_parent = ?'   => 1,
                'listing_product_id IN (?)' => $notProcessedListingProductIds,
            )
        );
    }

    // #################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor[]
     */
    private function getUniqueProcessors()
    {
        $processors = array();

        foreach ($this->listingsProducts as $listingProduct) {
            if (isset($processors[$listingProduct->getId()])) {
                continue;
            }

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();
            $variationManager = $amazonListingProduct->getVariationManager();

            if (!$variationManager->isRelationParentType()) {
                continue;
            }

            $processors[$listingProduct->getId()] = $variationManager->getTypeModel()->getProcessor();
        }

        return $processors;
    }

    // #################################
}