<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

$installer->run(<<<SQL

ALTER TABLE `m2epro_ebay_listing_other`
MODIFY COLUMN `item_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
MODIFY COLUMN `title` VARCHAR(255) NOT NULL AFTER `item_id`,
MODIFY COLUMN `online_price` DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
MODIFY COLUMN `online_qty` INT(11) UNSIGNED NOT NULL DEFAULT 0,
MODIFY COLUMN `online_qty_sold` INT(11) UNSIGNED NOT NULL DEFAULT 0,
MODIFY COLUMN `start_date` DATETIME NOT NULL;

ALTER TABLE `m2epro_ebay_order_external_transaction`
MODIFY COLUMN `fee` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
MODIFY COLUMN `sum` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000;

CREATE TABLE IF NOT EXISTS `m2epro_amazon_processed_inventory` (
  `hash` VARCHAR(100) NOT NULL,
  `sku` VARCHAR(100) NOT NULL,
  INDEX `hash` (`hash`),
  INDEX `sku` (`sku`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

//#############################################

/*
    ALTER TABLE `m2epro_ebay_account`
    ADD COLUMN `ebay_shipping_discount_profiles` TEXT DEFAULT NULL AFTER `ebay_info`,
    ADD COLUMN `other_listings_mapping_mode` TINYINT(2) NOT NULL DEFAULT '0' AFTER `other_listings_synchronization`,
    ADD COLUMN `other_listings_mapping_settings` VARCHAR(255) DEFAULT NULL AFTER `other_listings_mapping_mode`,
    ADD COLUMN `other_listings_synchronization_mapped_items_mode` TINYINT(2) NOT NULL DEFAULT '1'
        AFTER `other_listings_mapping_settings`;

    ALTER TABLE `m2epro_ebay_template_general`
    ADD COLUMN `refund_restockingfee` VARCHAR(255) NOT NULL AFTER `refund_shippingcost`,
    ADD COLUMN `local_shipping_combined_discount_profile_id` VARCHAR(255) DEFAULT NULL AFTER
`local_shipping_discount_mode`,
    ADD COLUMN `international_shipping_combined_discount_profile_id` VARCHAR(255) DEFAULT NULL AFTER
`international_shipping_discount_mode`,
    ADD COLUMN `use_ebay_international_shipping_rate_table` TINYINT(2) UNSIGNED NOT NULL AFTER
`use_ebay_local_shipping_rate_table`,
    ADD INDEX `use_ebay_international_shipping_rate_table` (`use_ebay_international_shipping_rate_table`);

    ALTER TABLE `m2epro_listing_other`
    ADD COLUMN `status_changer` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `status`,
    ADD INDEX `status_changer` (`status_changer`);

    ALTER TABLE `m2epro_ebay_listing_other`
    ADD COLUMN `sku` VARCHAR(255) DEFAULT NULL AFTER `item_id`,
    ADD INDEX `sku` (`sku`);

    ALTER TABLE `m2epro_ebay_marketplace`
    ADD COLUMN `related_store_id` INT(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `categories_version`;

    ALTER TABLE `m2epro_ebay_order_item`
    ADD COLUMN `unpaid_item_process_state` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `listing_type`,
    ADD INDEX `unpaid_item_process_state` (`unpaid_item_process_state`);

    ALTER TABLE `m2epro_processing_request`
    ADD COLUMN `next_part` INT(11) UNSIGNED DEFAULT NULL AFTER `perform_type`,
    ADD INDEX `next_part` (`next_part`);

    ALTER TABLE `m2epro_amazon_template_selling_format`
    CHANGE COLUMN `sale_price_start_date` `sale_price_start_date_value` DATETIME NOT NULL,
    CHANGE COLUMN `sale_price_end_date` `sale_price_end_date_value` DATETIME NOT NULL,
    ADD COLUMN `sale_price_start_date_mode` TINYINT(2) UNSIGNED NOT NULL AFTER `sale_price_coefficient`,
    ADD COLUMN `sale_price_start_date_custom_attribute` VARCHAR(255) NOT NULL AFTER `sale_price_start_date_value`,
    ADD COLUMN `sale_price_end_date_mode` TINYINT(2) UNSIGNED NOT NULL AFTER `sale_price_start_date_custom_attribute`,
    ADD COLUMN `sale_price_end_date_custom_attribute` VARCHAR(255) NOT NULL AFTER `sale_price_end_date_value`,
    DROP INDEX `sale_price_start_date`,
    DROP INDEX `sale_price_end_date`;

    ALTER TABLE `m2epro_amazon_listing_other`
    DROP COLUMN `description`,
    DROP COLUMN `notice`,
    DROP COLUMN `item_id`;

    ALTER TABLE `m2epro_amazon_listing_product`
    DROP COLUMN `item_id`;

    ALTER TABLE `m2epro_amazon_template_general`
    CHANGE COLUMN `handling_time` `handling_time_value` INT(11) UNSIGNED NOT NULL DEFAULT 1,
    ADD COLUMN `handling_time_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `condition_note_custom_attribute`,
    ADD COLUMN `handling_time_custom_attribute` VARCHAR(255) NOT NULL AFTER `handling_time_value`,
    ADD COLUMN `restock_date_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `handling_time_custom_attribute`,
    ADD COLUMN `restock_date_value` DATETIME NOT NULL AFTER `restock_date_mode`,
    ADD COLUMN `restock_date_custom_attribute` VARCHAR(255) NOT NULL AFTER `restock_date_value`;

    ALTER TABLE `m2epro_amazon_order_item`
    ADD COLUMN `gift_price` DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000 AFTER `price`,
    ADD COLUMN `gift_message` VARCHAR(500) DEFAULT NULL AFTER `gift_price`,
    ADD COLUMN `gift_type` VARCHAR(255) DEFAULT NULL AFTER `gift_message`;
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_account');

if ($connection->tableColumnExists($tempTable, 'ebay_shipping_discount_profiles') === false) {
    $connection->addColumn($tempTable, 'ebay_shipping_discount_profiles', 'TEXT DEFAULT NULL AFTER `ebay_info`');
}

if ($connection->tableColumnExists($tempTable, 'other_listings_mapping_mode') === false) {
    $connection->addColumn($tempTable, 'other_listings_mapping_mode',
        'tinyint(2) NOT NULL DEFAULT \'0\' AFTER `other_listings_synchronization`');
}

if ($connection->tableColumnExists($tempTable, 'other_listings_mapping_settings') === false) {
    $connection->addColumn($tempTable, 'other_listings_mapping_settings',
        'varchar(255) DEFAULT NULL AFTER `other_listings_mapping_mode`');
}

if ($connection->tableColumnExists($tempTable, 'other_listings_synchronization_mapped_items_mode') === false) {
    $connection->addColumn($tempTable, 'other_listings_synchronization_mapped_items_mode',
        'tinyint(2) NOT NULL DEFAULT \'1\' AFTER `other_listings_mapping_settings`');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_general');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'local_shipping_combined_discount_profile_id') === false) {
    $connection->addColumn($tempTable, 'local_shipping_combined_discount_profile_id',
        'VARCHAR(255) DEFAULT NULL AFTER `local_shipping_discount_mode`');
}
if ($connection->tableColumnExists($tempTable, 'international_shipping_combined_discount_profile_id') === false) {
    $connection->addColumn($tempTable, 'international_shipping_combined_discount_profile_id',
        'VARCHAR(255) DEFAULT NULL AFTER `international_shipping_discount_mode`');
}
if ($connection->tableColumnExists($tempTable, 'refund_restockingfee') === false) {
    $connection->addColumn($tempTable, 'refund_restockingfee',
        'VARCHAR(255) NOT NULL AFTER `refund_shippingcost`');
}
if ($connection->tableColumnExists($tempTable, 'use_ebay_international_shipping_rate_table') === false) {
    $connection->addColumn($tempTable, 'use_ebay_international_shipping_rate_table',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `use_ebay_local_shipping_rate_table`');
}

if (!isset($tempTableIndexList[strtoupper('use_ebay_international_shipping_rate_table')])) {
    $connection->addKey(
        $tempTable, 'use_ebay_international_shipping_rate_table', 'use_ebay_international_shipping_rate_table'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_listing_other');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'status_changer') === false) {
    $connection->addColumn($tempTable, 'status_changer', 'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `status`');
}

if (!isset($tempTableIndexList[strtoupper('status_changer')])) {
    $connection->addKey($tempTable, 'status_changer', 'status_changer');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_listing_other');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'sku') === false) {
    $connection->addColumn($tempTable, 'sku', 'VARCHAR(255) DEFAULT NULL AFTER `item_id`');
}

if (!isset($tempTableIndexList[strtoupper('sku')])) {
    $connection->addKey($tempTable, 'sku', 'sku');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_marketplace');

if ($connection->tableColumnExists($tempTable, 'related_store_id') === false) {
    $connection->addColumn($tempTable, 'related_store_id',
        'INT(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `categories_version`');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_order_item');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'unpaid_item_process_state') === false) {
    $connection->addColumn($tempTable, 'unpaid_item_process_state',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `listing_type`');
}

if (!isset($tempTableIndexList[strtoupper('unpaid_item_process_state')])) {
    $connection->addKey($tempTable, 'unpaid_item_process_state', 'unpaid_item_process_state');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_processing_request');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'next_part') === false) {
    $connection->addColumn($tempTable, 'next_part', 'INT(11) UNSIGNED DEFAULT NULL AFTER `perform_type`');
}

if (!isset($tempTableIndexList[strtoupper('next_part')])) {
    $connection->addKey($tempTable, 'next_part', 'next_part');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_template_selling_format');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'sale_price_start_date') !== false &&
    $connection->tableColumnExists($tempTable, 'sale_price_start_date_value') === false) {
    $connection->changeColumn($tempTable,'sale_price_start_date','sale_price_start_date_value', 'DATETIME NOT NULL');
}
if ($connection->tableColumnExists($tempTable, 'sale_price_end_date') !== false &&
    $connection->tableColumnExists($tempTable, 'sale_price_end_date_value') === false) {
    $connection->changeColumn($tempTable,'sale_price_end_date','sale_price_end_date_value', 'DATETIME NOT NULL');
}

if ($connection->tableColumnExists($tempTable, 'sale_price_start_date_mode') === false) {
    $connection->addColumn($tempTable, 'sale_price_start_date_mode',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `sale_price_coefficient`');
}

if ($connection->tableColumnExists($tempTable, 'sale_price_start_date_custom_attribute') === false) {
    $connection->addColumn($tempTable, 'sale_price_start_date_custom_attribute',
        'VARCHAR(255) NOT NULL AFTER `sale_price_start_date_value`');
}

if ($connection->tableColumnExists($tempTable, 'sale_price_end_date_mode') === false) {
    $connection->addColumn($tempTable, 'sale_price_end_date_mode',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `sale_price_start_date_custom_attribute`');
}

if ($connection->tableColumnExists($tempTable, 'sale_price_end_date_custom_attribute') === false) {
    $connection->addColumn($tempTable, 'sale_price_end_date_custom_attribute',
        'VARCHAR(255) NOT NULL AFTER `sale_price_end_date_value`');
}

if (isset($tempTableIndexList[strtoupper('sale_price_start_date')])) {
    $connection->dropKey($tempTable, 'sale_price_start_date');
}

if (isset($tempTableIndexList[strtoupper('sale_price_end_date')])) {
    $connection->dropKey($tempTable, 'sale_price_end_date');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_listing_other');

if ($connection->tableColumnExists($tempTable, 'description') === true) {
    $connection->dropColumn($tempTable, 'description');
}

if ($connection->tableColumnExists($tempTable, 'notice') === true) {
    $connection->dropColumn($tempTable, 'notice');
}

if ($connection->tableColumnExists($tempTable, 'item_id') === true) {
    $connection->dropColumn($tempTable, 'item_id');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_listing_product');

if ($connection->tableColumnExists($tempTable, 'item_id') === true) {
    $connection->dropColumn($tempTable, 'item_id');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_template_general');

if ($connection->tableColumnExists($tempTable, 'handling_time') !== false &&
    $connection->tableColumnExists($tempTable, 'handling_time_value') === false) {
    $connection->changeColumn($tempTable,'handling_time','handling_time_value', 'INT(11) UNSIGNED NOT NULL DEFAULT 1');
}

if ($connection->tableColumnExists($tempTable, 'handling_time_mode') === false) {
    $connection->addColumn($tempTable, 'handling_time_mode',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `condition_note_custom_attribute`');
}

if ($connection->tableColumnExists($tempTable, 'handling_time_custom_attribute') === false) {
    $connection->addColumn($tempTable, 'handling_time_custom_attribute',
        'VARCHAR(255) NOT NULL AFTER `handling_time_value`');
}

if ($connection->tableColumnExists($tempTable, 'restock_date_mode') === false) {
    $connection->addColumn($tempTable, 'restock_date_mode',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `handling_time_custom_attribute`');
}

if ($connection->tableColumnExists($tempTable, 'restock_date_value') === false) {
    $connection->addColumn($tempTable, 'restock_date_value', 'DATETIME NOT NULL AFTER `restock_date_mode`');
}

if ($connection->tableColumnExists($tempTable, 'restock_date_custom_attribute') === false) {
    $connection->addColumn($tempTable, 'restock_date_custom_attribute',
        'VARCHAR(255) NOT NULL AFTER `restock_date_value`');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_order_item');

if ($connection->tableColumnExists($tempTable, 'gift_price') === false) {
    $connection->addColumn($tempTable, 'gift_price',
        'DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000 AFTER `price`');
}

if ($connection->tableColumnExists($tempTable, 'gift_message') === false) {
    $connection->addColumn($tempTable, 'gift_message',
        'VARCHAR(500) DEFAULT NULL AFTER `gift_price`');
}

if ($connection->tableColumnExists($tempTable, 'gift_type') === false) {
    $connection->addColumn($tempTable, 'gift_type',
        'VARCHAR(255) DEFAULT NULL AFTER `gift_message`');
}

//#############################################

$tempTable = $installer->getTable('m2epro_config');
$tempRow = $connection->query("SELECT * FROM `{$tempTable}`
                               WHERE `group` = '/synchronization/settings/defaults/processing/'
                               AND   `key` = 'mode'")
                      ->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/synchronization/settings/defaults/processing/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),

('/ebay/synchronization/settings/feedbacks/receive/', 'interval', '10800', 'in seconds',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/feedbacks/receive/', 'last_access', NULL, 'date of last access',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/feedbacks/response/', 'last_access', NULL, 'date of last access',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/feedbacks/response/', 'attempt_interval', '86400', 'in seconds',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),

('/ebay/synchronization/settings/other_listings/update/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-10-22 13:19:47', '2011-01-12 02:55:16'),
('/ebay/synchronization/settings/other_listings/update/', 'max_deactivate_time', '86400', 'in seconds',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listings/templates/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-10-22 13:19:47', '2011-01-12 02:55:16'),
('/ebay/synchronization/settings/other_listings/templates/relist/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-10-22 13:19:47', '2011-01-12 02:55:16'),
('/ebay/synchronization/settings/other_listings/templates/revise/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-10-22 13:19:47', '2011-01-12 02:55:16'),
('/ebay/synchronization/settings/other_listings/templates/stop/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-10-22 13:19:47', '2011-01-12 02:55:16'),

('/ebay/synchronization/settings/other_listing/source/', 'qty', '1',
 '0 - none, \r\n1 - product qty, \r\n2 - custom attribute', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/source/', 'price', '1',
 '0 - none, \r\n1 - product price, \r\n2 - custom attribute, \r\n4 - special price',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/source/', 'title', '1',
 '0 - none, \r\n1 - product title, \r\n2 - custom attribute', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/source/', 'sub_title', '0',
 '0 - none, \r\n1 - product subtitle, \r\n2 - custom attribute', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/source/', 'description', '1',
 '0 - none, \r\n1 - product description, \r\n2 - custom attribute', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/revise/', 'revise_update_qty', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/revise/', 'revise_update_price', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/revise/', 'revise_update_title', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/revise/', 'revise_update_sub_title', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/revise/', 'revise_update_description', '1',
 '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/relist/', 'relist_mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/relist/', 'relist_filter_user_lock', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/relist/', 'relist_send_data', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/relist/', 'relist_status_enabled', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/relist/', 'relist_is_in_stock', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/relist/', 'relist_qty', '0', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/relist/', 'relist_qty_value', '1', NULL,
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/relist/', 'relist_qty_value_max', '10', NULL,
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/stop/', 'stop_status_disabled', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/stop/', 'stop_out_off_stock', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/stop/', 'stop_qty', '0', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/stop/', 'stop_qty_value', '0', NULL,
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/stop/', 'stop_qty_value_max', '0', NULL ,
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),

('/ebay/synchronization/settings/orders/receive/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/orders/cancellation/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/orders/cancellation/', 'interval', '86400', 'in seconds',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/orders/cancellation/', 'last_access', NULL, 'date of last access',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/orders/cancellation/', 'start_date', NULL, 'date of first run',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),

('/logs/listings/', 'last_action_id', '0', NULL,'2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/logs/other_listings/', 'last_action_id', '0', NULL,'2012-05-21 10:47:49', '2012-05-21 10:47:49');

SQL
);
}

//#############################################

$installer->run(<<<SQL

UPDATE `m2epro_ebay_template_general`
SET `gallery_type` = 4;

UPDATE `m2epro_ebay_account`
SET `other_listings_synchronization_mapped_items_mode` = 0;

UPDATE `m2epro_config`
SET `value` = '86400'
WHERE `group` = '/amazon/synchronization/settings/defaults/update_listings_products/'
AND   `key` = 'max_deactivate_time';

UPDATE `m2epro_config`
SET `value` = '86400'
WHERE `group` = '/amazon/synchronization/settings/orders/'
AND   `key` = 'max_deactivate_time';

UPDATE `m2epro_config`
SET `value` = '86400'
WHERE `group` = '/amazon/synchronization/settings/other_listings/'
AND   `key` = 'max_deactivate_time';

UPDATE `m2epro_config`
SET `value` = '10800'
WHERE `group` = '/ebay/synchronization/settings/feedbacks/response/'
AND   `key` = 'interval';

UPDATE `m2epro_amazon_template_selling_format`
SET `sale_price_start_date_mode` = 0,
    `sale_price_end_date_mode` = 0;

UPDATE `m2epro_amazon_template_selling_format`
SET `sale_price_mode` = 4
WHERE `sale_price_mode` = 0;

UPDATE `m2epro_amazon_template_general`
SET `handling_time_mode` = 1;

UPDATE `m2epro_ebay_template_general`
SET `sku_mode` = 1
WHERE `variation_ignore` = 0
AND   `variation_enabled` = 1;

SQL
);

//#############################################

$tempTable = $installer->getTable('m2epro_amazon_account');
$tempAccounts = $connection->query("SELECT * FROM `{$tempTable}`")->fetchAll();

foreach ($tempAccounts as $account) {

    $synchSetting = "0";

    if (!empty($account['other_listings_move_settings'])) {

        $tempSettings = json_decode($account['other_listings_move_settings'], true);

        if (!isset($tempSettings['synch']['qty']) || !isset($tempSettings['synch']['price'])) {
            continue;
        }

        if ($tempSettings['synch']['qty'] == 1 && $tempSettings['synch']['price'] == 1) {
            $synchSetting = "1";
        }

        if ($tempSettings['synch']['qty'] == 0 && $tempSettings['synch']['price'] == 1) {
            $synchSetting = "2";
        }

        if ($tempSettings['synch']['qty'] == 1 && $tempSettings['synch']['price'] == 0) {
            $synchSetting = "3";
        }
    }

    $synchSetting = $connection->quote(json_encode(array('synch' => $synchSetting)));

    $connection->query(
        "UPDATE `{$tempTable}`
         SET `other_listings_move_settings` = {$synchSetting}
         WHERE `account_id` = ".(int)$account['account_id']
    );
}

//#############################################

$tempTable = $installer->getTable('m2epro_ebay_template_description');
$tempTemplates = $connection->query("SELECT * FROM `{$tempTable}`")->fetchAll();

foreach ($tempTemplates as $template) {

    if (empty($template['watermark_settings'])) {
        continue;
    }

    $tempWatermarkSettings = json_decode($template['watermark_settings'], true);
    if (isset($tempWatermarkSettings['transparent'])) {
        continue;
    }

    $tempWatermarkSettings['transparent'] = "1";
    $tempWatermarkSettings = $connection->quote(json_encode($tempWatermarkSettings));

    $connection->query(
        "UPDATE `{$tempTable}`
         SET `watermark_settings` = {$tempWatermarkSettings}
         WHERE `template_description_id` = ".(int)$template['template_description_id']
    );
}

//#############################################

$tempTable = $installer->getTable('m2epro_ebay_account');
$tempAccounts = $connection->query("SELECT * FROM `{$tempTable}`")->fetchAll();

foreach ($tempAccounts as $account) {
    $magentoOrdersSettings = json_decode($account['magento_orders_settings'], true);

    if (!is_array($magentoOrdersSettings)) {
        continue;
    }

    // old format setting
    // ----------
    $rules = isset($magentoOrdersSettings['rules']) ? $magentoOrdersSettings['rules'] : array();
    // ----------

    // new format settings
    // ----------
    $creation = array(
        'mode' => 4, // checkout & payment completed
        'reservation_days' => 0
    );
    // ----------

    if (isset($rules['checkout_mode']) && isset($rules['payment_mode'])) {
        $checkout = (int)$rules['checkout_mode'];
        $payment  = (int)$rules['payment_mode'];

        $checkout == 0 && $payment == 0 && $creation['mode'] = 1; // immediately
        $checkout == 1 && $payment == 0 && $creation['mode'] = 2; // checkout completed
        $checkout == 0 && $payment == 1 && $creation['mode'] = 3; // payment completed
        $checkout == 1 && $payment == 1 && $creation['mode'] = 4; // checkout & payment completed
    }

    unset($magentoOrdersSettings['rules']);

    $magentoOrdersSettings['creation'] = $creation;
    $magentoOrdersSettings = $connection->quote(json_encode($magentoOrdersSettings));

    $connection->query(
        "UPDATE `{$tempTable}`
         SET `magento_orders_settings` = {$magentoOrdersSettings}
         WHERE `account_id` = ".(int)$account['account_id']
    );
}

//#############################################

$currentDateTime = date('c');

$mainTable = $installer->getTable('m2epro_listing_other');
$secondTable = $installer->getTable('m2epro_amazon_listing_other');
$accountTable = $installer->getTable('m2epro_amazon_account');
$itemTable = $installer->getTable('m2epro_amazon_item');

$tempRows = $connection->query("
            SELECT `lo`.`id` as `listing_other_id`,
                   `lo`.`marketplace_id`,
                   `lo`.`account_id`,
                   `alo`.`sku`,
                   `aa`.`marketplaces_data`,
                   `lo`.`product_id`

            FROM `{$mainTable}` AS `lo`
            INNER JOIN `{$secondTable}` AS `alo` ON `lo`.`id` = `alo`.`listing_other_id`
            INNER JOIN `{$accountTable}` AS `aa` ON `lo`.`account_id` = `aa`.`account_id`
            LEFT JOIN `{$itemTable}` AS `ai` ON (`lo`.`product_id` = `ai`.`product_id` AND
                                                 `lo`.`marketplace_id` = `ai`.`marketplace_id` AND
                                                 `lo`.`account_id` = `ai`.`account_id` AND
                                                 `alo`.`sku` = `ai`.`sku`)

            WHERE `lo`.`product_id` IS NOT NULL
            AND  `alo`.`sku` IS NOT NULL
            AND `ai`.`id` IS NULL")->fetchAll();

$insertSql = '';
foreach ($tempRows as $tempRow) {

    if (empty($tempRow['marketplaces_data'])) {
        continue;
    }

    !empty($insertSql) && $insertSql .= ' , ';

    $accountId = (int)$tempRow['account_id'];
    $marketplaceId = (int)$tempRow['marketplace_id'];
    $skuMySql = $connection->quote($tempRow['sku']);
    $productId = (int)$tempRow['product_id'];

    $tempMarketplacesData = json_decode($tempRow['marketplaces_data'],true);
    $storeId = (int)$tempMarketplacesData[$marketplaceId]['related_store_id'];

    $insertSql .= "(
        {$accountId},{$marketplaceId},{$skuMySql},{$productId},{$storeId},'{$currentDateTime}','{$currentDateTime}'
    )";
}

if (!empty($insertSql)) {

    $insertSql = 'INSERT INTO `'.$itemTable.'` (`account_id`,`marketplace_id`,`sku`,`product_id`,`store_id`,
                  `update_date`,`create_date`) VALUES '.$insertSql.';';

    $connection->query($insertSql);
}

//---------------------------------------------

$currentDateTime = date('c');

$mainTable = $installer->getTable('m2epro_listing_other');
$secondTable = $installer->getTable('m2epro_ebay_listing_other');
$marketplaceTable = $installer->getTable('m2epro_ebay_marketplace');
$itemTable = $installer->getTable('m2epro_ebay_item');

$tempRows = $connection->query("
            SELECT `lo`.`id` as `listing_other_id`,
                   `elo`.`item_id`,
                   `em`.`related_store_id`,
                   `lo`.`product_id`

            FROM `{$mainTable}` AS `lo`
            INNER JOIN `{$secondTable}` AS `elo` ON `lo`.`id` = `elo`.`listing_other_id`
            INNER JOIN `{$marketplaceTable}` AS `em` ON `lo`.`marketplace_id` = `em`.`marketplace_id`
            LEFT JOIN `{$itemTable}` AS `ei` ON (`lo`.`product_id` = `ei`.`product_id` AND
                                                 `elo`.`item_id` = `ei`.`item_id`)

            WHERE `lo`.`product_id` IS NOT NULL
            AND `ei`.`id` IS NULL")->fetchAll();

$insertSql = '';
foreach ($tempRows as $tempRow) {

    !empty($insertSql) && $insertSql .= ' , ';

    $itemId = (float)$tempRow['item_id'];
    $productId = (int)$tempRow['product_id'];
    $storeId = (int)$tempRow['related_store_id'];

    $insertSql .= "({$itemId}, {$productId}, {$storeId}, '{$currentDateTime}', '{$currentDateTime}')";
}

if (!empty($insertSql)) {

    $insertSql = 'INSERT INTO `'.$itemTable.'` (`item_id`,`product_id`,`store_id`,
                  `update_date`,`create_date`) VALUES '.$insertSql.';';

    $connection->query($insertSql);
}

//---------------------------------------------

$currentDateTime = date('c');

$mainTable = $installer->getTable('m2epro_listing_product');
$secondTable = $installer->getTable('m2epro_amazon_listing_product');
$listingTable = $installer->getTable('m2epro_listing');
$templateTable = $installer->getTable('m2epro_template_general');
$accountTable = $installer->getTable('m2epro_amazon_account');
$itemTable = $installer->getTable('m2epro_amazon_item');

$tempRows = $connection->query("
            SELECT `lp`.`id` as `listing_product_id`,
                   `tg`.`marketplace_id`,
                   `tg`.`account_id`,
                   `alp`.`sku`,
                   `aa`.`marketplaces_data`,
                   `lp`.`product_id`

            FROM `{$mainTable}` AS `lp`
            INNER JOIN `{$secondTable}` AS `alp` ON `lp`.`id` = `alp`.`listing_product_id`
            INNER JOIN `{$listingTable}` AS `l` ON `lp`.`listing_id` = `l`.`id`
            INNER JOIN `{$templateTable}` AS `tg` ON `l`.`template_general_id` = `tg`.`id`
            INNER JOIN `{$accountTable}` AS `aa` ON `tg`.`account_id` = `aa`.`account_id`
            LEFT JOIN `{$itemTable}` AS `ai` ON (`lp`.`product_id` = `ai`.`product_id` AND
                                                 `tg`.`marketplace_id` = `ai`.`marketplace_id` AND
                                                 `tg`.`account_id` = `ai`.`account_id` AND
                                                 `alp`.`sku` = `ai`.`sku`)

            WHERE `alp`.`sku` IS NOT NULL
            AND `ai`.`id` IS NULL")->fetchAll();

$insertSql = '';
foreach ($tempRows as $tempRow) {

    if (empty($tempRow['marketplaces_data'])) {
        continue;
    }

    !empty($insertSql) && $insertSql .= ' , ';

    $accountId = (int)$tempRow['account_id'];
    $marketplaceId = (int)$tempRow['marketplace_id'];
    $skuMySql = $connection->quote($tempRow['sku']);
    $productId = (int)$tempRow['product_id'];

    $tempMarketplacesData = json_decode($tempRow['marketplaces_data'],true);
    $storeId = (int)$tempMarketplacesData[$marketplaceId]['related_store_id'];

    $insertSql .= "(
        {$accountId},{$marketplaceId},{$skuMySql},{$productId},{$storeId},'{$currentDateTime}','{$currentDateTime}'
    )";
}

if (!empty($insertSql)) {

    $insertSql = 'INSERT INTO `'.$itemTable.'` (`account_id`,`marketplace_id`,`sku`,`product_id`,`store_id`,
                  `update_date`,`create_date`) VALUES '.$insertSql.';';

    $connection->query($insertSql);
}

//#############################################

$tempTable = $installer->getTable('m2epro_listing_log');
$tempRow = $connection->query("SELECT max(`action_id`) as `max_id`
                               FROM `{$tempTable}`")->fetch();

$installer->run("UPDATE `m2epro_config`
                 SET `value` = '".((int)$tempRow['max_id'] + 1000)."'
                 WHERE `group` = '/logs/listings/'
                 AND   `key` = 'last_action_id'");

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_listing_other_log');
$tempRow = $connection->query("SELECT max(`action_id`) as `max_id`
                               FROM `{$tempTable}`")->fetch();

$installer->run("UPDATE `m2epro_config`
                 SET `value` = '".((int)$tempRow['max_id'] + 1000)."'
                 WHERE `group` = '/logs/other_listings/'
                 AND   `key` = 'last_action_id'");

//#############################################

$tempTable = $installer->getTable('m2epro_config');
$tempRow = $connection->query("SELECT * FROM `{$tempTable}`
                               WHERE `group` = '/wizard/main/'")
                      ->fetch();

if ($tempRow === false) {

    $installStatus = (int)$connection->query("SELECT * FROM `{$tempTable}`
                                              WHERE `group` = '/wizard/'")
                                     ->fetchColumn(3);
    $upgradeStatus = (int)$connection->query("SELECT * FROM `{$tempTable}`
                                              WHERE `group` = '/wizard/upgrade/'")
                                     ->fetchColumn(3);

    $connection->query("DELETE FROM `{$tempTable}` WHERE `group` LIKE '%wizard%'");

    if ($installStatus < 99) {
        $mainStatus = 0;
        $ebayStatus = 0;
        $amazonStatus = 0;
    } else {
        if ($installStatus == 99) {
            $mainStatus = $ebayStatus = 3;
        }
        if ($installStatus == 100) {
            $mainStatus = $ebayStatus = 2;
        }
        if ($upgradeStatus < 99) {
            $amazonStatus = 0;
        } else {
            if ($upgradeStatus == 99) {
                $amazonStatus = 3;
            }
            if ($upgradeStatus == 100) {
                $amazonStatus = 2;
            }
        }
    }

    if ($ebayStatus == 0) {
        $ebayOtherListingStatus = 3;
    } else {
        $ebayOtherListingStatus = 0;
    }

    $installer->run(<<<SQL

    INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
    ('/wizard/main/', 'status', '{$mainStatus}', '0 - Not Started, 1 - Active, 2 - Completed, 3 - Skipped',
    '2012-10-15 17:54:56', '2012-10-03 10:47:49'),
    ('/wizard/main/', 'step', NULL, NULL,
    '2012-10-15 17:54:53', '2012-10-03 10:47:49'),
    ('/wizard/main/', 'priority', '1', '1 - highest',
    '2012-10-03 10:47:49', '2012-10-03 10:47:49'),
    ('/wizard/ebay/', 'status', '{$ebayStatus}', '0 - Not Started, 1 - Active, 2 - Completed, 3 - Skipped',
    '2012-10-15 18:00:10', '2012-10-03 10:47:49'),
    ('/wizard/ebay/', 'step', NULL, NULL,
     '2012-10-15 18:00:08', '2012-10-03 10:47:49'),
    ('/wizard/ebay/', 'priority', '2', '1 - highest',
     '2012-10-03 10:47:49', '2012-10-03 10:47:49'),
    ('/wizard/amazon/', 'status', '{$amazonStatus}', '0 - Not Started, 1 - Active, 2 - Completed, 3 - Skipped',
     '2012-10-15 18:00:15', '2012-10-03 10:47:49'),
    ('/wizard/amazon/', 'step', NULL, NULL,
    '2012-10-15 18:00:15', '2012-10-03 10:47:49'),
    ('/wizard/amazon/', 'priority', '3', '1 - highest',
     '2012-10-03 10:47:49', '2012-10-03 10:47:49'),
    ('/wizard/ebayOtherListing/', 'status', '{$ebayOtherListingStatus}',
     '0 - Not Started, 1 - Active, 2 - Completed, 3 - Skipped','2012-10-15 18:00:15', '2012-10-03 10:47:49'),
    ('/wizard/ebayOtherListing/', 'step', NULL, NULL,
    '2012-10-15 18:00:15', '2012-10-03 10:47:49'),
    ('/wizard/ebayOtherListing/', 'priority', '4', '1 - highest',
     '2012-10-03 10:47:49', '2012-10-03 10:47:49');

SQL
);
}

//#############################################

$installer->endSetup();

//#############################################