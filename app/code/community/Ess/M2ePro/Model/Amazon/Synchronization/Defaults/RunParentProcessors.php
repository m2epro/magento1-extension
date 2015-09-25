<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Amazon_Synchronization_Defaults_RunParentProcessors
    extends Ess_M2ePro_Model_Amazon_Synchronization_Defaults_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/run_parent_processors/';
    }

    protected function getTitle()
    {
        return 'Update Variation Parent Listing Products';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 10;
    }

    // -----------------------------------

    protected function intervalIsEnabled()
    {
        return true;
    }

    //####################################

    protected function performActions()
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('is_variation_parent', 1);
        $listingProductCollection->addFieldToFilter('variation_parent_need_processor', 1);
        $listingProductCollection->setPageSize(
            Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Mass
                ::MAX_PROCESSORS_COUNT_PER_ONE_TIME
        );

        $listingsProducts = $listingProductCollection->getItems();

        if (empty($listingsProducts)) {
            return;
        }

        $massProcessor = Mage::getModel(
            'M2ePro/Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Mass'
        );
        $massProcessor->setListingsProducts($listingsProducts);

        $massProcessor->execute();
    }

    //####################################
}