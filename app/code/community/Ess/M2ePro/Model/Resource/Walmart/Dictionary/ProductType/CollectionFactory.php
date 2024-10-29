<?php

class Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType_CollectionFactory
{
    /**
     * @return Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType_Collection
     */
    public function create()
    {
        /** @var Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType_Collection */
        return Mage::getResourceModel('M2ePro/Walmart_Dictionary_ProductType_Collection');
    }
}