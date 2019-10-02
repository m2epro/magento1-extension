<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_3_0__v6_3_1_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;
        $connection = $installer->getConnection();

        $tempTable = $installer->getTable('m2epro_amazon_template_selling_format');

        if ($connection->tableColumnExists($tempTable, 'map_price_mode') === false) {
            $connection->addColumn(
                $tempTable, 'map_price_mode', 'TINYINT(2) UNSIGNED NOT NULL AFTER `price_coefficient`'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'map_price_custom_attribute') === false) {
            $connection->addColumn(
                $tempTable, 'map_price_custom_attribute', 'VARCHAR(255) NOT NULL AFTER `map_price_mode`'
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_amazon_listing');

        if ($connection->tableColumnExists($tempTable, 'image_main_mode') === false) {
            $connection->addColumn(
                $tempTable, 'image_main_mode', 'tinyint(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `condition_note_value`'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'image_main_attribute') === false) {
            $connection->addColumn(
                $tempTable, 'image_main_attribute', 'varchar(255) NOT NULL AFTER `image_main_mode`'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'gallery_images_mode') === false) {
            $connection->addColumn(
                $tempTable, 'gallery_images_mode', 'tinyint(2) UNSIGNED NOT NULL AFTER `image_main_attribute`'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'gallery_images_limit') === false) {
            $connection->addColumn(
                $tempTable, 'gallery_images_limit', 'tinyint(2) UNSIGNED NOT NULL DEFAULT 1 AFTER `gallery_images_mode`'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'gallery_images_attribute') === false) {
            $connection->addColumn(
                $tempTable, 'gallery_images_attribute', 'varchar(255) NOT NULL AFTER `gallery_images_limit`'
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_ebay_dictionary_marketplace');

        if ($connection->tableColumnExists($tempTable, 'client_categories_version') !== false &&
            $connection->tableColumnExists($tempTable, 'client_details_last_update_date') === false) {
            $connection->changeColumn(
                $tempTable,
                'client_categories_version',
                'client_details_last_update_date',
                'DATETIME DEFAULT NULL AFTER `marketplace_id`'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'server_categories_version') !== false &&
            $connection->tableColumnExists($tempTable, 'server_details_last_update_date') === false) {
            $connection->changeColumn(
                $tempTable,
                'server_categories_version',
                'server_details_last_update_date',
                'DATETIME DEFAULT NULL AFTER `client_details_last_update_date`'
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_amazon_dictionary_marketplace');

        if ($connection->tableColumnExists($tempTable, 'client_details_last_update_date') === false) {
            $connection->addColumn(
                $tempTable,
                'client_details_last_update_date',
                'DATETIME DEFAULT NULL AFTER `marketplace_id`'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'server_details_last_update_date') === false) {
            $connection->addColumn(
                $tempTable,
                'server_details_last_update_date',
                'DATETIME DEFAULT NULL AFTER `client_details_last_update_date`'
            );
        }

        //########################################

        $installer->run(<<<SQL

UPDATE `m2epro_wizard` as `mw`
SET `mw`.`status` = 3
WHERE `mw`.`nick` = 'migrationNewAmazon'
AND (

    (SELECT `mc`.`value`
     FROM `m2epro_config` as `mc`
     WHERE `mc`.`value` IS NOT NULL
     AND `mc`.`group` = '/component/amazon/'
     AND `mc`.`key` = 'mode'
     LIMIT 1) < 1

    OR

    (SELECT `mc`.`value`
     FROM `m2epro_config` as `mc`
     WHERE `mc`.`value` IS NOT NULL
     AND `mc`.`group` = '/component/amazon/'
     AND `mc`.`key` = 'allowed'
     LIMIT 1) < 1

);

UPDATE `m2epro_ebay_dictionary_marketplace`
SET `client_details_last_update_date` = NULL,
    `server_details_last_update_date` = NULL;

SQL
        );
    }

    //########################################
}