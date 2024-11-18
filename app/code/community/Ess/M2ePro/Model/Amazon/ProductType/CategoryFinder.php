<?php

class Ess_M2ePro_Model_Amazon_ProductType_CategoryFinder
{
    const CACHE_LIFE_TIME = 3600;

    /** @var Ess_M2ePro_Model_Amazon_Connector_ProductType_SearchByCriteria_Processor */
    private $connectProcessor;

    /** @var Ess_M2ePro_Model_Amazon_ProductType_CategoryFinder_Repository  */
    private $repository;

    /** @var Ess_M2ePro_Helper_Data_Cache_Permanent */
    private $cachePermanent;
    /** @var Ess_M2ePro_Helper_Data */
    private $helperData;

    public function __construct() {
        $this->helperData = Mage::helper('M2ePro/Data');
        $this->cachePermanent = Mage::helper('M2ePro/Data_Cache_Permanent');
        $this->repository = Mage::getModel('M2ePro/Amazon_ProductType_CategoryFinder_Repository');
        $this->connectProcessor = Mage::getModel('M2ePro/Amazon_Connector_ProductType_SearchByCriteria_Processor');
    }

    /**
     * @param int $marketplaceId
     * @param string[] $criteria
     *
     * @return Ess_M2ePro_Model_Amazon_ProductType_CategoryFinder_Category[]
     */
    public function find($marketplaceId, array $criteria)
    {
        $marketplace = Mage::getModel('M2ePro/Marketplace');
        $marketplace->load($marketplaceId);

        if (!$marketplace->getId()) {
            throw new Ess_M2ePro_Model_Exception_Logic('Invalid marketplace id');
        }

        $marketplaceNativeId = $marketplace->getNativeId();

        $cachedCategories = $this->getCategoriesFromCache($marketplaceNativeId, $criteria);

        if ($cachedCategories === false) {
            $response = $this->fetchCategoriesFromServer($marketplaceNativeId, $criteria);
            $this->setCategoriesToCache($marketplaceNativeId, $criteria, $response);
        } else {
            $response = $this->buildResponseFromCachedData($cachedCategories);
        }

        $availableProductTypes = $this->getAvailableProductTypes($marketplaceId, $response);

        $categories = array();
        foreach ($response->getCategories() as $category) {
            $categoryItem = new Ess_M2ePro_Model_Amazon_ProductType_CategoryFinder_Category(
                $category['name'],
                $category['isLeaf']
            );

            foreach ($category['nicksOfProductTypes'] as $productTypeNick) {
                if (isset($availableProductTypes[$productTypeNick])) {
                    $productType = new Ess_M2ePro_Model_Amazon_ProductType_CategoryFinder_ProductType(
                        $availableProductTypes[$productTypeNick]['title'],
                        $availableProductTypes[$productTypeNick]['nick'],
                        $availableProductTypes[$productTypeNick]['templateId']
                    );
                    $categoryItem->addProductType($productType);
                }
            }

            $categories[] = $categoryItem;
        }

        return $categories;
    }

    /**
     * @param int $marketplaceId
     * @param Ess_M2ePro_Model_Amazon_Connector_ProductType_SearchByCriteria_Response $response
     *
     * @return array<string, array{nick:string, title:string, templateId: int|null}>
     */
    private function getAvailableProductTypes(
        $marketplaceId,
        Ess_M2ePro_Model_Amazon_Connector_ProductType_SearchByCriteria_Response $response
    ) {
        $nicks = array();
        foreach ($response->getCategories() as $category) {
            foreach ($category['nicksOfProductTypes'] as $productTypeNick) {
                $nicks[] = $productTypeNick;
            }
        }

        return $this->repository->getAvailableProductTypes(
            $marketplaceId,
            array_unique($nicks)
        );
    }

    private function fetchCategoriesFromServer($marketplaceNativeId, array $criteria)
    {
        $request = new Ess_M2ePro_Model_Amazon_Connector_ProductType_SearchByCriteria_Request(
            $marketplaceNativeId,
            $criteria
        );

        return $this->connectProcessor->process($request);
    }

    private function buildResponseFromCachedData(array $cachedCategories)
    {
        $response = new Ess_M2ePro_Model_Amazon_Connector_ProductType_SearchByCriteria_Response();

        foreach ($cachedCategories as $category) {
            $response->addCategory(
                $category['name'],
                $category['isLeaf'],
                $category['nicksOfProductTypes']
            );
        }

        return $response;
    }

    private function getCategoriesFromCache($marketplaceId, array $criteria)
    {
        $cacheKey = $this->getCacheKey($marketplaceId, $criteria);
        $cachedCategories = $this->cachePermanent->getValue($cacheKey);

        if ($cachedCategories !== false) {
            return Zend_Json::decode($cachedCategories);
        }

        return false;
    }

    private function getCacheKey($marketplaceId, array $criteria)
    {
        return $marketplaceId . '_' . $this->helperData->md5String(Zend_Json::encode($criteria));
    }

    private function setCategoriesToCache(
        $marketplaceNativeId,
        array $criteria,
        Ess_M2ePro_Model_Amazon_Connector_ProductType_SearchByCriteria_Response $categories
    ) {
        $cacheKey = $this->getCacheKey($marketplaceNativeId, $criteria);

        $this->cachePermanent->setValue(
            $cacheKey,
            Zend_Json::encode($categories->getCategories()),
            array(),
            self::CACHE_LIFE_TIME
        );
    }
}