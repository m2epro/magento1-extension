<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m03_ChangeMinSupportedVersion extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $tableName = $this->_installer->getFullTableName('migration_v6');
        $this->_installer->run("DROP TABLE IF EXISTS `{$tableName}`");
    }

    //########################################
}
