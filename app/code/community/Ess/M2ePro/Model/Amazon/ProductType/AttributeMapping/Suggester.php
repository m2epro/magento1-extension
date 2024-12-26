<?php

class Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping_Suggester
{
    /** @var Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping_Repository */
    private $mappingRepository;

    public function __construct()
    {
        $this->mappingRepository = Mage::getModel('M2ePro/Amazon_ProductType_AttributeMapping_Repository');
    }

    /**
     * @return list<string, array{mode: int, attribute_code: string}>
     */
    public function getSuggestedAttributes()
    {
        $attributes = $this->appendAttributeMapSuggestedAttributes();

        return $this->appendDefaultSuggestedAttributes($attributes);
    }

    /**
     * @return list<string, array{mode: int, attribute_code: string}>
     */
    private function appendAttributeMapSuggestedAttributes()
    {
        $attributes = array();

        $items = $this->mappingRepository->getAllItems();
        foreach ($items as $item) {
            $productTypeAttribute = $item->getProductTypeAttributeCode();
            $magentoAttributeCode = $item->getMagentoAttributeCode();

            if (
                array_key_exists($productTypeAttribute, $attributes)
                || $magentoAttributeCode === ''
            ) {
                continue;
            }

            $attributes[$productTypeAttribute] = array(
                'mode' => Ess_M2ePro_Model_Amazon_Template_ProductType::FIELD_CUSTOM_ATTRIBUTE,
                'attribute_code' => $magentoAttributeCode,
            );
        }

        return $attributes;
    }

    /**
     * @return list<string, array{mode: int, attribute_code: string}>
     */
    private function appendDefaultSuggestedAttributes(array $attributes)
    {
        $map = array(
            Ess_M2ePro_Helper_Component_Amazon_ProductType::SPECIFIC_KEY_NAME => 'name',
            Ess_M2ePro_Helper_Component_Amazon_ProductType::SPECIFIC_KEY_BRAND => 'manufacturer',
            Ess_M2ePro_Helper_Component_Amazon_ProductType::SPECIFIC_KEY_MANUFACTURER => 'manufacturer',
            Ess_M2ePro_Helper_Component_Amazon_ProductType::SPECIFIC_KEY_DESCRIPTION => 'description',
            Ess_M2ePro_Helper_Component_Amazon_ProductType::SPECIFIC_KEY_COUNTRY_OF_ORIGIN => 'country_of_manufacture',
            Ess_M2ePro_Helper_Component_Amazon_ProductType::SPECIFIC_KEY_ITEM_PACKAGE_WEIGHT => 'weight',
            Ess_M2ePro_Helper_Component_Amazon_ProductType::SPECIFIC_KEY_MAIN_PRODUCT_IMAGE_LOCATOR => 'image',
        );

        foreach ($map as $productTypeAttributeCode => $magentoAttributeCode) {
            if (array_key_exists($productTypeAttributeCode, $attributes)) {
                continue;
            }

            $attributes[$productTypeAttributeCode] = array(
                'mode' => Ess_M2ePro_Model_Amazon_Template_ProductType::FIELD_CUSTOM_ATTRIBUTE,
                'attribute_code' => $magentoAttributeCode,
            );
        }

        return $attributes;
    }
}
