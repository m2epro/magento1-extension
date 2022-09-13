<?php

class Ess_M2ePro_Sql_Update_y22_m08_AddIsReplacementColumnToAmazonOrder extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->_installer->getTableModifier('amazon_order')
             ->addColumn('is_replacement', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'is_business');
    }
}
