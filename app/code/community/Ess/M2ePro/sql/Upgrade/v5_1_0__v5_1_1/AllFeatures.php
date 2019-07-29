<?php

class Ess_M2ePro_Sql_Upgrade_v5_1_0__v5_1_1_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->installer;
        $connection = $installer->getConnection();

        /*
            ALTER TABLE `m2epro_amazon_template_new_product`
            ADD COLUMN `title` VARCHAR(255) NOT NULL DEFAULT 'Default' AFTER `xsd_hash`,
            ADD INDEX `title` (`title`);

            ALTER TABLE `m2epro_buy_template_new_product`
            ADD COLUMN `title` VARCHAR(255) NOT NULL DEFAULT 'Default' AFTER `category_id`,
            ADD INDEX `title` (`title`);
        */

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_amazon_template_new_product');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'title') === false) {
            $connection->addColumn($tempTable, 'title',
                                   'VARCHAR(255) NOT NULL DEFAULT \'Default\' AFTER `xsd_hash`');
        }

        if (!isset($tempTableIndexList[strtoupper('title')])) {
            $connection->addKey($tempTable, 'title', 'title');
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_buy_template_new_product');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'title') === false) {
            $connection->addColumn($tempTable, 'title',
                                   'VARCHAR(255) NOT NULL DEFAULT \'Default\' AFTER `category_id`');
        }

        if (!isset($tempTableIndexList[strtoupper('title')])) {
            $connection->addKey($tempTable, 'title', 'title');
        }

        //########################################

        $installer->run(<<<SQL

DELETE FROM `m2epro_config`
WHERE `group` = '/cron/lockItem/'
AND   `key` = 'max_deactivate_time';

SQL
        );
    }

    //########################################
}