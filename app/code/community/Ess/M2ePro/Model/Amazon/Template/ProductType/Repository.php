<?php

class Ess_M2ePro_Model_Amazon_Template_ProductType_Repository
{
    /** @var  Ess_M2ePro_Model_Resource_Amazon_Template_ProductType */
    private $resource;
    /** @var Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType */
    private $dictionaryProductTypeResource;

    private $runtimeCache = array();

    public function __construct()
    {
        $this->resource = Mage::getResourceModel('M2ePro/Amazon_Template_ProductType');
        $this->dictionaryProductTypeResource = Mage::getResourceModel('M2ePro/Amazon_Dictionary_ProductType');
    }

    /**
     * @return void
     */
    public function create(Ess_M2ePro_Model_Amazon_Template_ProductType $productType)
    {
        $this->resource->save($productType);
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_ProductType|null
     */
    public function find($id)
    {
        if (($model = $this->tryGetFromRuntimeCache($id)) !== null) {
            return $model;
        }

        /** @var Ess_M2ePro_Model_Amazon_Template_ProductType $model */
        $model = Mage::getModel('M2ePro/Amazon_Template_ProductType');
        $this->resource->load($model, $id);

        if ($model->isObjectNew()) {
            return null;
        }

        $this->addToRuntimeCache($model);

        return $model;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_ProductType
     */
    public function get($id)
    {
        $mode = $this->find($id);
        if ($mode === null) {
            throw new \Exception("Product Type template $id not found.");
        }

        return $mode;
    }

    /**
     * @return void
     */
    public function save(Ess_M2ePro_Model_Amazon_Template_ProductType $productType)
    {
        $this->resource->save($productType);
    }

    /**
     * @return void
     */
    public function remove(Ess_M2ePro_Model_Amazon_Template_ProductType $productType)
    {
        $this->removeFromRuntimeCache((int)$productType->getId());

        $this->resource->delete($productType);
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_ProductType|null
     */
    public function findByTitleMarketplace(
        $title,
        $marketplaceId,
        $productTypeId
    ) {
        $collection = Mage::getResourceModel('M2ePro/Amazon_Template_ProductType_Collection');
        $collection->getSelect()->joinInner(
            array('dictionary' => $this->dictionaryProductTypeResource->getMainTable()),
            sprintf(
                'dictionary.id = %s',
                Ess_M2ePro_Model_Resource_Amazon_Template_ProductType::COLUMN_DICTIONARY_PRODUCT_TYPE_ID
            ),
            array('marketplace_id' => 'marketplace_id')
        );

        $collection->addFieldToFilter(sprintf(
            'main_table.%s',
            Ess_M2ePro_Model_Resource_Amazon_Template_ProductType::COLUMN_TITLE
        ), array('eq' => $title));
        $collection->addFieldToFilter('dictionary.marketplace_id', array('eq' => $marketplaceId));
        if ($productTypeId !== null) {
            $collection->addFieldToFilter('main_table.id', array('neq' => $productTypeId));
        }

        $result = $collection->getFirstItem();
        if ($result->isObjectNew()) {
            return null;
        }

        return $result;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_ProductType|null
     */
    public function findByMarketplaceIdAndNick(
         $marketplaceId,
         $nick
    ) {
        $collection = Mage::getResourceModel('M2ePro/Amazon_Template_ProductType_Collection');
        $collection->getSelect()->joinInner(
            array('dictionary' => $this->dictionaryProductTypeResource->getMainTable()),
            sprintf(
                'dictionary.id = %s',
                Ess_M2ePro_Model_Resource_Amazon_Template_ProductType::COLUMN_DICTIONARY_PRODUCT_TYPE_ID
            ),
            array('marketplace_id' => 'marketplace_id')
        );

        $collection->addFieldToFilter(
            sprintf(
                'dictionary.%s',
                Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_NICK
            ),
            array('eq' => $nick)
        );
        $collection->addFieldToFilter(
            sprintf(
                'dictionary.%s',
                Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_MARKETPLACE_ID
            ),
            array('eq' => $marketplaceId)
        );

        $result = $collection->getFirstItem();
        if ($result->isObjectNew()) {
            return null;
        }

        return $result;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_ProductType[]
     */
    public function findByMarketplaceId($marketplaceId)
    {
        $collection = Mage::getResourceModel('M2ePro/Amazon_Template_ProductType_Collection');
        $collection->getSelect()->joinInner(
            array('dictionary' => $this->dictionaryProductTypeResource->getMainTable()),
            sprintf(
                'main_table.%s = dictionary.%s',
                Ess_M2ePro_Model_Resource_Amazon_Template_ProductType::COLUMN_DICTIONARY_PRODUCT_TYPE_ID,
                Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_ID
            ),
            array()
        );
        $collection->addFieldToFilter(
            sprintf(
                'dictionary.%s',
                Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_MARKETPLACE_ID
            ),
            array('eq' => $marketplaceId)
        );

        return array_values($collection->getItems());
    }

    /**
     * @param Ess_M2ePro_Model_Amazon_Dictionary_ProductType $dictionaryProductType
     *
     * @return Ess_M2ePro_Model_Amazon_Template_ProductType[]
     */
    public function findByDictionary(Ess_M2ePro_Model_Amazon_Dictionary_ProductType $dictionaryProductType)
    {
        $collection = Mage::getResourceModel('M2ePro/Amazon_Template_ProductType_Collection');
        $collection->addFieldToFilter(
            Ess_M2ePro_Model_Resource_Amazon_Template_ProductType::COLUMN_DICTIONARY_PRODUCT_TYPE_ID,
            array('eq' => $dictionaryProductType->getId())
        );

        return array_values($collection->getItems());
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Marketplace[]
     */
    public function getUsingMarketplaces()
    {
        $marketplaceCollection = Mage::getResourceModel('M2ePro/Marketplace');
        $marketplaceCollection->getSelect()
            ->joinInner(
                array('dictionary' => $this->dictionaryProductTypeResource->getMainTable()),
                sprintf(
                    'dictionary.%s = main_table.%s',
                    Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_MARKETPLACE_ID,
                    Ess_M2ePro_Model_Resource_Marketplace::COLUMN_ID
                ),
                array()
            );
        $marketplaceCollection->getSelect()
            ->joinInner(
                array('template' => $this->resource->getMainTable()),
                sprintf(
                    'template.%s = dictionary.%s',
                    Ess_M2ePro_Model_Resource_Amazon_Template_ProductType::COLUMN_DICTIONARY_PRODUCT_TYPE_ID,
                    Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_ID
                ),
                array()
            );

        $marketplaceCollection->getSelect()
            ->group(sprintf('main_table.%s', Ess_M2ePro_Model_Resource_Marketplace::COLUMN_ID));

        $marketplaceCollection->setOrder(
            sprintf('main_table.%s', Ess_M2ePro_Model_Resource_Marketplace::COLUMN_SORDER),
            'ASC'
        );

        return array_values($marketplaceCollection->getItems());
    }

    // ----------------------------------------

    /**
     * @param Ess_M2ePro_Model_Amazon_Template_ProductType $productType
     *
     * @return bool
     */
    public function isUsed(Ess_M2ePro_Model_Amazon_Template_ProductType $productType)
    {
        $productTypeId = (int)$productType->getId();

        return $this->isUsedInProducts($productTypeId)
            || $this->isUsedInListings($productTypeId)
            || $this->isUsedInAutoCategoryGroups($productTypeId);
    }

    private function isUsedInProducts($productTypeId)
    {
        $collection = Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Collection');

        $collection->getSelect()
            ->where(
                sprintf(
                    '%s = ?',
                    Ess_M2ePro_Model_Resource_Amazon_Listing_Product::COLUMN_TEMPLATE_PRODUCT_TYPE_ID
                ),
                $productTypeId
            )
            ->limit(1);

        $product = $collection->getFirstItem();

        return !$product->isObjectNew();
    }

    private function isUsedInListings($productTypeId)
    {
        $listingCollection = Mage::getResourceModel('M2ePro/Amazon_Listing_Collection');
        $listingCollection->getSelect()
            ->where(
                sprintf(
                    '%s = ? OR %s = ?',
                    Ess_M2ePro_Model_Resource_Amazon_Listing::COLUMN_AUTO_GLOBAL_ADDING_PRODUCT_TYPE_TEMPLATE_ID,
                    Ess_M2ePro_Model_Resource_Amazon_Listing::COLUMN_AUTO_WEBSITE_ADDING_PRODUCT_TYPE_TEMPLATE_ID
                              ),
                $productTypeId
            )
            ->limit(1);

        /** @var Ess_M2ePro_Model_Amazon_Listing $listing */
        $listing = $listingCollection->getFirstItem();

        return !$listing->isObjectNew();
    }

    private function isUsedInAutoCategoryGroups($productTypeId)
    {
        $autoCategoryGroupCollection = Mage::getResourceModel(
            'M2ePro/Amazon_Listing_Auto_Category_Group_Collection'
        );
        $autoCategoryGroupCollection->getSelect()
            ->where(
                sprintf(
                    '%s = ?',
                    Ess_M2ePro_Model_Resource_Amazon_Listing_Auto_Category_Group::
                                                                                  COLUMN_ADDING_PRODUCT_TYPE_TEMPLATE_ID
                ),
                $productTypeId
            )
            ->limit(1);

        /** @var Ess_M2ePro_Model_Amazon_Listing_Auto_Category_Group $autoCategoryGroup */
        $autoCategoryGroup = $autoCategoryGroupCollection->getFirstItem();

        return !$autoCategoryGroup->isObjectNew();
    }

    // ----------------------------------------

    private function addToRuntimeCache($productType)
    {
        $this->runtimeCache[(int)$productType->getId()] = $productType;
    }

    private function removeFromRuntimeCache($id)
    {
        unset($this->runtimeCache[$id]);
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_ProductType|null
     */
    private function tryGetFromRuntimeCache($id)
    {
        $result = null;
        if (isset($this->runtimeCache[$id])) {
            $result = $this->runtimeCache[$id];
        }

        return $result;
    }
}