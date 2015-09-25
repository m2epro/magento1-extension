<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

$installer->run(<<<SQL

    ALTER TABLE `m2epro_amazon_template_synchronization`
    MODIFY COLUMN `relist_qty_value_max` INT(11) UNSIGNED NOT NULL;

    ALTER TABLE `m2epro_buy_template_synchronization`
    MODIFY COLUMN `relist_qty_value_max` INT(11) UNSIGNED NOT NULL;

SQL
);

//#############################################

/*
    ALTER TABLE `m2epro_listing_log`
    ADD COLUMN `listing_product_id` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `product_id`,
    ADD INDEX `listing_product_id` (`listing_product_id`);

    ALTER TABLE `m2epro_order_log`
    ADD COLUMN `initiator` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `type`,
    ADD INDEX `initiator` (`initiator`);

    ALTER TABLE `m2epro_amazon_category`
    ADD COLUMN registered_parameter VARCHAR(25) DEFAULT NULL AFTER `identifiers`;

    ALTER TABLE `m2epro_buy_account`
    DROP COLUMN `seller_id`;

    ALTER TABLE `m2epro_buy_listing_product`
    ADD COLUMN `template_new_product_id` INT(11) UNSIGNED DEFAULT NULL AFTER `listing_product_id`,
    ADD INDEX `template_new_product_id` (`template_new_product_id`);

    ALTER TABLE `m2epro_amazon_template_selling_format`
    DROP COLUMN `qty_coefficient`,
    DROP INDEX `qty_mode`,
    ADD INDEX `qty_mode` (`qty_mode`);

    ALTER TABLE `m2epro_buy_template_selling_format`
    DROP COLUMN `qty_coefficient`,
    DROP INDEX `qty_mode`,
    ADD INDEX `qty_mode` (`qty_mode`);
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_listing_log');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'listing_product_id') === false) {
    $connection->addColumn($tempTable, 'listing_product_id',
        'INT(11) UNSIGNED NULL DEFAULT NULL AFTER `product_id`');
}

if (!isset($tempTableIndexList[strtoupper('listing_product_id')])) {
    $connection->addKey($tempTable, 'listing_product_id', 'listing_product_id');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_order_log');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'initiator') === false) {
    $connection->addColumn($tempTable, 'initiator',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `type`');
}
if (!isset($tempTableIndexList[strtoupper('initiator')])) {
    $connection->addKey($tempTable, 'initiator', 'initiator');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_category');

if ($connection->tableColumnExists($tempTable, 'registered_parameter') === false) {
    $connection->addColumn($tempTable, 'registered_parameter',
        'VARCHAR(25) DEFAULT NULL AFTER `identifiers`');
}

// -------------------------------------------

$tempTable = $installer->getTable('m2epro_buy_account');

if ($connection->tableColumnExists($tempTable, 'seller_id') === true) {
    $connection->dropColumn($tempTable, 'seller_id');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_buy_listing_product');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'template_new_product_id') === false) {
    $connection->addColumn($tempTable, 'template_new_product_id',
        'INT(11) UNSIGNED DEFAULT NULL AFTER `listing_product_id`');
}

if (!isset($tempTableIndexList[strtoupper('template_new_product_id')])) {
    $connection->addKey($tempTable, 'template_new_product_id', 'template_new_product_id');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_template_selling_format');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'qty_coefficient') === true) {
    $connection->dropColumn($tempTable, 'qty_coefficient');
}

if (isset($tempTableIndexList[strtoupper('qty_mode')])) {
    $connection->dropKey($tempTable, 'qty_mode');
}

$tempTableIndexList = $connection->getIndexList($tempTable);

if (!isset($tempTableIndexList[strtoupper('qty_mode')])) {
    $connection->addKey($tempTable, 'qty_mode', 'qty_mode');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_buy_template_selling_format');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'qty_coefficient') === true) {
    $connection->dropColumn($tempTable, 'qty_coefficient');
}

if (isset($tempTableIndexList[strtoupper('qty_mode')])) {
    $connection->dropKey($tempTable, 'qty_mode');
}

$tempTableIndexList = $connection->getIndexList($tempTable);

if (!isset($tempTableIndexList[strtoupper('qty_mode')])) {
    $connection->addKey($tempTable, 'qty_mode', 'qty_mode');
}

//#############################################

/*
    ALTER TABLE `m2epro_ebay_template_synchronization`
    ADD COLUMN `list_mode` TINYINT(2) UNSIGNED NOT NULL AFTER `template_synchronization_id`,
    ADD COLUMN `list_status_enabled` TINYINT(2) UNSIGNED NOT NULL AFTER `list_mode`,
    ADD COLUMN `list_is_in_stock` TINYINT(2) UNSIGNED NOT NULL AFTER `list_status_enabled`,
    ADD COLUMN `list_qty` TINYINT(2) UNSIGNED NOT NULL AFTER `list_is_in_stock`,
    ADD COLUMN `list_qty_value` INT(11) UNSIGNED NOT NULL AFTER `list_qty`,
    ADD COLUMN `list_qty_value_max` INT(11) UNSIGNED NOT NULL AFTER `list_qty_value`,
    DROP COLUMN `relist_list_mode`,
    DROP COLUMN `start_auto_list`,
    DROP COLUMN `end_auto_stop`,
    ADD INDEX `list_mode` (`list_mode`),
    ADD INDEX `list_status_enabled` (`list_status_enabled`),
    ADD INDEX `list_is_in_stock` (`list_is_in_stock`),
    ADD INDEX `list_qty` (`list_qty`),
    ADD INDEX `list_qty_value` (`list_qty_value`),
    ADD INDEX `list_qty_value_max` (`list_qty_value_max`);

    ALTER TABLE `m2epro_ebay_listing_product`
    ADD COLUMN `tried_to_list` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_m2epro_listed_item`,
    ADD INDEX `tried_to_list` (`tried_to_list`);
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_synchronization');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'list_mode') === false) {
    $connection->addColumn($tempTable, 'list_mode',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `template_synchronization_id`');
}

if ($connection->tableColumnExists($tempTable, 'list_status_enabled') === false) {
    $connection->addColumn($tempTable, 'list_status_enabled',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `list_mode`');
}

if ($connection->tableColumnExists($tempTable, 'list_is_in_stock') === false) {
    $connection->addColumn($tempTable, 'list_is_in_stock',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `list_status_enabled`');
}

if ($connection->tableColumnExists($tempTable, 'list_qty') === false) {
    $connection->addColumn($tempTable, 'list_qty',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `list_is_in_stock`');
}

if ($connection->tableColumnExists($tempTable, 'list_qty_value') === false) {
    $connection->addColumn($tempTable, 'list_qty_value',
        'INT(11) UNSIGNED NOT NULL AFTER `list_qty`');
}

if ($connection->tableColumnExists($tempTable, 'list_qty_value_max') === false) {
    $connection->addColumn($tempTable, 'list_qty_value_max',
        'INT(11) UNSIGNED NOT NULL AFTER `list_qty_value`');
}

if (!isset($tempTableIndexList[strtoupper('list_mode')])) {
    $connection->addKey($tempTable, 'list_mode', 'list_mode');
}

if (!isset($tempTableIndexList[strtoupper('list_is_in_stock')])) {
    $connection->addKey($tempTable, 'list_is_in_stock', 'list_is_in_stock');
}

if (!isset($tempTableIndexList[strtoupper('list_status_enabled')])) {
    $connection->addKey($tempTable, 'list_status_enabled', 'list_status_enabled');
}

if (!isset($tempTableIndexList[strtoupper('list_qty')])) {
    $connection->addKey($tempTable, 'list_qty', 'list_qty');
}

if (!isset($tempTableIndexList[strtoupper('list_qty_value')])) {
    $connection->addKey($tempTable, 'list_qty_value', 'list_qty_value');
}

if (!isset($tempTableIndexList[strtoupper('list_qty_value_max')])) {
    $connection->addKey($tempTable, 'list_qty_value_max', 'list_qty_value_max');
}

if ($connection->tableColumnExists($tempTable, 'relist_list_mode') !== false) {
    $installer->run(<<<SQL

UPDATE `m2epro_ebay_template_synchronization`
SET `list_mode` = 1,
    `list_status_enabled` = `relist_status_enabled`,
    `list_is_in_stock` = `relist_is_in_stock`,
    `list_qty` = `relist_qty`,
    `list_qty_value` = `relist_qty_value`,
    `list_qty_value_max` = `relist_qty_value_max`
WHERE `relist_list_mode` = 1 AND `relist_mode` = 1;

UPDATE `m2epro_ebay_template_synchronization`
SET `list_mode` = 0,
    `list_status_enabled` = 1,
    `list_is_in_stock` = 1,
    `list_qty` = 0,
    `list_qty_value` = 1,
    `list_qty_value_max` = 10
WHERE `relist_mode` = 0 OR `relist_list_mode` = 0;

SQL
    );

    $connection->dropColumn($tempTable, 'relist_list_mode');
}

if ($connection->tableColumnExists($tempTable, 'start_auto_list') !== false) {
    $connection->dropColumn($tempTable, 'start_auto_list');
}

if ($connection->tableColumnExists($tempTable, 'end_auto_stop') !== false) {
    $connection->dropColumn($tempTable, 'end_auto_stop');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_listing_product');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'tried_to_list') === false) {
    $connection->addColumn($tempTable, 'tried_to_list',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_m2epro_listed_item`');
}

if (!isset($tempTableIndexList[strtoupper('tried_to_list')])) {
    $connection->addKey($tempTable, 'tried_to_list', 'tried_to_list');
}

//#############################################

/*
    ALTER TABLE `m2epro_amazon_template_synchronization`
    MODIFY COLUMN `relist_qty_value_max` INT(11) UNSIGNED NOT NULL,
    ADD COLUMN `list_mode` TINYINT(2) UNSIGNED NOT NULL AFTER `template_synchronization_id`,
    ADD COLUMN `list_status_enabled` TINYINT(2) UNSIGNED NOT NULL AFTER `list_mode`,
    ADD COLUMN `list_is_in_stock` TINYINT(2) UNSIGNED NOT NULL AFTER `list_status_enabled`,
    ADD COLUMN `list_qty` TINYINT(2) UNSIGNED NOT NULL AFTER `list_is_in_stock`,
    ADD COLUMN `list_qty_value` INT(11) UNSIGNED NOT NULL AFTER `list_qty`,
    ADD COLUMN `list_qty_value_max` INT(11) UNSIGNED NOT NULL AFTER `list_qty_value`,
    DROP COLUMN `relist_list_mode`,
    DROP COLUMN `start_auto_list`,
    DROP COLUMN `end_auto_stop`,
    ADD INDEX `list_mode` (`list_mode`),
    ADD INDEX `list_status_enabled` (`list_status_enabled`),
    ADD INDEX `list_is_in_stock` (`list_is_in_stock`),
    ADD INDEX `list_qty` (`list_qty`),
    ADD INDEX `list_qty_value` (`list_qty_value`),
    ADD INDEX `list_qty_value_max` (`list_qty_value_max`);

    ALTER TABLE `m2epro_amazon_listing_product`
    ADD COLUMN `tried_to_list` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `end_date`,
    ADD INDEX `tried_to_list` (`tried_to_list`);
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_template_synchronization');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'list_mode') === false) {
    $connection->addColumn($tempTable, 'list_mode',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `template_synchronization_id`');
}

if ($connection->tableColumnExists($tempTable, 'list_status_enabled') === false) {
    $connection->addColumn($tempTable, 'list_status_enabled',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `list_mode`');
}

if ($connection->tableColumnExists($tempTable, 'list_is_in_stock') === false) {
    $connection->addColumn($tempTable, 'list_is_in_stock',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `list_status_enabled`');
}

if ($connection->tableColumnExists($tempTable, 'list_qty') === false) {
    $connection->addColumn($tempTable, 'list_qty',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `list_is_in_stock`');
}

if ($connection->tableColumnExists($tempTable, 'list_qty_value') === false) {
    $connection->addColumn($tempTable, 'list_qty_value',
        'INT(11) UNSIGNED NOT NULL AFTER `list_qty`');
}

if ($connection->tableColumnExists($tempTable, 'list_qty_value_max') === false) {
    $connection->addColumn($tempTable, 'list_qty_value_max',
        'INT(11) UNSIGNED NOT NULL AFTER `list_qty_value`');
}

if (!isset($tempTableIndexList[strtoupper('list_mode')])) {
    $connection->addKey($tempTable, 'list_mode', 'list_mode');
}

if (!isset($tempTableIndexList[strtoupper('list_is_in_stock')])) {
    $connection->addKey($tempTable, 'list_is_in_stock', 'list_is_in_stock');
}

if (!isset($tempTableIndexList[strtoupper('list_status_enabled')])) {
    $connection->addKey($tempTable, 'list_status_enabled', 'list_status_enabled');
}

if (!isset($tempTableIndexList[strtoupper('list_qty')])) {
    $connection->addKey($tempTable, 'list_qty', 'list_qty');
}

if (!isset($tempTableIndexList[strtoupper('list_qty_value')])) {
    $connection->addKey($tempTable, 'list_qty_value', 'list_qty_value');
}

if (!isset($tempTableIndexList[strtoupper('list_qty_value_max')])) {
    $connection->addKey($tempTable, 'list_qty_value_max', 'list_qty_value_max');
}

if ($connection->tableColumnExists($tempTable, 'relist_list_mode') !== false) {
    $installer->run(<<<SQL

UPDATE `m2epro_amazon_template_synchronization`
SET `list_mode` = 1,
    `list_status_enabled` = `relist_status_enabled`,
    `list_is_in_stock` = `relist_is_in_stock`,
    `list_qty` = `relist_qty`,
    `list_qty_value` = `relist_qty_value`,
    `list_qty_value_max` = `relist_qty_value_max`
WHERE `relist_list_mode` = 1 AND `relist_mode` = 1;

UPDATE `m2epro_amazon_template_synchronization`
SET `list_mode` = 0,
    `list_status_enabled` = 1,
    `list_is_in_stock` = 1,
    `list_qty` = 0,
    `list_qty_value` = 1,
    `list_qty_value_max` = 10
WHERE `relist_mode` = 0 OR `relist_list_mode` = 0;

SQL
    );

    $connection->dropColumn($tempTable, 'relist_list_mode');
}

if ($connection->tableColumnExists($tempTable, 'start_auto_list') !== false) {
    $connection->dropColumn($tempTable, 'start_auto_list');
}

if ($connection->tableColumnExists($tempTable, 'end_auto_stop') !== false) {
    $connection->dropColumn($tempTable, 'end_auto_stop');
}

$tempTable = $installer->getTable('m2epro_amazon_listing_product');
$tempTableIndexList = $connection->getIndexList($tempTable);

//--------------------------------------------

if ($connection->tableColumnExists($tempTable, 'tried_to_list') === false) {
    $connection->addColumn($tempTable, 'tried_to_list',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `end_date`');
}

if (!isset($tempTableIndexList[strtoupper('tried_to_list')])) {
    $connection->addKey($tempTable, 'tried_to_list', 'tried_to_list');
}

//#############################################

/*
    ALTER TABLE `m2epro_buy_template_synchronization`
    ADD COLUMN `list_mode` TINYINT(2) UNSIGNED NOT NULL AFTER `template_synchronization_id`,
    ADD COLUMN `list_status_enabled` TINYINT(2) UNSIGNED NOT NULL AFTER `list_mode`,
    ADD COLUMN `list_is_in_stock` TINYINT(2) UNSIGNED NOT NULL AFTER `list_status_enabled`,
    ADD COLUMN `list_qty` TINYINT(2) UNSIGNED NOT NULL AFTER `list_is_in_stock`,
    ADD COLUMN `list_qty_value` INT(11) UNSIGNED NOT NULL AFTER `list_qty`,
    ADD COLUMN `list_qty_value_max` INT(11) UNSIGNED NOT NULL AFTER `list_qty_value`,
    DROP COLUMN `relist_list_mode`,
    DROP COLUMN `start_auto_list`,
    DROP COLUMN `end_auto_stop`,
    ADD INDEX `list_mode` (`list_mode`),
    ADD INDEX `list_status_enabled` (`list_status_enabled`),
    ADD INDEX `list_is_in_stock` (`list_is_in_stock`),
    ADD INDEX `list_qty` (`list_qty`),
    ADD INDEX `list_qty_value` (`list_qty_value`),
    ADD INDEX `list_qty_value_max` (`list_qty_value_max`);

    ALTER TABLE `m2epro_buy_listing_product`
    ADD COLUMN `tried_to_list` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `end_date`,
    ADD INDEX `tried_to_list` (`tried_to_list`);
*/

// -------------------------------------------

$tempTable = $installer->getTable('m2epro_buy_template_synchronization');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'list_mode') === false) {
    $connection->addColumn($tempTable, 'list_mode',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `template_synchronization_id`');
}

if ($connection->tableColumnExists($tempTable, 'list_status_enabled') === false) {
    $connection->addColumn($tempTable, 'list_status_enabled',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `list_mode`');
}

if ($connection->tableColumnExists($tempTable, 'list_is_in_stock') === false) {
    $connection->addColumn($tempTable, 'list_is_in_stock',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `list_status_enabled`');
}

if ($connection->tableColumnExists($tempTable, 'list_qty') === false) {
    $connection->addColumn($tempTable, 'list_qty',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `list_is_in_stock`');
}

if ($connection->tableColumnExists($tempTable, 'list_qty_value') === false) {
    $connection->addColumn($tempTable, 'list_qty_value',
        'INT(11) UNSIGNED NOT NULL AFTER `list_qty`');
}

if ($connection->tableColumnExists($tempTable, 'list_qty_value_max') === false) {
    $connection->addColumn($tempTable, 'list_qty_value_max',
        'INT(11) UNSIGNED NOT NULL AFTER `list_qty_value`');
}

if (!isset($tempTableIndexList[strtoupper('list_mode')])) {
    $connection->addKey($tempTable, 'list_mode', 'list_mode');
}

if (!isset($tempTableIndexList[strtoupper('list_is_in_stock')])) {
    $connection->addKey($tempTable, 'list_is_in_stock', 'list_is_in_stock');
}

if (!isset($tempTableIndexList[strtoupper('list_status_enabled')])) {
    $connection->addKey($tempTable, 'list_status_enabled', 'list_status_enabled');
}

if (!isset($tempTableIndexList[strtoupper('list_qty')])) {
    $connection->addKey($tempTable, 'list_qty', 'list_qty');
}

if (!isset($tempTableIndexList[strtoupper('list_qty_value')])) {
    $connection->addKey($tempTable, 'list_qty_value', 'list_qty_value');
}

if (!isset($tempTableIndexList[strtoupper('list_qty_value_max')])) {
    $connection->addKey($tempTable, 'list_qty_value_max', 'list_qty_value_max');
}

if ($connection->tableColumnExists($tempTable, 'relist_list_mode') !== false) {
    $installer->run(<<<SQL

UPDATE `m2epro_buy_template_synchronization`
SET `list_mode` = 1,
    `list_status_enabled` = `relist_status_enabled`,
    `list_is_in_stock` = `relist_is_in_stock`,
    `list_qty` = `relist_qty`,
    `list_qty_value` = `relist_qty_value`,
    `list_qty_value_max` = `relist_qty_value_max`
WHERE `relist_list_mode` = 1 AND `relist_mode` = 1;

UPDATE `m2epro_buy_template_synchronization`
SET `list_mode` = 0,
    `list_status_enabled` = 1,
    `list_is_in_stock` = 1,
    `list_qty` = 0,
    `list_qty_value` = 1,
    `list_qty_value_max` = 10
WHERE `relist_mode` = 0 OR `relist_list_mode` = 0;

SQL
    );

    $connection->dropColumn($tempTable, 'relist_list_mode');
}

if ($connection->tableColumnExists($tempTable, 'start_auto_list') !== false) {
    $connection->dropColumn($tempTable, 'start_auto_list');
}

if ($connection->tableColumnExists($tempTable, 'end_auto_stop') !== false) {
    $connection->dropColumn($tempTable, 'end_auto_stop');
}

// -------------------------------------------

$tempTable = $installer->getTable('m2epro_buy_listing_product');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'tried_to_list') === false) {
    $connection->addColumn($tempTable, 'tried_to_list',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `end_date`');
}

if (!isset($tempTableIndexList[strtoupper('tried_to_list')])) {
    $connection->addKey($tempTable, 'tried_to_list', 'tried_to_list');
}

//#############################################

$installer->run(<<<SQL

UPDATE `m2epro_ebay_listing_product`
SET `tried_to_list` = 1;

UPDATE `m2epro_amazon_listing_product`
SET `tried_to_list` = 1;

UPDATE `m2epro_buy_listing_product`
SET `tried_to_list` = 1;

SQL
);

// -------------------------------------------

$tempTable = $installer->getTable('m2epro_config');
$tempRow = $connection->query("SELECT * FROM `{$tempTable}`
                               WHERE `group` = '/buy/synchronization/settings/templates/list/'
                               AND   `key` = 'mode'")
    ->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO m2epro_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/buy/synchronization/settings/templates/list/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/templates/list/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/amazon/synchronization/settings/templates/list/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49');

SQL
    );
}

//#############################################

/*
    ALTER TABLE `m2epro_amazon_listing_product`
    CHANGE COLUMN `category_id` `template_new_product_id` INT(11) UNSIGNED DEFAULT NULL,
    DROP INDEX `category_id`,
    ADD INDEX `template_new_product_id` (`template_new_product_id`);

    TRUNCATE m2epro_amazon_category;
    TRUNCATE m2epro_amazon_category_specific;
    TRUNCATE m2epro_amazon_category_description;

    ALTER TABLE `m2epro_amazon_category` RENAME `m2epro_amazon_template_new_product`;
    ALTER TABLE `m2epro_amazon_category_specific` RENAME `m2epro_amazon_template_new_product_specific`;
    ALTER TABLE `m2epro_amazon_category_description` RENAME `m2epro_amazon_template_new_product_description`;

    ALTER TABLE `m2epro_amazon_template_new_product`
    DROP COLUMN `category_description_id`;

    ALTER TABLE `m2epro_amazon_template_new_product_specific`
    CHANGE COLUMN `category_id` `template_new_product_id` INT(11) UNSIGNED NOT NULL,
    DROP INDEX `category_id`,
    ADD INDEX `template_new_product_id` (`template_new_product_id`);

    ALTER TABLE `m2epro_amazon_template_new_product_description`
    DROP COLUMN `id`,
    ADD COLUMN `template_new_product_id` INT(11) UNSIGNED NOT NULL FIRST,
    ADD PRIMARY KEY (`template_new_product_id`),
    ADD COLUMN `search_terms_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 after `target_audience_custom_attribute`,
    ADD COLUMN `search_terms` TEXT NOT NULL after `search_terms_mode`;
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_listing_product');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'category_id') !== false &&
    $connection->tableColumnExists($tempTable, 'template_new_product_id') === false) {

    $connection->changeColumn(
        $tempTable,
        'category_id',
        'template_new_product_id',
        'INT(11) UNSIGNED DEFAULT NULL'
    );
}

if (isset($tempTableIndexList[strtoupper('category_id')])) {
    $connection->dropKey($tempTable, 'category_id');
}

if (!isset($tempTableIndexList[strtoupper('template_new_product_id')])) {
    $connection->addKey($tempTable, 'template_new_product_id', 'template_new_product_id');
}

//---------------------------------------------

$tablesList = $connection->listTables();

$tempTable = $installer->getTable('m2epro_amazon_category');

if (in_array($tempTable,$tablesList)) {
    $query = sprintf('TRUNCATE  %s', $tempTable);
    $connection->query($query);
}

$tempTable = $installer->getTable('m2epro_amazon_category_specific');

if (in_array($tempTable,$tablesList)) {
    $query = sprintf('TRUNCATE  %s', $tempTable);
    $connection->query($query);
}

$tempTable = $installer->getTable('m2epro_amazon_category_description');

if (in_array($tempTable,$tablesList)) {
    $query = sprintf('TRUNCATE  %s', $tempTable);
    $connection->query($query);
}

//---------------------------------------------

$tempOldTable = $installer->getTable('m2epro_amazon_category');
$tempNewTable = $installer->getTable('m2epro_amazon_template_new_product');

if (in_array($tempOldTable, $tablesList) && !in_array($tempNewTable, $tablesList)) {
    $query = sprintf('ALTER TABLE %s RENAME TO %s', $tempOldTable, $tempNewTable);
    $connection->query($query);
}

$tempOldTable = $installer->getTable('m2epro_amazon_category_specific');
$tempNewTable = $installer->getTable('m2epro_amazon_template_new_product_specific');

if (in_array($tempOldTable, $tablesList) && !in_array($tempNewTable, $tablesList)) {
    $query = sprintf('ALTER TABLE %s RENAME TO %s', $tempOldTable, $tempNewTable);
    $connection->query($query);
}

$tempOldTable = $installer->getTable('m2epro_amazon_category_description');
$tempNewTable = $installer->getTable('m2epro_amazon_template_new_product_description');

if (in_array($tempOldTable, $tablesList) && !in_array($tempNewTable, $tablesList)) {
    $query = sprintf('ALTER TABLE %s RENAME TO %s', $tempOldTable, $tempNewTable);
    $connection->query($query);
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_template_new_product');

if ($connection->tableColumnExists($tempTable, 'category_description_id') !== false) {
    $connection->dropColumn($tempTable, 'category_description_id');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_template_new_product_specific');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'category_id') !== false &&
    $connection->tableColumnExists($tempTable, 'template_new_product_id') === false) {

    $connection->changeColumn(
        $tempTable,
        'category_id',
        'template_new_product_id',
        'INT(11) UNSIGNED NOT NULL'
    );
}

if (isset($tempTableIndexList[strtoupper('category_id')])) {
    $connection->dropKey($tempTable, 'category_id');
}

if (!isset($tempTableIndexList[strtoupper('template_new_product_id')])) {
    $connection->addKey($tempTable, 'template_new_product_id', 'template_new_product_id');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_template_new_product_description');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'id') !== false) {
    $connection->dropColumn(
        $tempTable,
        'id'
    );
}

if ($connection->tableColumnExists($tempTable, 'template_new_product_id') === false) {
    $connection->addColumn(
        $tempTable,
        'template_new_product_id',
        'INT(11) UNSIGNED NOT NULL FIRST'
    );
}

$tempTableIndexList = $connection->getIndexList($tempTable);

if (!isset($tempTableIndexList[strtoupper('primary')])) {
    $connection->addKey($tempTable, 'PRIMARY', 'template_new_product_id', 'primary');
}

if ($connection->tableColumnExists($tempTable, 'search_terms_mode') === false) {
    $connection->addColumn(
        $tempTable,
        'search_terms_mode',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 after `target_audience_custom_attribute`'
    );
}

if ($connection->tableColumnExists($tempTable, 'search_terms') === false) {
    $connection->addColumn(
        $tempTable,
        'search_terms',
        'TEXT NOT NULL after `search_terms_mode`'
    );
}

//#############################################

$tempStatus = Mage::registry('M2EPRO_IS_INSTALLATION') === true ? '3' : '0';

$installer->run(<<<SQL

    DELETE FROM `m2epro_config`
    WHERE `group` = '/wizard/amazonNewAsin/';

    INSERT INTO  `m2epro_config` (`group`, `key`, `value`, `notice`, `update_date`, `create_date`) VALUES
    ('/wizard/amazonNewAsin/', 'priority', '7',
     '1 - highest', '2012-10-03 10:47:49', '2012-10-03 10:47:49'),
    ('/wizard/amazonNewAsin/', 'step', NULL,
     NULL, '2012-10-15 18:00:15', '2012-10-03 10:47:49'),
    ('/wizard/amazonNewAsin/', 'status', '{$tempStatus}',
     '0 - Not Started, 1 - Active, 2 - Completed, 3 - Skipped', '2012-10-15 18:00:15', '2012-10-03 10:47:49');
SQL
);

//#############################################

$installer->endSetup();

//#############################################