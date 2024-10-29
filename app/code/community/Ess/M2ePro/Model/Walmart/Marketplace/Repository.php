<?php

use Ess_M2ePro_Model_Resource_Marketplace as MarketplaceResource;

class Ess_M2ePro_Model_Walmart_Marketplace_Repository
{
    /** @var Ess_M2ePro_Model_Resource_Marketplace_CollectionFactory */
    private $collectionFactory;
    /** @var Ess_M2ePro_Model_Resource_Marketplace */
    private $marketplaceResource;
    /** @var Ess_M2ePro_Model_MarketplaceFactory */
    private $marketplaceFactory;

    public function __construct()
    {
        $this->collectionFactory = Mage::getResourceModel('M2ePro/Marketplace_CollectionFactory');
        $this->marketplaceResource = Mage::getResourceModel('M2ePro/Marketplace');
        $this->marketplaceFactory = Mage::getModel('M2ePro/MarketplaceFactory');
    }

    /**
     * @param int $marketplaceId
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
     * @param int $marketplaceId
     * @return Ess_M2ePro_Model_Marketplace|null
     */
    public function find($marketplaceId)
    {
        $model = $this->marketplaceFactory->createEmpty();
        $this->marketplaceResource->load($model, $marketplaceId);
        if ($model->isObjectNew()) {
            return null;
        }

        if (!$model->isComponentModeWalmart()) {
            return null;
        }

        return $model;
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace[]
     */
    public function findActive()
    {
        $collection = $this->collectionFactory->createWithWalmartChildMode();
        $collection->addFieldToFilter(MarketplaceResource::COLUMN_STATUS,
            array('eq' => Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
        )
                   ->setOrder(MarketplaceResource::COLUMN_SORDER, 'ASC');

        return array_values($collection->getItems());
    }
}