<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Mass
{
    const MAX_PROCESSORS_COUNT_PER_ONE_TIME = 1000;

    /** @var Ess_M2ePro_Model_Listing_Product[] $_listingsProducts */
    protected $_listingsProducts = array();

    protected $_forceExecuting = true;

    //########################################

    /**
     * @param array $listingsProducts
     * @return $this
     */
    public function setListingsProducts(array $listingsProducts)
    {
        $this->_listingsProducts = $listingsProducts;
        return $this;
    }

    /**
     * @param bool $forceExecuting
     * @return $this
     */
    public function setForceExecuting($forceExecuting = true)
    {
        $this->_forceExecuting = $forceExecuting;
        return $this;
    }

    //########################################

    public function execute()
    {
        $uniqueProcessors = $this->getUniqueProcessors();

        $alreadyProcessed = array();

        foreach ($uniqueProcessors as $listingProductId => $processor) {
            if (!$this->_forceExecuting && count($alreadyProcessed) >= self::MAX_PROCESSORS_COUNT_PER_ONE_TIME) {
                break;
            }

            try {
                $processor->process();
            } catch (\Exception $exception) {
                Mage::helper('M2ePro/Module_Exception')->process($exception, false);
                continue;
            }

            $alreadyProcessed[] = $listingProductId;
        }

        if ($this->_forceExecuting || count($uniqueProcessors) <= count($alreadyProcessed)) {
            return;
        }

        $notProcessedListingProductIds = array_unique(array_diff(array_keys($uniqueProcessors), $alreadyProcessed));

        $resource  = Mage::getSingleton('core/resource');
        $connWrite = $resource->getConnection('core_write');

        $connWrite->update(
            Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_amazon_listing_product'),
            array('variation_parent_need_processor' => 1),
            array(
                'is_variation_parent = ?'   => 1,
                'listing_product_id IN (?)' => $notProcessedListingProductIds,
            )
        );
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor[]
     */
    protected function getUniqueProcessors()
    {
        $processors = array();

        foreach ($this->_listingsProducts as $listingProduct) {
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

    //########################################
}