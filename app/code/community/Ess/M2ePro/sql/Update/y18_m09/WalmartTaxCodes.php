<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y18_m09_WalmartTaxCodes extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('walmart_dictionary_marketplace')
                         ->addColumn('tax_codes', 'LONGTEXT', null, 'product_data');
    }

    //########################################
}
