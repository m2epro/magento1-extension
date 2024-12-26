<?php

class Ess_M2ePro_Model_Amazon_ProductType_AttributeMappingService
{
    /** @var Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping_Suggester */
    private $suggester;
    /** @var Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping_Manager */
    private $manager;

    public function __construct()
    {
        $this->suggester = Mage::getModel('M2ePro/Amazon_ProductType_AttributeMapping_Suggester');
        $this->manager = Mage::getModel('M2ePro/Amazon_ProductType_AttributeMapping_Manager');
    }

    /**
     * @return list<string, array{mode: int, attribute_code: string}>
     */
    public function getSuggestedAttributes()
    {
        return $this->suggester->getSuggestedAttributes();
    }

    //----------------------------------

    public function hasChangedMappings(Ess_M2ePro_Model_Amazon_Template_ProductType $productType)
    {
        return $this->manager->hasModified($productType);
    }

    /**
     * @return void
     */
    public function create(Ess_M2ePro_Model_Amazon_Template_ProductType $productType)
    {
        $this->manager->create($productType);
    }

    /**
     * @return void
     */
    public function update(Ess_M2ePro_Model_Amazon_Template_ProductType $productType)
    {
       $this->manager->update($productType);
    }

    //----------------------------------

    /**
     * @param int $id
     * @param string $magentoCode
     * @return void
     */
    public function updateMagentoAttributeCode($id, $magentoCode)
    {
        $this->manager->updateMagentoAttributeCode($id, $magentoCode);
    }
}
