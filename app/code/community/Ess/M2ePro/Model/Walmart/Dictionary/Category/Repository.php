<?php

use Ess_M2ePro_Model_Resource_Walmart_Dictionary_Category as ResourceModel;

class Ess_M2ePro_Model_Walmart_Dictionary_Category_Repository
{
    /** @var Ess_M2ePro_Model_Resource_Walmart_Dictionary_Category */
    private $categoryDictionaryResource;
    /** @var Ess_M2ePro_Model_Resource_Walmart_Dictionary_Category_CollectionFactory */
    private $collectionFactory;

    public function __construct()
    {
        $this->categoryDictionaryResource = Mage::getResourceModel('M2ePro/Walmart_Dictionary_Category');
        $this->collectionFactory = Mage::getResourceModel('M2ePro/Walmart_Dictionary_Category_CollectionFactory');
    }

    /**
     * @param Ess_M2ePro_Model_Walmart_Dictionary_Category[] $categories
     * @return void
     */
    public function bulkCreate(array $categories)
    {
        $insertData = array();
        foreach ($categories as $category) {
            $parentCategoryId = $category->isExistsParentCategoryId() ? $category->getParentCategoryId() : null;
            $productTypeNick = $category->isLeaf() ? $category->getProductTypeNick() : null;
            $productTypeTitle = $category->isLeaf() ? $category->getProductTypeTitle() : null;

            $insertData[] = array(
                ResourceModel::COLUMN_MARKETPLACE_ID => $category->getMarketplaceId(),
                ResourceModel::COLUMN_CATEGORY_ID => $category->getCategoryId(),
                ResourceModel::COLUMN_PARENT_CATEGORY_ID => $parentCategoryId,
                ResourceModel::COLUMN_TITLE => $category->getTitle(),
                ResourceModel::COLUMN_IS_LEAF => $category->isLeaf(),
                ResourceModel::COLUMN_PRODUCT_TYPE_NICK => $productTypeNick,
                ResourceModel::COLUMN_PRODUCT_TYPE_TITLE => $productTypeTitle,
            );
        }

        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        /** @var Varien_Db_Adapter_Pdo_Mysql $connWrite */
        $connWrite = $resource->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);

        foreach (array_chunk($insertData, 1000) as $chunk) {
            $connWrite->insertMultiple(
                $this->categoryDictionaryResource->getMainTable(),
                $chunk
            );
        }
    }

    /**
     * @param int $marketplaceId
     * @return Ess_M2ePro_Model_Walmart_Dictionary_Category[]
     */
    public function findRoots($marketplaceId)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ResourceModel::COLUMN_MARKETPLACE_ID, $marketplaceId);
        $collection->addFieldToFilter(ResourceModel::COLUMN_PARENT_CATEGORY_ID, array('null' => true));

        return array_values($collection->getItems());
    }

    /**
     * @param int $parentCategoryId
     * @return Ess_M2ePro_Model_Walmart_Dictionary_Category[]
     */
    public function findChildren($parentCategoryId)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ResourceModel::COLUMN_PARENT_CATEGORY_ID, $parentCategoryId);

        return array_values($collection->getItems());
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
            $this->categoryDictionaryResource->getMainTable(),
            array(ResourceModel::COLUMN_MARKETPLACE_ID . ' = ?' => $marketplaceId)
        );
    }
}
