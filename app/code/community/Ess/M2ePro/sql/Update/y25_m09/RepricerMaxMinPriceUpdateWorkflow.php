<?php

class Ess_M2ePro_Sql_Update_y25_m09_RepricerMaxMinPriceUpdateWorkflow extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $modifier = $this->_installer->getTableModifier('amazon_account_repricing');

        $modifier->addColumn(
            'min_price_value_attribute',
            'VARCHAR(255)',
            'NULL',
            'min_price_variation_mode',
            false,
            false
        );
        $modifier->addColumn(
            'min_price_percent_attribute',
            'VARCHAR(255)',
            'NULL',
            'min_price_value_attribute',
            false,
            false
        );
        $modifier->addColumn(
            'max_price_value_attribute',
            'VARCHAR(255)',
            'NULL',
            'max_price_variation_mode',
            false,
            false
        );
        $modifier->addColumn(
            'max_price_percent_attribute',
            'VARCHAR(255)',
            'NULL',
            'max_price_value_attribute',
            false,
            false
        );

        $modifier->commit();
    }
}
