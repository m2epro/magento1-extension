<?php

class Ess_M2ePro_Sql_Upgrade_v6_2_0__v6_2_1_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->installer;
        $connection = $installer->getConnection();

        $tempTable = $installer->getTable('m2epro_ebay_template_shipping');

        if ($connection->tableColumnExists($tempTable, 'click_and_collect_mode') === false) {
            $connection->addColumn(
                $tempTable,
                'click_and_collect_mode',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `local_shipping_discount_profile_id`'
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_ebay_template_return');

        if ($connection->tableColumnExists($tempTable, 'holiday_mode') === false) {
            $connection->addColumn(
                $tempTable,
                'holiday_mode',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `within`'
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_ebay_marketplace');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'is_holiday_return') === false) {
            $connection->addColumn(
                $tempTable,
                'is_holiday_return',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_charity`'
            );
        }

        if (!isset($tempTableIndexList[strtoupper('is_holiday_return')])) {
            $connection->addKey($tempTable, 'is_holiday_return', 'is_holiday_return');
        }

        //########################################

        $installer->run(<<<SQL

UPDATE `m2epro_ebay_marketplace`
SET `is_stp` = 1
WHERE `marketplace_id` = 9; -- Motor --

UPDATE `m2epro_ebay_marketplace`
SET `is_holiday_return` = 1
WHERE `marketplace_id` = 1 OR -- US --
      `marketplace_id` = 2 OR -- CA --
      `marketplace_id` = 3 OR -- UK --
      `marketplace_id` = 4 OR -- AU --
      `marketplace_id` = 8;   -- DE --

SQL
        );
    }

    //########################################
}