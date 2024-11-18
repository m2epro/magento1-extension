<?php

class Ess_M2ePro_Model_Amazon_Listing_Product_RetrieveIdentifiers_GeneralIdentifier
    extends Ess_M2ePro_Model_Amazon_Listing_Product_RetrieveIdentifiers_AbstractIdentifier
{
    /**
     * @return bool
     */
    public function isASIN()
    {
        return Mage::helper('M2ePro/Component_Amazon')->isASIN($this->identifier);
    }

    /**
     * @return bool
     */
    public function isISBN()
    {
        return Mage::helper('M2ePro')->isISBN($this->identifier);
    }

    /**
     * @return bool
     */
    public function hasResolvedType()
    {
        return $this->isASIN() || $this->isISBN();
    }
}
