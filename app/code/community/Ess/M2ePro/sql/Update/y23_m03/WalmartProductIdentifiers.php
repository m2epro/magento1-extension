<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y23_m03_WalmartProductIdentifiers extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $moduleConfigModifier = $this->_installer->getMainConfigModifier();

        $usedAttribute = $this->getUsedAttribute();

        if (empty($usedAttribute)) {
            $moduleConfigModifier->insert('/walmart/configuration/', 'product_id_mode', '0');
            $moduleConfigModifier->insert('/walmart/configuration/', 'product_id_custom_attribute', '');
        } else {
            $moduleConfigModifier->insert('/walmart/configuration/', 'product_id_mode', '1');
            $moduleConfigModifier->insert('/walmart/configuration/', 'product_id_custom_attribute', $usedAttribute);
        }

        $moduleConfigModifier->delete('/walmart/configuration/', 'upc_mode');
        $moduleConfigModifier->delete('/walmart/configuration/', 'upc_custom_attribute');
        $moduleConfigModifier->delete('/walmart/configuration/', 'ean_mode');
        $moduleConfigModifier->delete('/walmart/configuration/', 'ean_custom_attribute');
        $moduleConfigModifier->delete('/walmart/configuration/', 'gtin_mode');
        $moduleConfigModifier->delete('/walmart/configuration/', 'gtin_custom_attribute');
        $moduleConfigModifier->delete('/walmart/configuration/', 'isbn_mode');
        $moduleConfigModifier->delete('/walmart/configuration/', 'isbn_custom_attribute');
    }

    private function getUsedAttribute()
    {
        $moduleConfigModifier = $this->_installer->getMainConfigModifier();

        $identifierAttributeKeys = array(
            'gtin_custom_attribute',
            'upc_custom_attribute',
            'ean_custom_attribute',
            'isbn_custom_attribute'
        );

        foreach ($identifierAttributeKeys as $identifierAttributeKey) {
            $attr = $moduleConfigModifier->getEntity('/walmart/configuration/', $identifierAttributeKey)->getValue();

            if ($attr) {
                return $attr;
            }
        }

        return null;
    }
}
