<?php

class Ess_M2ePro_Model_Resource_Amazon_Template_Shipping_CollectionFactory
{
    /**
     * @return Ess_M2ePro_Model_Resource_Amazon_Template_Shipping_Collection
     */
    public function create()
    {
        /** @var Ess_M2ePro_Model_Resource_Amazon_Template_Shipping_Collection */
        return Mage::getResourceModel('M2ePro/Amazon_Template_Shipping_Collection');
    }
}