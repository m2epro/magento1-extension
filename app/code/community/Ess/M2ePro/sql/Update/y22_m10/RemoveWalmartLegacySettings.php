<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m10_RemoveWalmartLegacySettings extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    /**
     * @return void
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Db_Statement_Exception
     */
    public function execute()
    {
        $this->removeMinimumAdvertisedPrice();
        $this->removeTaxCodes();
        $this->removeKeywords();
    }

    /**
     * @return void
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Db_Statement_Exception
     */
    private function removeMinimumAdvertisedPrice()
    {
        $this->_installer
            ->getTableModifier('walmart_template_selling_format')
            ->dropColumn('map_price_mode', true, false)
            ->dropColumn('map_price_custom_attribute', true, false)
            ->commit();
    }

    /**
     * @return void
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Db_Statement_Exception
     */
    private function removeTaxCodes()
    {
        $this->_installer->getTableModifier('m2epro_walmart_dictionary_marketplace')
            ->dropColumn('tax_codes', true, false)
            ->commit();

        $this->_installer->getTableModifier('walmart_template_selling_format')
            ->dropColumn('product_tax_code_mode', true, false)
            ->dropColumn('product_tax_code_custom_value', true, false)
            ->dropColumn('product_tax_code_custom_attribute', true, false)
            ->commit();
    }

    /**
     * @return void
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Db_Statement_Exception
     */
    private function removeKeywords()
    {
        $this->_installer->getTableModifier('walmart_template_description')
            ->dropColumn('keywords_mode', true, false)
            ->dropColumn('keywords_custom_value', true, false)
            ->dropColumn('keywords_custom_attribute', true, false)
            ->commit();
    }
}
