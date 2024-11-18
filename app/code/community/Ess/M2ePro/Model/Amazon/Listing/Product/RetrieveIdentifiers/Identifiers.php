<?php

class Ess_M2ePro_Model_Amazon_Listing_Product_RetrieveIdentifiers_Identifiers
{
    /** @var Ess_M2ePro_Model_Amazon_Listing_Product_RetrieveIdentifiers_GeneralIdentifier|null */
    private $generalId = null;
    /** @var Ess_M2ePro_Model_Amazon_Listing_Product_RetrieveIdentifiers_WorldwideIdentifier|null */
    private $worldwideId = null;

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_RetrieveIdentifiers_GeneralIdentifier|null
     */
    public function getGeneralId()
    {
        return $this->generalId;
    }

    /**
     * @return void
     */
    public function setGeneralId(
        Ess_M2ePro_Model_Amazon_Listing_Product_RetrieveIdentifiers_GeneralIdentifier $generalId
    ) {
        $this->generalId = $generalId;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_RetrieveIdentifiers_WorldwideIdentifier|null
     */
    public function getWorldwideId()
    {
        return $this->worldwideId;
    }

    public function setWorldwideId(
        Ess_M2ePro_Model_Amazon_Listing_Product_RetrieveIdentifiers_WorldwideIdentifier $worldwideId
    ) {
        $this->worldwideId = $worldwideId;
    }
}
