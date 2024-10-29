<?php

class Ess_M2ePro_Model_Walmart_Dictionary_MarketplaceService
{
    /** @var Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetInfoWithDetails_Processor */
    private $getInfoWithDetailsConnector;
    /** @var Ess_M2ePro_Model_Walmart_Dictionary_MarketplaceFactory */
    private $dictionaryMarketplaceFactory;
    /** @var Ess_M2ePro_Model_Walmart_Dictionary_Marketplace_Repository */
    private $dictionaryMarketplaceRepository;
    /** @var Ess_M2ePro_Model_Walmart_Dictionary_ProductType_Repository */
    private $dictionaryProductTypeRepository;
    /** @var Ess_M2ePro_Model_Walmart_ProductType_Repository */
    private $productTypeRepository;

    public function __construct()
    {
        $this->getInfoWithDetailsConnector = Mage::getModel(
            'M2ePro/Walmart_Connector_Marketplace_GetInfoWithDetails_Processor'
        );
        $this->dictionaryMarketplaceFactory = Mage::getModel('M2ePro/Walmart_Dictionary_MarketplaceFactory');
        $this->dictionaryMarketplaceRepository = Mage::getModel('M2ePro/Walmart_Dictionary_Marketplace_Repository');
        $this->dictionaryProductTypeRepository = Mage::getModel('M2ePro/Walmart_Dictionary_ProductType_Repository');
        $this->productTypeRepository = Mage::getModel('M2ePro/Walmart_ProductType_Repository');
    }

    /**
     * @return void
     */
    public function update(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        if (!$marketplace->isComponentModeWalmart()) {
            throw new \LogicException('Marketplace is not Walmart component mode.');
        }

        $response = $this->getInfoWithDetailsConnector->process($marketplace);
        $this->dictionaryMarketplaceRepository->removeByMarketplace(
            (int)$marketplace->getId()
        );

        $marketplaceDictionary = $this->dictionaryMarketplaceFactory->createWithProductTypes(
            (int)$marketplace->getId(),
            $response->getProductTypes(),
            $response->getLastUpdate(),
            $response->getLastUpdate()
        );

        $this->dictionaryMarketplaceRepository->create($marketplaceDictionary);

        $this->processRemovedProductTypes(
            $marketplace,
            $response->getProductTypesNicks()
        );

        $this->restoreInvalidProductTypes(
            $marketplace,
            $response->getProductTypesNicks()
        );
    }

    /**
     * @return void
     */
    private function processRemovedProductTypes(
        Ess_M2ePro_Model_Marketplace $marketplace,
        array $productTypesNicks
    ) {
        $dictionaryProductTypes = $this->dictionaryProductTypeRepository->retrieveByMarketplace($marketplace);
        foreach ($dictionaryProductTypes as $dictionaryProductType) {
            if (in_array($dictionaryProductType->getNick(), $productTypesNicks)) {
                continue;
            }

            $template = $this->productTypeRepository->findByDictionary($dictionaryProductType);
            if ($template === null) {
                $this->dictionaryProductTypeRepository->remove($dictionaryProductType);

                continue;
            }

            $dictionaryProductType->markAsInvalid();

            $this->dictionaryProductTypeRepository->save($dictionaryProductType);
        }
    }

    /**
     * @return void
     */
    private function restoreInvalidProductTypes(
        Ess_M2ePro_Model_Marketplace $marketplace,
        array $productTypesNicks
    ) {
        $dictionaryProductTypes = $this->dictionaryProductTypeRepository->retrieveByMarketplace($marketplace);
        foreach ($dictionaryProductTypes as $productType) {
            if (!$productType->isInvalid()) {
                continue;
            }

            if (!in_array($productType->getNick(), $productTypesNicks)) {
                continue;
            }

            $productType->markAsValid();
            $this->dictionaryProductTypeRepository->save($productType);
        }
    }
}
