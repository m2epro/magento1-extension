<?php

class Ess_M2ePro_Model_Walmart_Dictionary_CategoryService
{
    /** @var Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Processor */
    private $getCategoriesConnector;
    /** @var Ess_M2ePro_Model_Walmart_Dictionary_CategoryFactory */
    private $categoryDictionaryFactory;
    /** @var Ess_M2ePro_Model_Walmart_Dictionary_Category_Repository */
    private $categoryDictionaryRepository;

    public function __construct()
    {
        $this->getCategoriesConnector = Mage::getModel('M2ePro/Walmart_Connector_Marketplace_GetCategories_Processor');
        $this->categoryDictionaryFactory = Mage::getModel('M2ePro/Walmart_Dictionary_CategoryFactory');
        $this->categoryDictionaryRepository = Mage::getModel('M2ePro/Walmart_Dictionary_Category_Repository');
    }

    /**
     * @return void
     */
    public function update(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        if (!$marketplace->isComponentModeWalmart()) {
            throw new \LogicException('Marketplace is not Walmart component mode.');
        }

        $this->categoryDictionaryRepository->removeByMarketplace(
            (int)$marketplace->getId()
        );

        $part = $this->processPart(1, $marketplace);
        if ($part->getNextPartNumber() === null) {
            return;
        }

        $totalParts = $part->getTotalParts();
        for ($i = 2; $i <= $totalParts; $i++) {
            $part = $this->processPart($part->getNextPartNumber(), $marketplace);
            if ($part->getNextPartNumber() === null) {
                break;
            }
        }
    }

    /**
     * @param int $partNumber
     * @return Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Response_Part
     */
    private function processPart(
        $partNumber,
        Ess_M2ePro_Model_Marketplace $marketplace
    ) {
        $response = $this->getCategoriesConnector->process(
            $marketplace,
            $partNumber
        );

        $this->createCategoryDictionaries((int)$marketplace->getId(), $response);

        return $response->getPart();
    }

    /**
     * @param int $marketplaceId
     * @return void
     */
    private function createCategoryDictionaries(
        $marketplaceId,
        Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Response $response
    ) {
        $categoryDictionaryEntities = array();
        foreach ($response->getCategories() as $responseCategory) {
            $categoryDictionaryEntities[] = $this->getCategoryDictionary(
                $marketplaceId,
                $responseCategory
            );
        }

        $this->categoryDictionaryRepository->bulkCreate($categoryDictionaryEntities);
    }

    /**
     * @param int $marketplaceId
     * @return Ess_M2ePro_Model_Walmart_Dictionary_Category
     */
    private function getCategoryDictionary(
        $marketplaceId,
        Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Response_Category $responseCategory
    ) {
        if ($responseCategory->getParentId() === null) {
            return $this->categoryDictionaryFactory->createAsRoot(
                $marketplaceId,
                $responseCategory->getId(),
                $responseCategory->getTitle()
            );
        }

        if (!$responseCategory->isLeaf()) {
            return $this->categoryDictionaryFactory->createAsChild(
                $marketplaceId,
                $responseCategory->getParentId(),
                $responseCategory->getId(),
                $responseCategory->getTitle()
            );
        }

        $responseProductType = $responseCategory->getProductType();
        return $this->categoryDictionaryFactory->createAsLeaf(
            $marketplaceId,
            $responseCategory->getParentId(),
            $responseCategory->getId(),
            $responseCategory->getTitle(),
            $responseProductType->getNick(),
            $responseProductType->getTitle()
        );
    }
}
