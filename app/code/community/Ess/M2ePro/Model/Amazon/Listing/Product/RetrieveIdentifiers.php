<?php

class Ess_M2ePro_Model_Amazon_Listing_Product_RetrieveIdentifiers
{
    /** @var Ess_M2ePro_Model_Amazon_Listing_Product_ProductIdentifiersConfig */
    private $config;

    public function __construct()
    {
        $this->config = Mage::getModel('M2ePro/Amazon_Listing_Product_ProductIdentifiersConfig');
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_RetrieveIdentifiers_Identifiers
     */
    public function process(
        Ess_M2ePro_Model_Amazon_Listing $amazonListing,
        Ess_M2ePro_Model_Magento_Product $magentoProduct
    ) {
        $identifiers = new Ess_M2ePro_Model_Amazon_Listing_Product_RetrieveIdentifiers_Identifiers();
        if ($generalId = $this->findGeneralId($amazonListing, $magentoProduct)) {
            $identifiers->setGeneralId($generalId);
        }

        if ($worldWideId = $this->findWorldWideId($amazonListing, $magentoProduct)) {
            $identifiers->setWorldwideId($worldWideId);
        }

        return $identifiers;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_RetrieveIdentifiers_GeneralIdentifier|null
     */
    private function findGeneralId(
        Ess_M2ePro_Model_Amazon_Listing $amazonListing,
        Ess_M2ePro_Model_Magento_Product $magentoProduct
    ) {
        $attribute = $this->config->findGeneralIdAttribute($amazonListing);
        if ($attribute === null) {
            return null;
        }

        $attributeValue = $this->getAttributeValue($attribute, $magentoProduct);
        if ($attributeValue === null) {
            return null;
        }

        return new Ess_M2ePro_Model_Amazon_Listing_Product_RetrieveIdentifiers_GeneralIdentifier(
            $attributeValue
        );
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_RetrieveIdentifiers_WorldwideIdentifier|null
     */
    private function findWorldWideId(
        Ess_M2ePro_Model_Amazon_Listing $amazonListing,
        Ess_M2ePro_Model_Magento_Product $magentoProduct
    ) {
        $attribute = $this->config->findWorldwideIdAttribute($amazonListing);
        if ($attribute === null) {
            return null;
        }

        $attributeValue = $this->getAttributeValue($attribute, $magentoProduct);
        if ($attributeValue === null) {
            return null;
        }

        return new Ess_M2ePro_Model_Amazon_Listing_Product_RetrieveIdentifiers_WorldwideIdentifier(
            $attributeValue
        );
    }

    /**
     * @param string $attributeCode
     * @return string|null
     */
    private function getAttributeValue(
        $attributeCode,
        Ess_M2ePro_Model_Magento_Product $magentoProduct
    ) {
        $value = $magentoProduct->getAttributeValue($attributeCode);
        $value = trim(str_replace('-', '', $value));

        return $value === '' ? null : $value;
    }
}