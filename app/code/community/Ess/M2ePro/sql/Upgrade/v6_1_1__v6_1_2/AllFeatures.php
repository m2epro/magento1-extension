<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_1_1__v6_1_2_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;
        $connection = $installer->getConnection();

        $tempTable = $installer->getTable('m2epro_config');
        $tempRow = $connection->query("
            SELECT * FROM `{$tempTable}`
            WHERE `group` = '/product/force_qty/'
            AND   `key` = 'mode'
        ")->fetch();

        if ($tempRow === false) {

            $installer->run(<<<SQL

INSERT INTO m2epro_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/product/force_qty/', 'mode', '0', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
('/product/force_qty/', 'value', '10', 'min qty value', '2013-05-08 00:00:00', '2013-05-08 00:00:00');

SQL
            );
        }
    }

    //########################################
}