<?php

class Ess_M2ePro_Model_Amazon_Marketplace_Repository
{
    /** @var Ess_M2ePro_Model_Resource_Amazon_Account */
    private $amazonAccountResource;
    /** @var Ess_M2ePro_Model_Resource_Marketplace */
    private $marketplaceResource;
    /** @var Ess_M2ePro_Model_Resource_Marketplace_CollectionFactory */
    private $marketplaceCollectionFactory;

    public function __construct()
    {
        $this->amazonAccountResource = Mage::getResourceModel('M2ePro/Amazon_Account');
        $this->marketplaceResource = Mage::getResourceModel('M2ePro/Marketplace');
        $this->marketplaceCollectionFactory= Mage::getResourceModel('M2ePro/Marketplace_CollectionFactory');
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function get($marketplaceId)
    {
        $marketplace = $this->find($marketplaceId);
        if ($marketplace === null) {
            throw new \RuntimeException("Marketplace '$marketplaceId' not found");
        }

        return $marketplace;
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace|null
     */
    public function find($marketplaceId)
    {
        /** @var Ess_M2ePro_Model_Marketplace $model */
        $model = Mage::getModel('M2ePro/Marketplace');
        $this->marketplaceResource->load($model, $marketplaceId);
        if ($model->isObjectNew()) {
            return null;
        }

        if (!$model->isComponentModeAmazon()) {
            return null;
        }

        return $model;
    }

    /**
     * @param int $nativeId
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function findByNativeId($nativeId)
    {
        $collection = Mage::getResourceModel('M2ePro/Marketplace_Collection');
        $collection->addFieldToFilter(
            Ess_M2ePro_Model_Resource_Marketplace::COLUMN_NATIVE_ID,
            array('eq' => $nativeId)
        );

        $marketplace = $collection->getFirstItem();
        if ($marketplace->isObjectNew()) {
            return null;
        }

        return $marketplace;
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace[]
     */
    public function findWithAccounts()
    {
        $collection = Mage::getResourceModel('M2ePro/Marketplace_Collection');
        $collection->getSelect()->joinInner(
            array('account' => $this->amazonAccountResource->getMainTable()),
            sprintf(
                'main_table.%s = account.%s',
                Ess_M2ePro_Model_Resource_Marketplace::COLUMN_ID,
                Ess_M2ePro_Model_Resource_Amazon_Account::COLUMN_MARKETPLACE_ID
            ),
            array()
        );
        $collection->getSelect()->group(sprintf(
            'main_table.%s',
            Ess_M2ePro_Model_Resource_Marketplace::COLUMN_ID)
        );

        return array_values($collection->getItems());
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Marketplace[]
     */
    public function getAll()
    {
        $collection = $this->marketplaceCollectionFactory->createWithAmazonChildMode();
        $collection->setOrder(Ess_M2ePro_Model_Resource_Marketplace::COLUMN_SORDER, 'ASC');

        return array_values($collection->getItems());
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Marketplace[]
     */
    public function findActive()
    {
        $collection = $this->marketplaceCollectionFactory->createWithAmazonChildMode();
        $collection->addFieldToFilter(
            Ess_M2ePro_Model_Resource_Marketplace::COLUMN_STATUS,
            array('eq' => Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
        )
                   ->setOrder(Ess_M2ePro_Model_Resource_Marketplace::COLUMN_SORDER, 'ASC');

        return array_values($collection->getItems());
    }
}