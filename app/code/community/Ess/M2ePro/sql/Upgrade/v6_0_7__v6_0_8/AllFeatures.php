<?php

class Ess_M2ePro_Sql_Upgrade_v6_0_7__v6_0_8_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->installer;
        $connection = $installer->getConnection();

        $tempTable = $installer->getTable('m2epro_ebay_order');

        if ($connection->tableColumnExists($tempTable, 'global_shipping_details') === false) {
            $connection->addColumn(
                $tempTable,
                'global_shipping_details',
                'TEXT DEFAULT NULL AFTER `shipping_date`'
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_ebay_marketplace');

        if ($connection->tableColumnExists($tempTable, 'is_global_shipping_program') === false) {
            $connection->addColumn(
                $tempTable,
                'is_global_shipping_program',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_cash_on_delivery`'
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_ebay_template_shipping');

        if ($connection->tableColumnExists($tempTable, 'global_shipping_program') === false) {
            $connection->addColumn(
                $tempTable,
                'global_shipping_program',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `international_trade`'
            );
        }

        //########################################

        $installer->run(<<<SQL

UPDATE `m2epro_ebay_marketplace`
SET `is_global_shipping_program` = 1
WHERE `marketplace_id` = '1';

SQL
        );
    }

    //########################################
}