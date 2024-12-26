<?php

class Ess_M2ePro_Model_Resource_Amazon_ProductType_AttributeMapping_CollectionFactory
{
    /**
     * @return Ess_M2ePro_Model_Resource_Amazon_ProductType_AttributeMapping_Collection
     */
    public function create()
    {
        /** @var Ess_M2ePro_Model_Resource_Amazon_ProductType_AttributeMapping_Collection */
        return Mage::getResourceModel('M2ePro/Amazon_ProductType_AttributeMapping_Collection');
    }
}
