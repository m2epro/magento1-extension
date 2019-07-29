<?php

class Ess_M2ePro_Sql_Upgrade_v6_2_3__v6_2_4_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->installer;
        $connection = $installer->getConnection();

        $tempTable = $installer->getTable('m2epro_amazon_account');
        $columnName = 'token';

        if ($connection->tableColumnExists($tempTable, $columnName) === false) {
            $connection->addColumn(
                $tempTable, $columnName, 'VARCHAR(255) DEFAULT NULL after `merchant_id`'
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_ebay_marketplace');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'is_click_and_collect') === false) {
            $connection->addColumn(
                $tempTable,
                'is_click_and_collect',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_charity`'
            );
        }

        if (!isset($tempTableIndexList[strtoupper('is_click_and_collect')])) {
            $connection->addKey($tempTable, 'is_click_and_collect', 'is_click_and_collect');
        }

        //########################################

        $installer->run(<<<SQL

UPDATE `m2epro_ebay_marketplace`
SET `is_click_and_collect` = 1
WHERE `marketplace_id` = 3 OR -- UK --
      `marketplace_id` = 4;   -- AU --

SQL
        );
    }

    //########################################
}