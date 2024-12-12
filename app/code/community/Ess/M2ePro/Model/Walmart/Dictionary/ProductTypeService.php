<?php

class Ess_M2ePro_Model_Walmart_Dictionary_ProductTypeService
{
    /** @var Ess_M2ePro_Model_Walmart_Dictionary_ProductType_Repository */
    private $productTypeDictionaryRepository;
    /** @var Ess_M2ePro_Model_Walmart_Connector_ProductType_GetInfo_Processor */
    private $getInfoConnectProcessor;
    /** @var Ess_M2ePro_Model_Walmart_Dictionary_ProductTypeFactory */
    private $productTypeDictionaryFactory;

    public function __construct()
    {
        $this->productTypeDictionaryRepository = Mage::getModel('M2ePro/Walmart_Dictionary_ProductType_Repository');
        $this->getInfoConnectProcessor = Mage::getModel('M2ePro/Walmart_Connector_ProductType_GetInfo_Processor');
        $this->productTypeDictionaryFactory = Mage::getModel('M2ePro/Walmart_Dictionary_ProductTypeFactory');
    }

    /**
     * @param string $productTypeNick
     * @return Ess_M2ePro_Model_Walmart_Dictionary_ProductType
     */
    public function retrieve($productTypeNick, Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $this->checkMarketplace($marketplace);

        $productTypeDictionary = $this->productTypeDictionaryRepository->findByNick(
            $productTypeNick,
            (int)$marketplace->getId()
        );

        if ($productTypeDictionary !== null) {
            return $productTypeDictionary;
        }

        $response = $this->getInfoConnectProcessor->process(
            $productTypeNick,
            $marketplace
        );

        $productTypeDictionary = $this->productTypeDictionaryFactory->create(
            (int)$marketplace->getId(),
            $productTypeNick,
            $response->getTitle(),
            $response->getAttributes(),
            $response->getVariationAttributes()
        );
        $this->productTypeDictionaryRepository->create($productTypeDictionary);

        return $productTypeDictionary;
    }

    /**
     * @return void
     */
    public function update(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $this->checkMarketplace($marketplace);

        $productTypeDictionaries = $this->productTypeDictionaryRepository
            ->retrieveByMarketplace($marketplace);

        foreach ($productTypeDictionaries as $productTypeDictionary) {
            if ($productTypeDictionary->isInvalid()) {
                continue;
            }
            $response = $this->getInfoConnectProcessor->process(
                $productTypeDictionary->getNick(),
                $marketplace
            );

            $productTypeDictionary->setAttributes($response->getAttributes())
                                  ->setVariationAttributes($response->getVariationAttributes());

            $this->productTypeDictionaryRepository->save($productTypeDictionary);
        }
    }

    private function checkMarketplace(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        if (!$marketplace->isComponentModeWalmart()) {
            throw new \LogicException('Marketplace is not Walmart component mode.');
        }
    }
}
