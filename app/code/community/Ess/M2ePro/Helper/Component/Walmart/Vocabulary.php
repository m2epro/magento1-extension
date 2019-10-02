<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Walmart_Vocabulary extends Ess_M2ePro_Helper_Module_Product_Variation_Vocabulary
{
    //########################################

    public function addAttribute($productAttribute, $channelAttribute)
    {
        if (!parent::addAttribute($productAttribute, $channelAttribute)) {
            return;
        }

        $affectedParents = $this->getParentListingsProductsAffectedToAttribute($channelAttribute);
        if (empty($affectedParents)) {
            return;
        }

        $massProcessor = Mage::getModel(
            'M2ePro/Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Mass'
        );
        $massProcessor->setListingsProducts($affectedParents);
        $massProcessor->setForceExecuting(false);

        $massProcessor->execute();
    }

    public function addOption($productOption, $channelOption, $channelAttribute)
    {
        if (!parent::addOption($productOption, $channelOption, $channelAttribute)) {
            return;
        }

        $affectedParents = $this->getParentListingsProductsAffectedToOption($channelAttribute, $channelOption);
        if (empty($affectedParents)) {
            return;
        }

        $massProcessor = Mage::getModel(
            'M2ePro/Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Mass'
        );
        $massProcessor->setListingsProducts($affectedParents);
        $massProcessor->setForceExecuting(false);

        $massProcessor->execute();
    }

    //########################################

    protected function getParentListingsProductsAffectedToAttribute($channelAttribute)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $collection->addFieldToFilter('is_variation_parent', 1);

        $collection->addFieldToFilter(
            'additional_data', array('regexp' => '"variation_channel_attributes":.*"'.$channelAttribute.'"')
        );

        return $collection->getItems();
    }

    protected function getParentListingsProductsAffectedToOption($channelAttribute, $channelOption)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $collection->addFieldToFilter('variation_parent_id', array('notnull' => true));

        $collection->addFieldToFilter(
            'additional_data', array(
            'regexp'=> '"variation_channel_options":.*"'.$channelAttribute.'":"'.$channelOption.'"}')
        );

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            array(
            'second_table.variation_parent_id'
            )
        );

        $parentIds = $collection->getColumnValues('variation_parent_id');
        if (empty($parentIds)) {
            return array();
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $collection->addFieldToFilter('is_variation_parent', 1);
        $collection->addFieldToFilter('id', array('in' => $parentIds));

        return $collection->getItems();
    }

    //########################################
}
