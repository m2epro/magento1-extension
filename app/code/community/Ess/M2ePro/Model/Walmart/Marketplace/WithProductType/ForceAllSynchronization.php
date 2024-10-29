<?php

class Ess_M2ePro_Model_Walmart_Marketplace_WithProductType_ForceAllSynchronization
{
    /** @var Ess_M2ePro_Model_Walmart_Marketplace_Repository */
    private $marketplaceRepository;
    /** @var Ess_M2ePro_Model_Walmart_Dictionary_MarketplaceService */
    private $marketplaceDictionaryService;
    /** @var Ess_M2ePro_Model_Walmart_Dictionary_CategoryService */
    private $categoryDictionaryService;

    public function __construct()
    {
        $this->marketplaceRepository = Mage::getModel('M2ePro/Walmart_Marketplace_Repository');;
        $this->categoryDictionaryService = Mage::getModel('M2ePro/Walmart_Dictionary_CategoryService');
        $this->marketplaceDictionaryService = Mage::getModel('M2ePro/Walmart_Dictionary_MarketplaceService');
    }

    /**
     * @return void
     */
    public function process()
    {
        foreach ($this->marketplaceRepository->findActive() as $marketplace) {
            if (
                !$marketplace->getChildObject()
                             ->isSupportedProductType()
            ) {
                continue;
            }

            $this->marketplaceDictionaryService->update($marketplace);
            $this->categoryDictionaryService->update($marketplace);
        }
    }
}