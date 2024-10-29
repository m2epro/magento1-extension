<?php

class Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Response_Part
{
    /** @var int */
    private $totalParts;
    /** @var int|null */
    private $nextPartNumber = null;

    /**
     * @param int $totalParts
     * @param int|null $nextPartNumber
     */
    public function __construct($totalParts, $nextPartNumber)
    {
        $this->totalParts = $totalParts;
        $this->nextPartNumber = $nextPartNumber;
    }

    /**
     * @return int
     */
    public function getTotalParts()
    {
        return $this->totalParts;
    }

    /**
     * @return int|null
     */
    public function getNextPartNumber()
    {
        return $this->nextPartNumber;
    }
}
