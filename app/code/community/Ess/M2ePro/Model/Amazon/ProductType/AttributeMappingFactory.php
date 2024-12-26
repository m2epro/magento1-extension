<?php

class Ess_M2ePro_Model_Amazon_ProductType_AttributeMappingFactory
{
    /**
     * @param string $productTypeAttributeCode
     * @param string $magentoAttributeCode
     * @param string $productTypeAttributeName
     * @return Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping
     */
    public function create(
        $productTypeAttributeCode,
        $productTypeAttributeName,
        $magentoAttributeCode
    ) {
        return $this->createEmpty()
                    ->create(
                        $productTypeAttributeCode,
                        $productTypeAttributeName,
                        $magentoAttributeCode
                    );
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping
     */
    public function createEmpty()
    {
        /** @var Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping */
        return Mage::getModel('M2ePro/Amazon_ProductType_AttributeMapping');
    }
}
