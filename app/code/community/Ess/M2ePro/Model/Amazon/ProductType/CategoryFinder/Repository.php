<?php

class Ess_M2ePro_Model_Amazon_ProductType_CategoryFinder_Repository
{
    /** @var Ess_M2ePro_Model_Amazon_Dictionary_Marketplace_Repository */
    private $dictionaryMarketplaceRepository;
    /** @var Ess_M2ePro_Model_Amazon_Template_ProductType_Repository */
    private $templateProductTypeRepository;

    public function __construct()
    {
        $this->dictionaryMarketplaceRepository = Mage::getModel('M2ePro/Amazon_Dictionary_Marketplace_Repository');
        $this->templateProductTypeRepository = Mage::getModel('M2ePro/Amazon_Template_ProductType_Repository');
    }

    /**
     * @param int $marketplaceId
     * @param array $nicks
     *
     * @return array<string, array{nick:string, title:string, templateId: int|null}>
     */
    public function getAvailableProductTypes($marketplaceId, array $nicks)
    {
        $productTypesMap = $this->getProductTypesFromMarketplaceDictionary($marketplaceId);
        $alreadyUsedProductTypesMap = $this->getProductTypesTemplates($marketplaceId);

        $availableProductTypes = array();
        foreach ($nicks as $nick) {
            if (isset($productTypesMap[$nick])) {
                $templateId = null;
                if (isset($alreadyUsedProductTypesMap[$productTypesMap[$nick]['nick']])) {
                    $templateId = $alreadyUsedProductTypesMap[$productTypesMap[$nick]['nick']];
                }
                $availableProductTypes[$nick] = array(
                    'nick' => $productTypesMap[$nick]['nick'],
                    'title' => $productTypesMap[$nick]['title'],
                    'templateId' => $templateId,
                );
            }
        }

        return $availableProductTypes;
    }

    private function getProductTypesFromMarketplaceDictionary($marketplaceId)
    {
        $dictionary = $this->dictionaryMarketplaceRepository->findByMarketplaceId($marketplaceId);
        if ($dictionary === null) {
            return array();
        }

        $productTypes = $dictionary->getProductTypes();

        $result = array();
        foreach ($productTypes as $productType) {
            $result[$productType['nick']] = array(
                'nick' => $productType['nick'],
                'title' => $productType['title'],
            );
        }

        return $result;
    }

    private function getProductTypesTemplates($marketplaceId)
    {
        $result = array();
        foreach ($this->templateProductTypeRepository->findByMarketplaceId($marketplaceId) as $productType) {
            $result[$productType->getNick()] = (int)$productType->getId();
        }

        return $result;
    }
}