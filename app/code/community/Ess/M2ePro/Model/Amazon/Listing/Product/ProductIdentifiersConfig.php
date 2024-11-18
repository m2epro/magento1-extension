<?php

class Ess_M2ePro_Model_Amazon_Listing_Product_ProductIdentifiersConfig
{
    /** @var Ess_M2ePro_Helper_Component_Amazon_Configuration */
    private $amazonConfig;

    public function __construct()
    {
        $this->amazonConfig = Mage::helper('M2ePro/Component_Amazon_Configuration');
    }

    /**
     * @return string|null
     */
    public function findGeneralIdAttribute(Ess_M2ePro_Model_Amazon_Listing $amazonListing)
    {
        if ($listingGeneralIdAttribute = $amazonListing->getGeneralIdAttribute()) {
            return $listingGeneralIdAttribute;
        }

        if ($this->amazonConfig->isGeneralIdModeCustomAttribute()) {
            return $this->amazonConfig->getGeneralIdCustomAttribute();
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function findWorldWideIdAttribute(Ess_M2ePro_Model_Amazon_Listing $amazonListing)
    {
        if ($listingWorldWideIdAttribute = $amazonListing->getWorldWideIdAttribute()) {
            return $listingWorldWideIdAttribute;
        }

        if ($this->amazonConfig->isWorldWideIdModeCustomAttribute()) {
            return $this->amazonConfig->getWorldwideCustomAttribute();
        }

        return null;
    }
}