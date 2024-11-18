<?php

class Ess_M2ePro_Model_Amazon_Marketplace_Sync_MarketplaceLoader
{
    public function load($marketplaceId)
    {
        if ($marketplaceId === null) {
            throw new \RuntimeException('Missing marketplace ID');
        }

        /** @var Ess_M2ePro_Model_Amazon_Marketplace_Repository $amazonMarketplaceRepository */
        $amazonMarketplaceRepository = Mage::getModel('M2ePro/Amazon_Marketplace_Repository');
        $marketplace = $amazonMarketplaceRepository->get((int)$marketplaceId);
        if (!$marketplace->isComponentModeAmazon()) {
            throw new \LogicException('Marketplace is not valid.');
        }

        return $marketplace;
    }
}