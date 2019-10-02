<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v5_0_7__v5_0_8_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;
        $connection = $installer->getConnection();

        $tempTable = $installer->getTable('m2epro_config');
        $tempRow = $connection->query("
            SELECT * FROM `{$tempTable}`
            WHERE `group` = '/amazon/synchronization/settings/orders/update/'
            AND   `key` = 'max_deactivate_time'
        ")->fetch();

        if ($tempRow === false) {

            $installer->run(<<<SQL
INSERT INTO m2epro_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/amazon/synchronization/settings/orders/update/', 'max_deactivate_time', '86400', 'in seconds',
 '2013-04-23 00:00:00', '2013-04-23 00:00:00');
SQL
            );
        }
    }

    //########################################
}