<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y18_m09_GlobalNotifications extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer
            ->getMainConfigModifier()
            ->insert('/cron/task/magento/global_notifications/', 'mode', '1', '0 - disable, \r\n1 - enable');

        $this->_installer
            ->getMainConfigModifier()
            ->insert('/cron/task/magento/global_notifications/', 'interval', '86400', 'in seconds');
    }

    //########################################
}
