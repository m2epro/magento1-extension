<?php

class Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping_Manager
{
    /** @var Ess_M2ePro_Model_Amazon_ProductType_AttributeMappingFactory */
    private $attributeMappingFactory;
    /** @var Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping_Repository */
    private $mappingRepository;

    /** @var list<int, Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping[]> */
    private $establishedMappings = array();
    /** @var list<int, Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping[]> */
    private $modifiedMappings = array();
    private $filled = array();

    public function __construct()
    {
        $this->attributeMappingFactory = Mage::getModel('M2ePro/Amazon_ProductType_AttributeMappingFactory');
        $this->mappingRepository = Mage::getModel('M2ePro/Amazon_ProductType_AttributeMapping_Repository');
    }

    public function hasModified(Ess_M2ePro_Model_Amazon_Template_ProductType $productType)
    {
        $modified = $this->retrieveModifiedMappings($productType);

        return count($modified) > 0;
    }

    /**
     * @return void
     */
    public function create(Ess_M2ePro_Model_Amazon_Template_ProductType $productType)
    {
        $established = $this->retrieveEstablishedMappings($productType);
        foreach ($established as $mapping) {
            $this->mappingRepository->create($mapping);
        }
    }

    /**
     * @return void
     */
    public function update(Ess_M2ePro_Model_Amazon_Template_ProductType $productType)
    {
        $modified = $this->retrieveModifiedMappings($productType);
        foreach ($modified as $mapping) {
            $this->mappingRepository->update($mapping);
        }
    }

    /**
     * @param int $id
     * @param string $magentoCode
     * @return void
     */
    public function updateMagentoAttributeCode($id, $magentoCode)
    {
        $attributeMapping = $this->mappingRepository->find($id);
        if ($attributeMapping === null) {
            return;
        }

        $attributeMapping->setMagentoAttributeCode($magentoCode);
        $this->mappingRepository->update($attributeMapping);
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping[]
     */
    private function retrieveEstablishedMappings(Ess_M2ePro_Model_Amazon_Template_ProductType $productType)
    {
        $this->fill($productType);

        return !empty($this->establishedMappings[$productType->getId()])
            ? $this->establishedMappings[$productType->getId()]
            : array();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping[]
     */
    private function retrieveModifiedMappings(Ess_M2ePro_Model_Amazon_Template_ProductType $productType)
    {
        $this->fill($productType);

        return !empty($this->modifiedMappings[$productType->getId()])
            ? $this->modifiedMappings[$productType->getId()]
            : array();
    }

    /**
     * @return void
     */
    private function fill(Ess_M2ePro_Model_Amazon_Template_ProductType $productType)
    {
        if (in_array($productType->getId(), $this->filled)) {
            return;
        }

        $mappingsList = $this->getMappingsList($productType);

        foreach ($productType->getCustomAttributesList() as $customAttribute) {
            $productTypeAttributeCode = $customAttribute['name'];
            $magentoAttributeCode = $customAttribute['attribute_code'];

            if (array_key_exists($productTypeAttributeCode, $mappingsList)) {
                $mapping = $mappingsList[$productTypeAttributeCode];
                if ($mapping->getMagentoAttributeCode() !== $magentoAttributeCode) {
                    $mapping->setMagentoAttributeCode($magentoAttributeCode);
                    $this->modifiedMappings[$productType->getId()][] = $mapping;
                }

                continue;
            }

            $productTypeAttributeName = $productType->getDictionary()
                                                    ->findNameByProductTypeCode(
                                                        $productTypeAttributeCode
                                                    );
            $this->establishedMappings[$productType->getId()][] = $this->attributeMappingFactory->create(
                $productTypeAttributeCode,
                $productTypeAttributeName,
                $magentoAttributeCode
            );
        }

        $this->filled[] = $productType->getId();
    }

    /**
     * @return list<int, Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping>
     */
    private function getMappingsList(Ess_M2ePro_Model_Amazon_Template_ProductType $productType)
    {
        $customProductTypeAttributes = array();
        foreach ($productType->getCustomAttributesList() as $attribute) {
            if (isset($attribute['name'])) {
                $customProductTypeAttributes[] = $attribute['name'];
            }
        }
        if (empty($customProductTypeAttributes)) {
            return array();
        }

        $result = array();
        $items = $this->mappingRepository->getItemsByAttributeCode($customProductTypeAttributes);
        foreach ($items as $item) {
            $result[$item->getProductTypeAttributeCode()] = $item;
        }

        return $result;
    }
}
