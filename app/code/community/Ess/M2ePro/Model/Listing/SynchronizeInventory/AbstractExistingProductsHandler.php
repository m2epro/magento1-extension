<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Resource_Collection_Abstract as AbstractCollection;
use Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Mass as AmazonProcessor;
use Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Mass as WalmartProcessor;

abstract class Ess_M2ePro_Model_Listing_SynchronizeInventory_AbstractExistingProductsHandler
    extends Ess_M2ePro_Model_Listing_SynchronizeInventory_AbstractHandler
{
    /** @var array */
    protected $_responseData;

    //########################################

    /**
     * @return AbstractCollection
     */
    abstract protected function getPreparedProductsCollection();

    /**
     * @param array $ids
     * @return Zend_Db_Statement_Interface
     */
    protected function getPdoStatementExistingListings(array $ids)
    {
        $ids = array_map(function ($id) { return (string) $id; }, $ids);

        $collection = $this->getPreparedProductsCollection();

        $collection->clear()->getSelect()->reset(Zend_Db_Select::WHERE);
        $collection->getSelect()->where("`second_table`.`{$this->getInventoryIdentifier()}` IN (?)", $ids);

        return Mage::getSingleton('core/resource')->getConnection('core_read')->query(
            $collection->getSelect()->__toString()
        );
    }

    /**
     * @param array $parentIds
     */
    protected function processParentProcessors(array $parentIds)
    {
        if (empty($parentIds)) {
            return;
        }

        $component = ucfirst($this->getComponentMode());

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::helper("M2ePro/Component_{$component}")->getCollection('Listing_Product');
        $collection->addFieldToFilter('id', array('in' => array_unique($parentIds)));

        $parentListingsProducts = $collection->getItems();
        if (empty($parentListingsProducts)) {
            return;
        }

        /** @var AmazonProcessor|WalmartProcessor $massProcessor */
        $massProcessor = Mage::getModel(
            "M2ePro/{$component}_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Mass"
        );
        $massProcessor->setListingsProducts($parentListingsProducts);
        $massProcessor->setForceExecuting(false);

        $massProcessor->execute();
    }

    /**
     * @param array $existData
     * @param array $newData
     * @param $key
     * @return bool
     */
    protected function isDataChanged($existData, $newData, $key)
    {
        if (!isset($existData[$key]) || !isset($newData[$key])) {
            return false;
        }

        return $existData[$key] != $newData[$key];
    }

    //########################################
}
