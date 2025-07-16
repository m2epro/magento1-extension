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
     * @param int $accountId
     * @return Ess_M2ePro_Model_Account|null
     */
    public function find($accountId)
    {
        $collection = $this->accountCollectionFactory->createWithWalmartChildMode();
        $collection->addFieldToFilter(
            Ess_M2ePro_Model_Resource_Walmart_Account::COLUMN_ACCOUNT_ID, $accountId
        );

        $account = $collection->getFirstItem();

        if ($account->isObjectNew()) {
            return null;
        }

        return $account;
    }

    /**
     * @param int $accountId
     * @return Ess_M2ePro_Model_Account
     */
    public function get($id)
    {
        $account = $this->find($id);
        if ($account === null) {
            throw new \LogicException("Account '$id' not found.");
        }

        return $account;
    }

    /**
     * @param $id
     * @return bool
     */
    public function isAccountExists($id)
    {
        return $this->find($id) !== null;
    }

    /**
     * @param $identifier
     * @return bool
     */
    public function isAccountExistsByIdentifier($identifier)
    {
        $collection = $this->accountCollectionFactory->createWithWalmartChildMode();
        $collection->addFieldToFilter(Ess_M2ePro_Model_Resource_Walmart_Account::COLUMN_IDENTIFIER, $identifier);

        $account = $collection->getFirstItem();
        if ($account->isObjectNew()) {
            return false;
        }

        return true;
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