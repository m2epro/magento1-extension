<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m01_WalmartAuthentication extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('walmart_account')
                         ->addColumn('client_id', 'VARCHAR(100)', 'NULL', 'consumer_id', false, false)
                         ->addColumn('client_secret', 'TEXT', 'NULL', 'client_id', false, false)
                         ->dropColumn('private_key', false, false)
                         ->commit();
    }

    //########################################
}
