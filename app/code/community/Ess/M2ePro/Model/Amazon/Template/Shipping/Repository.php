<?php

class Ess_M2ePro_Model_Amazon_Template_Shipping_Repository
{
    /** @var Ess_M2ePro_Model_Resource_Amazon_Template_Shipping_CollectionFactory */
    private $collectionFactory;

    public function __construct()
    {
        $this->collectionFactory = Mage::getResourceModel(
            'M2ePro/Amazon_Template_Shipping_CollectionFactory'
        );
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Dictionary_TemplateShipping[]
     */
    public function retrieveByAccountId($accountId)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            Ess_M2ePro_Model_Resource_Amazon_Template_Shipping::COLUMN_ACCOUNT_ID,
            $accountId
        );
        $collection->addOrder(
            Ess_M2ePro_Model_Resource_Amazon_Template_Shipping::COLUMN_TITLE,
            Varien_Data_Collection::SORT_ORDER_ASC
        );

        return array_values($collection->getItems());
    }

    /**
     * @return void
     */
    public function deleteByAccount(Ess_M2ePro_Model_Account $account)
    {
        $templates = $this->retrieveByAccountId($account->getId());
        foreach ($templates as $template) {
            $template->delete();
        }
    }
}