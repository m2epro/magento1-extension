<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE `m2epro_ebay_account`
       ADD COLUMN `translation_hash` VARCHAR(255) DEFAULT NULL AFTER `server_hash`,
       ADD COLUMN `translation_info` TEXT DEFAULT NULL AFTER `translation_hash`;

    ALTER TABLE `m2epro_ebay_marketplace`
       ADD COLUMN `origin_country` VARCHAR(255) DEFAULT NULL AFTER `currency`,
       ADD COLUMN `language_code` VARCHAR(255) DEFAULT NULL AFTER `origin_country`,
       ADD COLUMN `translation_service_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `language_code`;

    ALTER TABLE `m2epro_ebay_listing_product`
       ADD COLUMN `translation_status` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `online_category`,
       ADD COLUMN `translation_service` VARCHAR(255) DEFAULT NULL AFTER `translation_status`,
       ADD COLUMN `translated_date` DATETIME DEFAULT NULL AFTER `translation_service`,
       ADD INDEX `translation_status` (`translation_status`),
       ADD INDEX `translation_service` (`translation_service`),
       ADD INDEX `translated_date` (`translated_date`);

    ALTER TABLE `m2epro_processing_request`
       CHANGE COLUMN `component` `component` VARCHAR(12) NOT NULL;
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_account');

if ($connection->tableColumnExists($tempTable, 'translation_hash') === false) {
    $connection->addColumn(
        $tempTable,
        'translation_hash',
        'VARCHAR(255) DEFAULT NULL AFTER `server_hash`');
}

if ($connection->tableColumnExists($tempTable, 'translation_info') === false) {
    $connection->addColumn(
        $tempTable,
        'translation_info',
        'TEXT DEFAULT NULL AFTER `translation_hash`');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_marketplace');

if ($connection->tableColumnExists($tempTable, 'origin_country') === false) {
    $connection->addColumn(
        $tempTable,
        'origin_country',
        'VARCHAR(255) DEFAULT NULL  AFTER `currency`');
}

if ($connection->tableColumnExists($tempTable, 'language_code') === false) {
    $connection->addColumn(
        $tempTable,
        'language_code',
        'VARCHAR(255) DEFAULT NULL AFTER `origin_country`');
}

if ($connection->tableColumnExists($tempTable, 'translation_service') === false) {
    $connection->addColumn(
        $tempTable,
        'translation_service_mode',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `language_code`');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_listing_product');

if ($connection->tableColumnExists($tempTable, 'translation_status') === false) {
    $connection->addColumn(
        $tempTable,
        'translation_status',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `online_category`');
}

if ($connection->tableColumnExists($tempTable, 'translation_service') === false) {
    $connection->addColumn(
        $tempTable,
        'translation_service',
        'VARCHAR(255) DEFAULT NULL AFTER `translation_status`');
}

if ($connection->tableColumnExists($tempTable, 'translated_date') === false) {
    $connection->addColumn(
        $tempTable,
        'translated_date',
        'DATETIME DEFAULT NULL AFTER `translation_service`');
}

$tempTableIndexList = $connection->getIndexList($tempTable);

if (!isset($tempTableIndexList[strtoupper('translation_status')])) {
    $connection->addKey($tempTable, 'translation_status',  'translation_status');
    $connection->addKey($tempTable, 'translation_service', 'translation_service');
    $connection->addKey($tempTable, 'translated_date',     'translated_date');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_processing_request');

if ($connection->tableColumnExists($tempTable, 'component') !== false) {
    $connection->changeColumn(
        $tempTable,
        'component',
        'component',
        'VARCHAR(12) NOT NULL'
    );
}

//#############################################

$tempTable = $installer->getTable('m2epro_config');

$tempRow = $connection->query("
    SELECT * FROM `{$tempTable}`
    WHERE `group` = '/ebay/sell_on_another_marketplace/'
    AND   `key` = 'tutorial_shown'
")->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO m2epro_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/ebay/sell_on_another_marketplace/', 'tutorial_shown', '0', NULL, '2014-06-25 00:00:00', '2014-06-25 00:00:00'),
('/ebay/translation_services/gold/', 'avg_cost', '7.21', NULL, '2014-07-31 00:00:00', '2014-07-31 00:00:00'),
('/ebay/translation_services/silver/', 'avg_cost', '1.21', NULL, '2014-07-31 00:00:00', '2014-07-31 00:00:00'),
('/ebay/translation_services/platinum/', 'avg_cost', '17.51', NULL, '2014-07-31 00:00:00', '2014-07-31 00:00:00');

SQL
    );
}

//#############################################

$installer->run(<<<SQL

    UPDATE `m2epro_ebay_marketplace`
    SET `is_global_shipping_program` = 1
    WHERE `marketplace_id` = 3

SQL
);

$installer->run(<<<SQL

    UPDATE `m2epro_ebay_marketplace`
    SET `origin_country` = 'us', `language_code` = 'en_US', `translation_service_mode` = 0
    WHERE `marketplace_id` = 1;

    UPDATE `m2epro_ebay_marketplace`
    SET `origin_country` = 'ca', `language_code` = 'en_CA', `translation_service_mode` = 0
    WHERE `marketplace_id` = 2;

    UPDATE `m2epro_ebay_marketplace`
    SET `origin_country` = 'gb', `language_code` = 'en_GB', `translation_service_mode` = 3
    WHERE `marketplace_id` = 3;

    UPDATE `m2epro_ebay_marketplace`
    SET `origin_country` = 'au', `language_code` = 'en_AU', `translation_service_mode` = 0
    WHERE `marketplace_id` = 4;

    UPDATE `m2epro_ebay_marketplace`
    SET `origin_country` = 'at', `language_code` = 'de_AT', `translation_service_mode` = 0
    WHERE `marketplace_id` = 5;

    UPDATE `m2epro_ebay_marketplace`
    SET `origin_country` = 'be', `language_code` = 'nl_BE', `translation_service_mode` = 0
    WHERE `marketplace_id` = 6;

    UPDATE `m2epro_ebay_marketplace`
    SET `origin_country` = 'fr', `language_code` = 'fr_FR', `translation_service_mode` = 1
    WHERE `marketplace_id` = 7;

    UPDATE `m2epro_ebay_marketplace`
    SET `origin_country` = 'de', `language_code` = 'de_DE', `translation_service_mode` = 3
    WHERE `marketplace_id` = 8;

    UPDATE `m2epro_ebay_marketplace`
    SET `origin_country` = 'us', `language_code` = 'en_US', `translation_service_mode` = 0
    WHERE `marketplace_id` = 9;

    UPDATE `m2epro_ebay_marketplace`
    SET `origin_country` = 'it', `language_code` = 'it_IT', `translation_service_mode` = 1
    WHERE `marketplace_id` = 10;

    UPDATE `m2epro_ebay_marketplace`
    SET `origin_country` = 'be', `language_code` = 'fr_BE', `translation_service_mode` = 0
    WHERE `marketplace_id` = 11;

    UPDATE `m2epro_ebay_marketplace`
    SET `origin_country` = 'nl', `language_code` = 'nl_NL', `translation_service_mode` = 0
    WHERE `marketplace_id` = 12;

    UPDATE `m2epro_ebay_marketplace`
    SET `origin_country` = 'es', `language_code` = 'es_ES', `translation_service_mode` = 1
    WHERE `marketplace_id` = 13;

    UPDATE `m2epro_ebay_marketplace`
    SET `origin_country` = 'ch', `language_code` = 'fr_CH', `translation_service_mode` = 0
    WHERE `marketplace_id` = 14;

    UPDATE `m2epro_ebay_marketplace`
    SET `origin_country` = 'hk', `language_code` = 'zh_HK', `translation_service_mode` = 0
    WHERE `marketplace_id` = 15;

    UPDATE `m2epro_ebay_marketplace`
    SET `origin_country` = 'in', `language_code` = 'hi_IN', `translation_service_mode` = 0
    WHERE `marketplace_id` = 16;

    UPDATE `m2epro_ebay_marketplace`
    SET `origin_country` = 'ie', `language_code` = 'en_IE', `translation_service_mode` = 0
    WHERE `marketplace_id` = 17;

    UPDATE `m2epro_ebay_marketplace`
    SET `origin_country` = 'my', `language_code` = 'ms_MY', `translation_service_mode` = 0
    WHERE `marketplace_id` = 18;

    UPDATE `m2epro_ebay_marketplace`
    SET `origin_country` = 'ca', `language_code` = 'fr_CA', `translation_service_mode` = 0
    WHERE `marketplace_id` = 19;

    UPDATE `m2epro_ebay_marketplace`
    SET `origin_country` = 'ph', `language_code` = 'fil_PH', `translation_service_mode` = 0
    WHERE `marketplace_id` = 20;

    UPDATE `m2epro_ebay_marketplace`
    SET `origin_country` = 'pl', `language_code` = 'pl_PL', `translation_service_mode` = 0
    WHERE `marketplace_id` = 21;

    UPDATE `m2epro_ebay_marketplace`
    SET `origin_country` = 'sg', `language_code` = 'zh_SG', `translation_service_mode` = 0
    WHERE `marketplace_id` = 22;

SQL
);

//#############################################

$installer->endSetup();

//#############################################