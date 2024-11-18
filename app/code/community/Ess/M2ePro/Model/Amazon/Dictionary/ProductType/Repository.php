<?php

class Ess_M2ePro_Model_Amazon_Dictionary_ProductType_Repository
{
    /** @var Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType */
    private $resource;
    private $runtimeCache = array();

    public function __construct()
    {
        $this->resource = Mage::getResourceModel('M2ePro/Amazon_Dictionary_ProductType');
    }

    /**
     * @param Ess_M2ePro_Model_Amazon_Dictionary_ProductType $productType
     * @return void
     */
    public function create(Ess_M2ePro_Model_Amazon_Dictionary_ProductType $productType)
    {
        $this->resource->save($productType);
    }

    /**
     * @param int $id
     *
     * @return Ess_M2ePro_Model_Amazon_Dictionary_ProductType
     */
    public function get($id)
    {
        $productType = $this->find($id);
        if ($productType === null) {
            throw new \LogicException("Product Type $id not found.");
        }

        return $productType;
    }

    /**
     * @param int $id
     * @return Ess_M2ePro_Model_Amazon_Dictionary_ProductType|null
     */
    public function find($id)
    {
        if (($model = $this->tryGetFromRuntimeCache($id)) !== null) {
            return $model;
        }

        /** @var Ess_M2ePro_Model_Amazon_Dictionary_ProductType $model */
        $model = Mage::getModel('M2ePro/Amazon_Dictionary_ProductType');
        $this->resource->load($model, $id);
        if ($model->isObjectNew()) {
            return null;
        }

        $this->addToRuntimeCache($model);

        return $model;
    }

    /**
     * @param Ess_M2ePro_Model_Amazon_Dictionary_ProductType $productType
     * @return void
     */
    public function save(Ess_M2ePro_Model_Amazon_Dictionary_ProductType $productType)
    {
        $this->resource->save($productType);
    }

    /**
     * @return void
     */
    public function remove(Ess_M2ePro_Model_Amazon_Dictionary_ProductType$productType)
    {
        $this->removeFromRuntimeCache((int)$productType->getId());

        $this->resource->delete($productType);
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Dictionary_ProductType[]
     */
    public function findByMarketplace(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $collection = Mage::getResourceModel('M2ePro/Amazon_Dictionary_ProductType_Collection');
        $collection->addFieldToFilter(
            Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_MARKETPLACE_ID,
            array('eq' => $marketplace->getId())
        );

        return array_values($collection->getItems());
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Dictionary_ProductType[]
     */
    public function findValidByMarketplace(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $collection = Mage::getResourceModel('M2ePro/Amazon_Dictionary_ProductType_Collection');
        $collection->addFieldToFilter(
            Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_MARKETPLACE_ID,
            array('eq' => $marketplace->getId()))
        ;
        $collection->addFieldToFilter(
            Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_INVALID, array('eq' => 0)
        );

        return array_values($collection->getItems());
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Dictionary_ProductType[]
     */
    public function findValidOutOfDate()
    {
        $collection = Mage::getResourceModel('M2ePro/Amazon_Dictionary_ProductType_Collection');
        $collection->addFieldToFilter(
            Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_INVALID,
            array('eq' => 0)
        );
        $collection->addFieldToFilter(
            Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_SERVER_DETAILS_LAST_UPDATE_DATE,
            array('gt' => new \Zend_Db_Expr(
                Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_CLIENT_DETAILS_LAST_UPDATE_DATE)
            )
        );
        $collection->addFieldToFilter(
            Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_CLIENT_DETAILS_LAST_UPDATE_DATE,
            array('notnull' => true)
        );
        $collection->addFieldToFilter(
            Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_SERVER_DETAILS_LAST_UPDATE_DATE,
            array('notnull' => true)
        );

        return array_values($collection->getItems());
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Dictionary_ProductType|null
     */
    public function findByMarketplaceAndNick(
        $marketplaceId,
        $nick
    ) {
        $collection = Mage::getResourceModel('M2ePro/Amazon_Dictionary_ProductType_Collection');
        $collection->addFieldToFilter(
            Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_MARKETPLACE_ID,
            array('eq' => $marketplaceId)
        )
            ->addFieldToFilter(
                Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_NICK,
                array('eq' => $nick)
            );

        $result = $collection->getFirstItem();
        if ($result->isObjectNew()) {
            return null;
        }

        return $result;
    }

    // ----------------------------------------

    /**
     * @return list<int, string[]>
     */
    public function getValidNickMapByMarketplaceNativeId()
    {
        $marketplaceCollection = Mage::getResourceModel('M2ePro/Marketplace_Collection');
        $marketplaceCollection->getSelect()
            ->joinInner(
                array('dictionary' => $this->resource->getMainTable()),
                sprintf(
                    'dictionary.%s = main_table.%s',
                    Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_MARKETPLACE_ID,
                    Ess_M2ePro_Model_Resource_Marketplace::COLUMN_ID
                ),
                array()
            );

        $marketplaceCollection->addFieldToFilter(
            sprintf('dictionary.%s', Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_INVALID),
            array('eq' => 0)
        );

        $marketplaceCollection->getSelect()->reset('columns');
        $marketplaceCollection->getSelect()->columns(
            array(
                'native_id' => sprintf(
                    'main_table.%s',
                    Ess_M2ePro_Model_Resource_Marketplace::COLUMN_NATIVE_ID)
                ,
                'nick' => sprintf(
                    'dictionary.%s',
                    Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_NICK
                ),
            )
        );

        $resultMap = array();
        $marketplaceCollectionToArray = $marketplaceCollection->toArray();

        if (isset($marketplaceCollectionToArray['items'])) {
            foreach ($marketplaceCollectionToArray['items'] as $row) {
                $resultMap[(int)$row['native_id']][] = $row['nick'];
            }
        }

        return $resultMap;
    }

    // ----------------------------------------

    /**
     * @return void
     */
    private function addToRuntimeCache(Ess_M2ePro_Model_Amazon_Dictionary_ProductType $productType)
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
     * @return Ess_M2ePro_Model_Amazon_Dictionary_ProductType|null
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