<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m10_SynchronizationConfigsModeRemoved
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function getBackupTables()
    {
        return array(
            'config',
        );
    }

    public function execute()
    {
        $this->_installer->getMainConfigModifier()->delete('/cron/checker/task/repair_crashed_tables/');

        $this->_installer->run(<<<SQL
DELETE FROM `m2epro_config`
WHERE `key` IN ('last_access', 'last_run', 'interval', 'mode') AND 
      `group` LIKE '/cron/task/%' AND `group` NOT IN ('/cron/task/system/servicing/synchronize/');
SQL
        );
    }

    //########################################
}
