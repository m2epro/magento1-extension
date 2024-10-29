<?php

class Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Response
{
    /** @var Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Response_Category[] */
    private $categories;
    /** @var Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Response_Part */
    private $part;
    /** @var int|null */
    private $nextPartNumber = null;

    public function __construct(
        array $categories,
        Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Response_Part $part
    ) {
        $this->categories = $categories;
        $this->part = $part;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Response_Category[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @return int|null
     */
    public function getNextPartNumber()
    {
        return $this->nextPartNumber;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Response_Part
     */
    public function getPart()
    {
        return $this->part;
    }
}
