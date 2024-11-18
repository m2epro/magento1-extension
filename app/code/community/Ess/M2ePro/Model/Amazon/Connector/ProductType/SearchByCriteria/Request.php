<?php

class Ess_M2ePro_Model_Amazon_Connector_ProductType_SearchByCriteria_Request
{
    /** @var int */
    private $marketplaceId;
    /** @var string[] */
    private $criteria;

    /**
     * @param int $marketplaceId
     * @param string[] $criteria
     */
    public function __construct($marketplaceId, array $criteria)
    {
        $this->marketplaceId = $marketplaceId;
        $this->criteria = $criteria;
    }

    public function getMarketplaceId()
    {
        return $this->marketplaceId;
    }

    /**
     * @return string[]
     */
    public function getCriteria()
    {
        return $this->criteria;
    }
}