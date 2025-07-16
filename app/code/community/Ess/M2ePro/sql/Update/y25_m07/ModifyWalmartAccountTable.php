<?php

class Ess_M2ePro_Sql_Update_y25_m07_ModifyWalmartAccountTable extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $modifier = $this->_installer->getTableModifier('walmart_account');
        $modifier->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_Account::COLUMN_IDENTIFIER,
            'VARCHAR(100) NOT NULL',
            '',
            Ess_M2ePro_Model_Resource_Walmart_Account::COLUMN_MARKETPLACE_ID,
            false,
            false
        );

        $modifier->commit();

        $modifier->dropColumn('consumer_id');
        $modifier->dropColumn('private_key');
        $modifier->dropColumn('client_id');
        $modifier->dropColumn('client_secret');
    }
}
