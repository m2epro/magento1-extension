<?php

class Ess_M2ePro_Model_Walmart_Account_Repository
{
    /** @var Ess_M2ePro_Model_Resource_Account_CollectionFactory */
    private $accountCollectionFactory;

    public function __construct()
    {
        $this->accountCollectionFactory = Mage::getResourceModel('M2ePro/Account_CollectionFactory');
    }

    /**
     * @return Ess_M2ePro_Model_Account[]
     */
    public function getAllItems()
    {
        $collection = $this->accountCollectionFactory->createWithWalmartChildMode();

        return array_values($collection->getItems());
    }
}