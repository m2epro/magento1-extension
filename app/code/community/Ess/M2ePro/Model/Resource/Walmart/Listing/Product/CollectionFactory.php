<?php

class Ess_M2ePro_Model_Resource_Walmart_Listing_Product_CollectionFactory
{
    /**
     * @returnEss_M2ePro_Model_Resource_Walmart_Listing_Product_Collection
     */
    public function create()
    {
        /** @var Ess_M2ePro_Model_Resource_Walmart_Listing_Product_Collection */
        return Mage::getResourceModel('M2ePro/Walmart_Listing_Product_Collection');
    }
}
