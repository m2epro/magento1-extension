<?php

class Ess_M2ePro_Model_Walmart_ProductType_Repository
{
    /** @var Ess_M2ePro_Model_Resource_Walmart_ProductType */
    private $productTypeResource;
    /** @var Ess_M2ePro_Model_Resource_Walmart_ProductType_CollectionFactory */
    private $productTypeCollectionFactory;
    /** @var Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType */
    private $productTypeDictionaryResource;
    /** @var Ess_M2ePro_Model_Walmart_ProductTypeFactory */
    private $productTypeFactory;
    /** @var Ess_M2ePro_Model_Resource_Walmart_Listing_CollectionFactory */
    private $listingCollectionFactory;
    /** @var Ess_M2ePro_Model_Resource_Walmart_Listing_Auto_Category_Group_CollectionFactory */
    private $autoCategoryGroupCollectionFactory;
    /** @var Ess_M2ePro_Helper_Component_Walmart */
    private $walmartHelper;

    private $runtimeCache = array();

    public function __construct()
    {
        $this->productTypeResource = Mage::getResourceModel('M2ePro/Walmart_ProductType');
        $this->productTypeCollectionFactory = Mage::getResourceModel('M2ePro/Walmart_ProductType_CollectionFactory');
        $this->productTypeDictionaryResource = Mage::getResourceModel('M2ePro/Walmart_Dictionary_ProductType');
        $this->productTypeFactory = Mage::getModel('M2ePro/Walmart_ProductTypeFactory');
        $this->listingCollectionFactory = Mage::getResourceModel('M2ePro/Walmart_Listing_CollectionFactory');;
        $this->autoCategoryGroupCollectionFactory = Mage::getResourceModel(
            'M2ePro/Walmart_Listing_Auto_Category_Group_CollectionFactory'
        );
        $this->walmartHelper = Mage::helper('M2ePro/Component_Walmart');
    }

    /**
     * @param int $id
     * @return Ess_M2ePro_Model_Walmart_ProductType|null
     */
    public function find($id)
    {
        if (($model = $this->tryGetFromRuntimeCache($id)) !== null) {
            return $model;
        }

        $model = $this->productTypeFactory->createEmpty();
        $this->productTypeResource->load($model, $id);

        if ($model->isObjectNew()) {
            return null;
        }

        $this->addToRuntimeCache($model);

        return $model;
    }

    /**
     * @param int $id
     * @return Ess_M2ePro_Model_Walmart_ProductType
     * @throws Ess_M2ePro_Model_Exception_EntityNotFound
     */
    public function get($id)
    {
        $model = $this->find($id);
        if ($model === null) {
            throw new Ess_M2ePro_Model_Exception_EntityNotFound("Product Type $id not found.");
        }

        return $model;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function isExists($id)
    {
        return $this->find($id) !== null;
    }

    /**
     * @param Ess_M2ePro_Model_Walmart_ProductType $productType
     * @return void
     */
    public function delete(Ess_M2ePro_Model_Walmart_ProductType $productType)
    {
        $this->productTypeResource->delete($productType);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isUsed(Ess_M2ePro_Model_Walmart_ProductType $productType)
    {
        $productTypeId = (int)$productType->getId();

        return $this->isUsedInListings($productTypeId)
            || $this->isUsedInProducts($productTypeId)
            || $this->isUsedInAutoCategoryGroups($productTypeId);
    }

    /**
     * @param int $productTypeId
     * @return bool
     */
    private function isUsedInListings($productTypeId)
    {
        $listingCollection = $this->listingCollectionFactory->create();
        $listingCollection->getSelect()
            ->where(
                sprintf(
                    '%s = ? OR %s = ?',
                    Ess_M2ePro_Model_Resource_Walmart_Listing::COLUMN_AUTO_GLOBAL_ADDING_PRODUCT_TYPE_ID,
                    Ess_M2ePro_Model_Resource_Walmart_Listing::COLUMN_AUTO_WEBSITE_ADDING_PRODUCT_TYPE_ID
                ),
                $productTypeId
            )
            ->limit(1);

        /** @var Ess_M2ePro_Model_Walmart_Listing $listing */
        $listing = $listingCollection->getFirstItem();

        return !$listing->isObjectNew();
    }

    /**
     * @param int $productTypeId
     * @return bool
     */
    private function isUsedInProducts($productTypeId)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $productCollection */
        $productCollection = $this->walmartHelper->getCollection('Listing_Product');
        $productCollection->getSelect()
            ->where(
                sprintf('%s = ?', Ess_M2ePro_Model_Resource_Walmart_Listing_Product::COLUMN_PRODUCT_TYPE_ID),
                $productTypeId
            )
            ->limit(1);

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $product */
        $product = $productCollection->getFirstItem();

        return !$product->isObjectNew();
    }

    /**
     * @param int $productTypeId
     * @return bool
     */
    private function isUsedInAutoCategoryGroups($productTypeId)
    {
        $autoCategoryGroupCollection = $this->autoCategoryGroupCollectionFactory->create();
        $autoCategoryGroupCollection->getSelect()
            ->where(
                sprintf(
                    '%s = ?',
                    Ess_M2ePro_Model_Resource_Walmart_Listing_Auto_Category_Group::COLUMN_ADDING_PRODUCT_TYPE_ID
                ),
                $productTypeId
            )
            ->limit(1);

        /** @var Ess_M2ePro_Model_Walmart_Listing_Auto_Category_Group $autoCategoryGroup */
        $autoCategoryGroup = $autoCategoryGroupCollection->getFirstItem();

        return !$autoCategoryGroup->isObjectNew();
    }

    // ---------------------------------------

    /**
     * @param int $marketplaceId
     * @return list<string, Ess_M2ePro_Model_Walmart_ProductType>
     */
    public function retrieveListWithKeyNick($marketplaceId)
    {
        $result = array();
        foreach ($this->retrieveByMarketplaceId($marketplaceId) as $item) {
            $result[$item->getData(Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType::COLUMN_NICK)] = $item;
        }

        return $result;
    }

    /**
     * @param int $marketplaceId
     * @return Ess_M2ePro_Model_Walmart_ProductType[]
     */
    public function retrieveByMarketplaceId($marketplaceId)
    {
        $collection = $this->productTypeCollectionFactory->create();
        $collection->getSelect()->join(
            array('ptd' => $this->productTypeDictionaryResource->getMainTable()),
            sprintf(
                'main_table.%s = ptd.%s',
                Ess_M2ePro_Model_Resource_Walmart_ProductType::COLUMN_DICTIONARY_PRODUCT_TYPE_ID,
                Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType::COLUMN_ID
            ),
            Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType::COLUMN_NICK
        );
        $collection->addFieldToFilter(
            Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType::COLUMN_MARKETPLACE_ID,
            $marketplaceId
        );

        return array_values($collection->getItems());
    }

    /**
     * @param string $title
     * @param int $marketplaceId
     * @param int|null $productTypeId
     * @return Ess_M2ePro_Model_Walmart_ProductType|null
     */
    public function findByTitleMarketplace(
        $title,
        $marketplaceId,
        $productTypeId
    ) {
        $collection = $this->productTypeCollectionFactory->create();
        $collection->getSelect()->joinInner(
            array('dictionary' => $this->productTypeDictionaryResource->getMainTable()),
            sprintf(
                'dictionary.id = %s',
                Ess_M2ePro_Model_Resource_Walmart_ProductType::COLUMN_DICTIONARY_PRODUCT_TYPE_ID
            ),
            array(Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType::COLUMN_MARKETPLACE_ID)
        );

        $collection->addFieldToFilter(
            sprintf('main_table.%s', Ess_M2ePro_Model_Resource_Walmart_ProductType::COLUMN_TITLE),
            array('eq' => $title)
        );
        $collection->addFieldToFilter('dictionary.marketplace_id', array('eq' => $marketplaceId));
        if ($productTypeId !== null) {
            $collection->addFieldToFilter('main_table.id', array('neq' => $productTypeId));
        }

        /** @var Ess_M2ePro_Model_Walmart_ProductType $result */
        $result = $collection->getFirstItem();
        if ($result->isObjectNew()) {
            return null;
        }

        return $result;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_ProductType|null
     */
    public function findByDictionary(
        Ess_M2ePro_Model_Walmart_Dictionary_ProductType $dictionaryProductType
    ) {
        $collection = $this->productTypeCollectionFactory->create();
        $collection->addFieldToFilter(
            Ess_M2ePro_Model_Resource_Walmart_ProductType::COLUMN_DICTIONARY_PRODUCT_TYPE_ID,
            array('eq' => $dictionaryProductType->getId())
        );

        /** @var Ess_M2ePro_Model_Walmart_ProductType $result */
        $result = $collection->getFirstItem();
        if ($result->isObjectNew()) {
            return null;
        }

        return $result;
    }

    /**
     * @param int $marketplaceId
     * @param string $nick
     * @return Ess_M2ePro_Model_Walmart_ProductType|null
     */
    public function findByMarketplaceIdAndNick($marketplaceId, $nick)
    {
        $collection = $this->productTypeCollectionFactory->create();
        $collection->getSelect()->joinInner(
            array('dictionary' => $this->productTypeDictionaryResource->getMainTable()),
            sprintf(
                'dictionary.id = %s',
                Ess_M2ePro_Model_Resource_Walmart_ProductType::COLUMN_DICTIONARY_PRODUCT_TYPE_ID
            ),
            array('marketplace_id' => 'marketplace_id')
        );

        $collection->addFieldToFilter(
            sprintf('dictionary.%s', Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType::COLUMN_NICK),
            array('eq' => $nick)
        );
        $collection->addFieldToFilter(
            sprintf('dictionary.%s', Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType::COLUMN_MARKETPLACE_ID),
            array('eq' => $marketplaceId)
        );

        /** @var Ess_M2ePro_Model_Walmart_ProductType $productType */
        $productType = $collection->getFirstItem();
        if ($productType->isObjectNew()) {
            return null;
        }

        return $productType;
    }

    // ----------------------------------------

    /**
     * @param Ess_M2ePro_Model_Walmart_ProductType $productType
     * @return void
     */
    private function addToRuntimeCache(Ess_M2ePro_Model_Walmart_ProductType $productType)
    {
        $this->runtimeCache[(int)$productType->getId()] = $productType;
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
     * @return Ess_M2ePro_Model_Walmart_ProductType|null
     */
    private function tryGetFromRuntimeCache($id)
    {
        return !empty($this->runtimeCache[$id]) ? $this->runtimeCache[$id] : null;
    }
}