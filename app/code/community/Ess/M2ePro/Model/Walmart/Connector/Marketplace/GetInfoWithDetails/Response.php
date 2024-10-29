<?php

class Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetInfoWithDetails_Response
{
    /** @var array */
    private $productTypes;
    /** @var array */
    private $productTypesNicks;
    /** @var DateTime */
    private $lastUpdate;

    public function __construct(
        array $productTypes,
        array $productTypesNicks,
        \DateTime $lastUpdate
    ) {
        $this->productTypes = $productTypes;
        $this->productTypesNicks = $productTypesNicks;
        $this->lastUpdate = $lastUpdate;
    }

    /**
     * @return list<array{nick: string, title: string}>
     */
    public function getProductTypes()
    {
        return $this->productTypes;
    }

    /**
     * @return string[]
     */
    public function getProductTypesNicks()
    {
        return $this->productTypesNicks;
    }

    /**
     * @return DateTime
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }
}