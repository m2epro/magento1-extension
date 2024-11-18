<?php

class Ess_M2ePro_Model_Amazon_Dictionary_TemplateShipping_Repository
{
    /** @var Ess_M2ePro_Model_Resource_Amazon_Dictionary_TemplateShipping_CollectionFactory */
    private $collectionFactory;

    public function __construct()
    {
        $this->collectionFactory = Mage::getResourceModel(
            'M2ePro/Amazon_Dictionary_TemplateShipping_CollectionFactory'
        );
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Dictionary_TemplateShipping[]
     */
    public function retrieveByAccountId($accountId)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            Ess_M2ePro_Model_Resource_Amazon_Dictionary_TemplateShipping::COLUMN_ACCOUNT_ID,
            $accountId
        );
        $collection->addOrder(
            Ess_M2ePro_Model_Resource_Amazon_Dictionary_TemplateShipping::COLUMN_TITLE,
            Varien_Data_Collection::SORT_ORDER_ASC
        );

        return array_values($collection->getItems());
    }

    /**
     * @return void
     */
    public function deleteByAccount(Ess_M2ePro_Model_Account $account)
    {
        $dictionaries = $this->retrieveByAccountId($account->getId());
        foreach ($dictionaries as $dictionary) {
            $dictionary->delete();
        }
    }
}