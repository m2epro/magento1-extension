<?php

class Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping_Repository
{
    /** @var Ess_M2ePro_Model_Amazon_ProductType_AttributeMappingFactory */
    private $attributeMappingFactory;
    /** @var Ess_M2ePro_Model_Resource_Amazon_ProductType_AttributeMapping */
    private $attributeMappingResource;
    /** @var Ess_M2ePro_Model_Resource_Amazon_ProductType_AttributeMapping_CollectionFactory */
    private $collectionFactory;

    public function __construct()
    {
        $this->attributeMappingFactory = Mage::getModel('M2ePro/Amazon_ProductType_AttributeMappingFactory');
        $this->attributeMappingResource = Mage::getResourceModel('M2ePro/Amazon_ProductType_AttributeMapping');
        $this->collectionFactory = Mage::getResourceModel(
            'M2ePro/Amazon_ProductType_AttributeMapping_CollectionFactory'
        );
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping|null
     */
    public function find($id)
    {
        $attributeMapping = $this->attributeMappingFactory->createEmpty();
        $this->attributeMappingResource->load($attributeMapping, $id);

        if ($attributeMapping->isEmpty()) {
            return null;
        }

        return $attributeMapping;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping[]
     */
    public function getAllItems()
    {
        $collection = $this->collectionFactory->create();

        return array_values($collection->getItems());
    }

    /**
     * @param string[] $attributeCodes
     * @return Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping[]
     */
    public function getItemsByAttributeCode(array $attributeCodes)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            'product_type_attribute_code',
            array('in' => $attributeCodes)
        );

        return array_values($collection->getItems());
    }

    /**
     * @return void
     */
    public function create(Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping $attributeMapping)
    {
        $this->attributeMappingResource->save($attributeMapping);
    }

    /**
     * @return void
     */
    public function update(Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping $attributeMapping)
    {
        $this->attributeMappingResource->save($attributeMapping);
    }
}
