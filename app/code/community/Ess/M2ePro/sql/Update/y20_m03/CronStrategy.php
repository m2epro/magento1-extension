<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m03_CronStrategy extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getMainConfigModifier()->insert('/cron/', 'last_executed_task_group', null);
    }

    //########################################
}
