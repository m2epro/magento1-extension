<?php

class Ess_M2ePro_Model_Amazon_Dictionary_ProductTypeService
{
    /** @var Ess_M2ePro_Model_Amazon_Connector_Dispatcher */
    private $dispatcher;
    /** @var Ess_M2ePro_Model_Amazon_Marketplace_Repository */
    private $amazonMarketplaceRepository;
    /** @var Ess_M2ePro_Model_Amazon_Dictionary_ProductType_Repository */
    private $dictionaryProductTypeRepository;
    /** @var Ess_M2ePro_Model_Amazon_Marketplace_Issue_ProductTypeOutOfDate_Cache */
    private $issueOutOfDateCache;
    /** @var Ess_M2ePro_Model_Amazon_Dictionary_ProductTypeFactory */
    private $productTypeFactory;

    public function __construct()
    {
        $this->dispatcher = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
        $this->amazonMarketplaceRepository = Mage::getModel('M2ePro/Amazon_Marketplace_Repository');
        $this->dictionaryProductTypeRepository = Mage::getModel('M2ePro/Amazon_Dictionary_ProductType_Repository');
        $this->issueOutOfDateCache = Mage::getModel('M2ePro/Amazon_Marketplace_Issue_ProductTypeOutOfDate_Cache');
        $this->productTypeFactory = Mage::getModel('M2ePro/Amazon_Dictionary_ProductTypeFactory');
    }

    /**
     * @param string $nick
     * @return Ess_M2ePro_Model_Amazon_Dictionary_ProductType
     */
    public function retrieve(
        $nick,
        Ess_M2ePro_Model_Marketplace $marketplace
    ) {
        if (!$marketplace->isComponentModeAmazon()) {
            throw new \LogicException('Marketplace is not Amazon component mode.');
        }

        $productType = $this->dictionaryProductTypeRepository->findByMarketplaceAndNick(
            (int)$marketplace->getId(),
            $nick
        );
        if ($productType !== null) {
            return $productType;
        }

        $data = $this->getData($nick, $marketplace);

        $productType = $this->productTypeFactory->create(
            $marketplace,
            $nick,
            $data['title'],
            $data['attributes'],
            $data['variation_themes'],
            $data['attributes_groups'],
            Mage::helper('M2ePro')->createGmtDateTime($data['last_update']),
            Mage::helper('M2ePro')->createCurrentGmtDateTime()
        );

        $this->dictionaryProductTypeRepository->create($productType);

        return $productType;
    }

    /**
     * @return void
     */
    public function update(Ess_M2ePro_Model_Amazon_Dictionary_ProductType $productType)
    {
        $marketplace = $this->amazonMarketplaceRepository->get($productType->getMarketplaceId());

        $data = $this->getData($productType->getNick(), $marketplace);

        $productType->setVariationThemes($data['variation_themes'])
            ->setScheme($data['attributes'])
            ->setAttributesGroups($data['attributes_groups'])
            ->setServerDetailsLastUpdateDate(Mage::helper('M2ePro')->createGmtDateTime($data['last_update']))
            ->setClientDetailsLastUpdateDate(Mage::helper('M2ePro')->createCurrentGmtDateTime());

        $this->dictionaryProductTypeRepository->save($productType);

        $this->clearCache();
    }

    private function getData($nick, Ess_M2ePro_Model_Marketplace $marketplace)
    {
        /** @var Ess_M2ePro_Model_Amazon_Connector_ProductType_Get_Info $command */
        $command = $this->dispatcher->getConnectorByClass(
            'Ess_M2ePro_Model_Amazon_Connector_ProductType_Get_Info',
            array(
                'product_type_nick' => $nick,
                'marketplace_id' => $marketplace->getNativeId()
            )
        );

        $this->dispatcher->process($command);

        return $command->getResponseData();
    }

    private function clearCache()
    {
        $this->issueOutOfDateCache->clear();
    }
}