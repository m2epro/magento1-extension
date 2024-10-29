<?php

use Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType as ProductTypeDictionaryResource;

class Ess_M2ePro_Model_Walmart_Dictionary_ProductType_Repository
{
    /** @var Ess_M2ePro_Model_Walmart_Dictionary_ProductTypeFactory */
    private $productTypeDictionaryFactory;
    /** @var Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType_CollectionFactory */
    private $productTypeDictionaryCollectionFactory;
    /** @var Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType */
    private $productTypeDictionaryResource;

    private $runtimeCache = array();

    public function __construct()
    {
        $this->productTypeDictionaryFactory = Mage::getModel('M2ePro/Walmart_Dictionary_ProductTypeFactory');
        $this->productTypeDictionaryCollectionFactory = Mage::getResourceModel(
            'M2ePro/Walmart_Dictionary_ProductType_CollectionFactory'
        );
        $this->productTypeDictionaryResource = Mage::getResourceModel('M2ePro/Walmart_Dictionary_ProductType');
    }

    /**
     * @param int $id
     * @return Ess_M2ePro_Model_Walmart_Dictionary_ProductType|null
     */
    public function find($id)
    {
        if (($model = $this->tryGetFromRuntimeCache($id)) !== null) {
            return $model;
        }

        $model = $this->productTypeDictionaryFactory->createEmpty();
        $this->productTypeDictionaryResource->load($model, $id);

        if ($model->isObjectNew()) {
            return null;
        }

        $this->addToRuntimeCache($model);

        return $model;
    }

    /**
     * @param int $id
     * @return Ess_M2ePro_Model_Walmart_Dictionary_ProductType
     * @throws Ess_M2ePro_Model_Exception_EntityNotFound
     */
    public function get($id)
    {
        $model = $this->find($id);
        if ($model === null) {
            throw new Ess_M2ePro_Model_Exception_EntityNotFound("Product Type Dictionary with id $id not found.");
        }

        return $model;
    }

    /**
     * @param string $nick
     * @param int $marketplaceId
     * @return Ess_M2ePro_Model_Walmart_Dictionary_ProductType|null
     */
    public function findByNick($nick, $marketplaceId)
    {
        $collection = $this->productTypeDictionaryCollectionFactory->create();
        $collection->addFieldToFilter(ProductTypeDictionaryResource::COLUMN_NICK, $nick);
        $collection->addFieldToFilter(ProductTypeDictionaryResource::COLUMN_MARKETPLACE_ID, $marketplaceId);

        /** @var Ess_M2ePro_Model_Walmart_Dictionary_ProductType $entity */
        $entity = $collection->getFirstItem();
        if ($entity->isObjectNew()) {
            return null;
        }

        return $entity;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Dictionary_ProductType[]
     */
    public function retrieveByMarketplace(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $collection = $this->productTypeDictionaryCollectionFactory->create();
        $collection->addFieldToFilter(ProductTypeDictionaryResource::COLUMN_MARKETPLACE_ID, $marketplace->getId());

        return array_values($collection->getItems());
    }

    /**
     * @return void
     */
    public function create(Ess_M2ePro_Model_Walmart_Dictionary_ProductType $dictionaryProductType)
    {
        $this->productTypeDictionaryResource->save($dictionaryProductType);
    }

    /**
     * @return void
     */
    public function save(Ess_M2ePro_Model_Walmart_Dictionary_ProductType $dictionaryProductType)
    {
        $this->productTypeDictionaryResource->save($dictionaryProductType);
    }

    /**
     * @return void
     */
    public function remove(Ess_M2ePro_Model_Walmart_Dictionary_ProductType $dictionaryProductType)
    {
        $this->productTypeDictionaryResource->delete($dictionaryProductType);
    }

    // ----------------------------------------

    /**
     * @return void
     */
    private function addToRuntimeCache(Ess_M2ePro_Model_Walmart_Dictionary_ProductType $dictionaryProductType)
    {
        $this->runtimeCache[(int)$dictionaryProductType->getId()] = $dictionaryProductType;
    }

    /**
     * @param int $id
     * @return void
     */
    private function removeFromRuntimeCache($id)
    {
        unset($this->runtimeCache[$id]);
    }

    /**
     * @param int $id
     * @return Ess_M2ePro_Model_Walmart_Dictionary_ProductType|null
     */
    private function tryGetFromRuntimeCache($id)
    {
        return !empty($this->runtimeCache[$id]) ? $this->runtimeCache[$id] : null;
    }
}
