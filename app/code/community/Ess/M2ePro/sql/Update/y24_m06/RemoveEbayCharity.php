<?php

class Ess_M2ePro_Sql_Update_y24_m06_RemoveEbayCharity
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    /**
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Exception
     */
    public function execute()
    {
        $this->_installer->getTableModifier('ebay_template_selling_format')
            ->dropColumn('charity')
            ->commit();

        $this->_installer->getTableModifier('ebay_marketplace')
            ->dropColumn('is_charity')
            ->commit();

        $this->_installer->getTableModifier('ebay_dictionary_marketplace')
            ->dropColumn('charities')
            ->commit();
    }
}
