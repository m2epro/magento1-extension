<?php

class Ess_M2ePro_Model_Resource_Walmart_Listing_CollectionFactory
{
    /**
     * @return Ess_M2ePro_Model_Resource_Walmart_Listing_Collection
     */
    public function create()
    {
        /** @var Ess_M2ePro_Model_Resource_Walmart_Listing_Collection */
        return Mage::getResourceModel('M2ePro/Walmart_Listing_Collection');
    }
}
