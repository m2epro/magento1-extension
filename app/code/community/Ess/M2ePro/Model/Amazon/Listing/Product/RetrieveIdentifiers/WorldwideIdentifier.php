<?php

class Ess_M2ePro_Model_Amazon_Listing_Product_RetrieveIdentifiers_WorldwideIdentifier
    extends Ess_M2ePro_Model_Amazon_Listing_Product_RetrieveIdentifiers_AbstractIdentifier
{
    /**
     * @return bool
     */
    public function isUPC()
    {
        return Mage::helper('M2ePro')->isUPC($this->identifier);
    }

    /**
     * @return bool
     */
    public function isEAN()
    {
        return Mage::helper('M2ePro')->isEAN($this->identifier);
    }

    /**
     * @return bool
     */
    public function hasResolvedType()
    {
        return $this->isUPC() || $this->isEAN();
    }
}
