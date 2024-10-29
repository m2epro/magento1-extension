<?php

use Ess_M2ePro_Model_Resource_Walmart_Dictionary_Marketplace as MarketplaceDictionaryResource;

class Ess_M2ePro_Model_Walmart_Dictionary_Marketplace_Repository
{
    /** @var Ess_M2ePro_Model_Resource_Walmart_Dictionary_Marketplace_CollectionFactory */
    private $marketplaceDictionaryCollectionFactory;
    /** @var Ess_M2ePro_Model_Resource_Walmart_Dictionary_Marketplace */
    private $marketplaceDictionaryResource;

    public function __construct()
    {
        $this->marketplaceDictionaryCollectionFactory = Mage::getResourceModel(
            'M2ePro/Walmart_Dictionary_Marketplace_CollectionFactory'
        );
        $this->marketplaceDictionaryResource = Mage::getResourceModel(
            'M2ePro/Walmart_Dictionary_Marketplace'
        );
    }

    /**
     * @return void
     */
    public function create(Ess_M2ePro_Model_Walmart_Dictionary_Marketplace $marketplaceDictionary)
    {
        $this->marketplaceDictionaryResource->save($marketplaceDictionary);
    }

    /**
     * @param int $marketplaceId
     * @return void
     */
    public function removeByMarketplace($marketplaceId)
    {
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        /** @var Varien_Db_Adapter_Pdo_Mysql $connWrite */
        $connWrite = $resource->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);
        $connWrite->delete(
            $this->marketplaceDictionaryResource->getMainTable(),
            array(MarketplaceDictionaryResource::COLUMN_MARKETPLACE_ID . ' = ?' => $marketplaceId)
        );
    }

    /**
     * @param int $marketplaceId
     * @return Ess_M2ePro_Model_Walmart_Dictionary_Marketplace|null
     */
    public function findByMarketplaceId($marketplaceId)
    {
        $collection = $this->marketplaceDictionaryCollectionFactory->create();
        $collection->addFieldToFilter(MarketplaceDictionaryResource::COLUMN_MARKETPLACE_ID, $marketplaceId);

        /** @var Ess_M2ePro_Model_Walmart_Dictionary_Marketplace $dictionary */
        $dictionary = $collection->getFirstItem();
        if ($dictionary->isObjectNew()) {
            return null;
        }

        return $dictionary;
    }
}
