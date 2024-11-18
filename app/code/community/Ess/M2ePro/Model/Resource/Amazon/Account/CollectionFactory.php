<?php

class Ess_M2ePro_Model_Resource_Amazon_Account_CollectionFactory
{
    /**
     * @return Ess_M2ePro_Model_Resource_Amazon_Account_Collection
     */
    public function create()
    {
        /** @var Ess_M2ePro_Model_Resource_Amazon_Account_Collection */
        return Mage::getResourceModel('M2ePro/Amazon_Account_Collection');
    }
}