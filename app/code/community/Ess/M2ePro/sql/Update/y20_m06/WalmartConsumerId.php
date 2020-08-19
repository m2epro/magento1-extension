<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m06_WalmartConsumerId extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('walmart_account')
            ->changeColumn('consumer_id', 'VARCHAR(255)', 'NULL', 'marketplace_id')
            ->renameColumn('old_private_key', 'private_key');
    }

    //########################################
}
