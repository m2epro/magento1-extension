<?php

class Ess_M2ePro_Model_Walmart_ProductType_AttributeSetting_Provider
{
    /**
     * @return Ess_M2ePro_Model_Walmart_ProductType_AttributeSetting_Provider_Item[]
     */
    public function getAttributes(
        Ess_M2ePro_Model_Walmart_ProductType $productType,
        Ess_M2ePro_Model_Magento_Product $product
    ) {
        $attributes = array();
        foreach ($productType->getAttributesSettings() as $setting) {
            $resultValues = $this->getResultValues($setting, $product);
            if (empty($resultValues)) {
                continue;
            }

            $attributes[] = new Ess_M2ePro_Model_Walmart_ProductType_AttributeSetting_Provider_Item(
                $setting->getAttributeName(),
                $resultValues
            );
        }

        return $attributes;
    }

    /**
     * @return string[]
     */
    private function getResultValues(
        Ess_M2ePro_Model_Walmart_ProductType_AttributeSetting $attributeSetting,
        Ess_M2ePro_Model_Magento_Product $product
    ) {
        $result = array();
        /** @var \Ess_M2ePro_Helper_Module_Renderer_Description $descriptionRender */
        $descriptionRender = Mage::helper('M2ePro/Module_Renderer_Description');
        foreach ($attributeSetting->getValues() as $settingValue) {
            if ($settingValue->isCustom()) {
                $result[] = $descriptionRender->parseWithoutMagentoTemplate(
                    $settingValue->getValue(),
                    $product
                );

                continue;
            }

            if ($settingValue->isProductAttributeCode()) {
                $attributeValue = $product->getAttributeValue(
                    $settingValue->getValue()
                );
                if (!empty($attributeValue)) {
                    $result[] = $attributeValue;
                }
            }
        }

        return $result;
    }
}
