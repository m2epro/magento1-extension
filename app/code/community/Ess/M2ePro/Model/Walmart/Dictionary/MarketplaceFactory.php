<?php

class Ess_M2ePro_Model_Walmart_Dictionary_MarketplaceFactory
{
    /**
     * @param int $marketplaceId
     * @param array $productTypes
     * @param DateTime $clientDetailsLastUpdateDate
     * @param DateTime $serverDetailsLastUpdateDate
     * @return Ess_M2ePro_Model_Walmart_Dictionary_Marketplace
     */
    public function createWithProductTypes(
        $marketplaceId,
        $productTypes,
        \DateTime $clientDetailsLastUpdateDate,
        \DateTime $serverDetailsLastUpdateDate
    ) {
        $entity = $this->createEmpty();
        $entity->init(
            $marketplaceId,
            $clientDetailsLastUpdateDate,
            $serverDetailsLastUpdateDate
        );
        $entity->setProductTypes($productTypes);

        return $entity;
    }

    /**
     * @param int $marketplaceId
     * @param DateTime $clientDetailsLastUpdateDate
     * @param DateTime $serverDetailsLastUpdateDate
     * @return Ess_M2ePro_Model_Walmart_Dictionary_Marketplace
     */
    public function createWithoutProductTypes(
        $marketplaceId,
        \DateTime $clientDetailsLastUpdateDate,
        \DateTime $serverDetailsLastUpdateDate
    ) {
        $entity = $this->createEmpty();
        $entity->init(
            $marketplaceId,
            $clientDetailsLastUpdateDate,
            $serverDetailsLastUpdateDate
        );

        return $entity;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Dictionary_Marketplace
     */
    public function createEmpty()
    {
        /** @var Ess_M2ePro_Model_Walmart_Dictionary_Marketplace */
        return Mage::getModel('M2ePro/Walmart_Dictionary_Marketplace');
    }
}
