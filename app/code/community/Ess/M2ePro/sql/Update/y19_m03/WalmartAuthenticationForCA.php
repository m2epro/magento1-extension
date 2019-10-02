<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m03_WalmartAuthenticationForCA extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function getBackupTables()
    {
        return array(
            'walmart_account'
        );
    }

    public function execute()
    {
        $this->_installer->getTableModifier('walmart_account')
                         ->addColumn('old_private_key', 'TEXT', 'NULL', 'consumer_id', false, false)
                         ->commit();
    }

    //########################################
}
