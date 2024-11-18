<?php

class Ess_M2ePro_Model_Amazon_Dictionary_MarketplaceService
{
    /** @var Ess_M2ePro_Model_Amazon_Dictionary_Marketplace_Repository */
    private $dictionaryMarketplaceRepository;
    /** @var Ess_M2ePro_Model_Amazon_Dictionary_ProductType_Repository */
    private $dictionaryProductTypeRepository;
    /** @var Ess_M2ePro_Model_Amazon_Template_ProductType_Repository */
    private $templateProductTypeRepository;

    public function __construct()
    {
        $this->dictionaryMarketplaceRepository = Mage::getModel('M2ePro/Amazon_Dictionary_Marketplace_Repository');
        $this->dictionaryProductTypeRepository = Mage::getModel('M2ePro/Amazon_Dictionary_ProductType_Repository');
        $this->templateProductTypeRepository = Mage::getModel('M2ePro/Amazon_Template_ProductType_Repository');
    }

    /**
     * @return bool
     */
    public function isExistForMarketplace(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        return $this->dictionaryMarketplaceRepository->findByMarketplace($marketplace) !== null;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Dictionary_Marketplace
     */
    public function update(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $dispatcher = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
        /** @var Ess_M2ePro_Model_Amazon_Connector_Marketplace_Get_InfoWithDetails $command */
        $command = $dispatcher->getConnector(
            'marketplace',
            'get',
            'infoWithDetails',
            array('marketplace_id' => $marketplace->getNativeId())
        );

        $dispatcher->process($command);

        $response = $command->getResponseData();

        $dictionaryResult = $this->makeDictionary($marketplace, $response['info']);
        $this->processRemovedProductTypes($marketplace, $dictionaryResult['list']);
        $this->restoreInvalidProductTypes($marketplace, $dictionaryResult['list']);

        return $dictionaryResult['dictionary'];
    }

    /**
     * @return array{dictionary: Ess_M2ePro_Model_Amazon_Dictionary_Marketplace, list: string[]}
     */
    private function makeDictionary(Ess_M2ePro_Model_Marketplace $marketplace, array $info)
    {
        $collectionProductTypes = $this->collectProductTypes($info['details']['product_type']);

        /** @var Ess_M2ePro_Model_Amazon_Dictionary_Marketplace $dictionary */
        $dictionary = Mage::getModel('M2ePro/Amazon_Dictionary_Marketplace');
        $dictionary->setData(
            Ess_M2ePro_Model_Resource_Amazon_Dictionary_Marketplace::COLUMN_MARKETPLACE_ID,
            $marketplace->getId()
        );
        $dictionary->setData(
            Ess_M2ePro_Model_Resource_Amazon_Dictionary_Marketplace::COLUMN_PRODUCT_TYPES,
            json_encode($collectionProductTypes['prepared'])
        );

        $this->dictionaryMarketplaceRepository->removeByMarketplace($marketplace);
        $this->dictionaryMarketplaceRepository->create($dictionary);

        return array(
            'dictionary' => $dictionary,
            'list' => $collectionProductTypes['list']
        );
    }

    /**
     * @return array{prepared: list<array{nick: string, title: string}>, list: string[]}
     */
    private function collectProductTypes(array $productTypeList)
    {
        $prepared = array();
        $list = array();
        foreach ($productTypeList as $row) {
            if (!isset($row['nick']) || !isset($row['title'])) {
                continue;
            }
            $prepared[] = array(
                'nick' => $row['nick'],
                'title' => $row['title']
            );

            $list[] = $row['nick'];
        }

        return array(
            'prepared' => $prepared,
            'list' => $list
        );
    }

    /**
     * @param string[] $listProductTypesNicks
     *
     * @return void
     */
    private function processRemovedProductTypes(
        Ess_M2ePro_Model_Marketplace $marketplace,
        array $listProductTypesNicks
    ) {
        $existProductTypesMap = array_flip($listProductTypesNicks);
        foreach ($this->dictionaryProductTypeRepository->findByMarketplace($marketplace) as $productType) {
            if (isset($existProductTypesMap[$productType->getNick()])) {
                continue;
            }

            $templates = $this->templateProductTypeRepository->findByDictionary($productType);
            if (empty($templates)) {
                $this->dictionaryProductTypeRepository->remove($productType);

                continue;
            }

            $productType->markAsInvalid();

            $this->dictionaryProductTypeRepository->save($productType);
        }
    }

    /**
     * @param string[] $listProductTypesNicks
     *
     * @return void
     */
    private function restoreInvalidProductTypes(
        Ess_M2ePro_Model_Marketplace $marketplace,
        array $listProductTypesNicks
    ) {
        $existProductTypesMap = array_flip($listProductTypesNicks);
        foreach ($this->dictionaryProductTypeRepository->findByMarketplace($marketplace) as $productType) {
            if (!$productType->isInvalid()) {
                continue;
            }

            if (!isset($existProductTypesMap[$productType->getNick()])) {
                continue;
            }

            $productType->markAsValid();

            $this->dictionaryProductTypeRepository->save($productType);
        }
    }
}