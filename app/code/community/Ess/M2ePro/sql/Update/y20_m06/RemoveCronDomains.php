<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m06_RemoveCronDomains extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getConnection()->delete(
            $this->_installer->getFullTableName('config'),
            "`group` = '/cron/service/' AND `key` LIKE 'hostname_%'"
        );
    }

    //########################################
}
