<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m07_WalmartKeywordsFields extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('walmart_template_description')
            ->changeColumn('keywords_custom_value', 'VARCHAR(4000)', 'NULL', null, false)
            ->changeColumn('keywords_custom_attribute', 'VARCHAR(4000)', 'NULL', null, false)
            ->commit();
    }

    //########################################
}
