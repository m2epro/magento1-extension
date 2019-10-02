<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_2_1__v6_2_2_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;
        $connection = $installer->getConnection();

        $tempTable = $installer->getTable('m2epro_ebay_template_shipping');

        if ($connection->tableColumnExists($tempTable, 'click_and_collect_mode')) {
            $connection->changeColumn(
                $tempTable,
                'click_and_collect_mode',
                'click_and_collect_mode',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 1 AFTER `local_shipping_discount_profile_id`'
            );
        }

        $installer->run(<<<SQL

UPDATE `m2epro_ebay_marketplace`
SET `is_holiday_return` = 1
WHERE `marketplace_id` = 9;   -- Motor --

SQL
        );
    }

    //########################################
}