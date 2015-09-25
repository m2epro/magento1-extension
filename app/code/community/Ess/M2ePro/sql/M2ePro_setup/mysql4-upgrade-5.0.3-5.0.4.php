<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

$installer->run(<<<SQL

ALTER TABLE `m2epro_buy_template_new_product_core`
MODIFY `weight_custom_value` DECIMAL(10, 2) UNSIGNED DEFAULT NULL;

SQL
);

//#############################################

/*
    ALTER TABLE `m2epro_buy_template_new_product_core`
    CHANGE COLUMN `produc_set_id_custom_attribute` `product_set_id_custom_attribute` VARCHAR(255) DEFAULT NULL,
    DROP COLUMN `seller_sku_mode`,
    DROP COLUMN `seller_sku_custom_value`,
    DROP COLUMN `gtin_mode`,
    DROP COLUMN `gtin_custom_value`,
    DROP COLUMN `asin_custom_value`,
    DROP COLUMN `isbn_custom_value`;
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_buy_template_new_product_core');

if ($connection->tableColumnExists($tempTable, 'produc_set_id_custom_attribute') !== false &&
    $connection->tableColumnExists($tempTable, 'product_set_id_custom_attribute') === false) {

    $connection->changeColumn(
        $tempTable,
        'produc_set_id_custom_attribute',
        'product_set_id_custom_attribute',
        'VARCHAR(255) DEFAULT NULL'
    );
}

if ($connection->tableColumnExists($tempTable, 'seller_sku_mode') === true) {
    $connection->dropColumn($tempTable, 'seller_sku_mode');
}

if ($connection->tableColumnExists($tempTable, 'seller_sku_custom_value') === true) {
    $connection->dropColumn($tempTable, 'seller_sku_custom_value');
}

if ($connection->tableColumnExists($tempTable, 'gtin_mode') === true) {
    $connection->dropColumn($tempTable, 'gtin_mode');
}

if ($connection->tableColumnExists($tempTable, 'gtin_custom_value') === true) {
    $connection->dropColumn($tempTable, 'gtin_custom_value');
}

if ($connection->tableColumnExists($tempTable, 'asin_custom_value') === true) {
    $connection->dropColumn($tempTable, 'asin_custom_value');
}

if ($connection->tableColumnExists($tempTable, 'isbn_custom_value') === true) {
    $connection->dropColumn($tempTable, 'isbn_custom_value');
}

//#############################################

$tempStatus = Mage::registry('M2EPRO_IS_INSTALLATION') === true ? '3' : '0';

$installer->run(<<<SQL

    DELETE FROM `m2epro_config`
    WHERE `group` = '/wizard/buyNewSku/';

    INSERT INTO  `m2epro_config` (`group`, `key`, `value`, `notice`, `update_date`, `create_date`) VALUES
    ('/wizard/buyNewSku/', 'priority', '8',
     '1 - highest', '2012-10-03 10:47:49', '2012-10-03 10:47:49'),
    ('/wizard/buyNewSku/', 'step', NULL,
     NULL, '2012-10-15 18:00:15', '2012-10-03 10:47:49'),
    ('/wizard/buyNewSku/', 'status', '{$tempStatus}',
     '0 - Not Started, 1 - Active, 2 - Completed, 3 - Skipped', '2012-10-15 18:00:15', '2012-10-03 10:47:49');

    UPDATE `m2epro_config`
    SET `value` = '1'
    WHERE `group` = '/buy/synchronization/settings/marketplaces/'
    AND `key` = 'mode';

SQL
);

//#############################################

/*
    ALTER TABLE `m2epro_amazon_template_new_product`
    ADD COLUMN `item_package_quantity_mode`
        TINYINT(2) UNSIGNED DEFAULT 0 AFTER `worldwide_id_custom_attribute`,
    ADD COLUMN `item_package_quantity_custom_value`
        VARCHAR(255) DEFAULT NULL AFTER `item_package_quantity_mode`,
    ADD COLUMN `item_package_quantity_custom_attribute`
        VARCHAR(255) DEFAULT NULL AFTER `item_package_quantity_custom_value`,
    ADD COLUMN `number_of_items_mode`
        TINYINT(2) UNSIGNED DEFAULT 0 AFTER `item_package_quantity_custom_attribute`,
    ADD COLUMN `number_of_items_custom_value`
        VARCHAR(255) DEFAULT NULL AFTER `number_of_items_mode`,
    ADD COLUMN `number_of_items_custom_attribute`
        VARCHAR(255) DEFAULT NULL AFTER `number_of_items_custom_value`,
    ADD INDEX xsd_hash (xsd_hash),
    ADD INDEX node_title (node_title),
    ADD INDEX category_path (category_path),
    ADD INDEX worldwide_id_mode (worldwide_id_mode),
    ADD INDEX item_package_quantity_mode (item_package_quantity_mode),
    ADD INDEX number_of_items_mode (number_of_items_mode);

    ALTER TABLE `m2epro_amazon_template_new_product_description`
    ADD COLUMN `package_weight_mode`
        TINYINT(2) UNSIGNED DEFAULT 0 AFTER `manufacturer_part_number_custom_attribute`,
    ADD COLUMN `package_weight_custom_value`
        VARCHAR(255) DEFAULT NULL AFTER `package_weight_mode`,
    ADD COLUMN `package_weight_custom_attribute`
        VARCHAR(255) DEFAULT NULL AFTER `package_weight_custom_value`,
    ADD COLUMN `package_weight_unit_of_measure_mode`
        TINYINT(2) UNSIGNED DEFAULT 1 AFTER `package_weight_custom_attribute`,
    ADD COLUMN `package_weight_unit_of_measure_custom_value`
        VARCHAR(255) DEFAULT NULL AFTER `package_weight_unit_of_measure_mode`,
    ADD COLUMN `package_weight_unit_of_measure_custom_attribute`
        VARCHAR(255) DEFAULT NULL AFTER `package_weight_unit_of_measure_custom_value`,
    ADD COLUMN `shipping_weight_mode`
        TINYINT(2) UNSIGNED DEFAULT 0 AFTER `package_weight_custom_attribute`,
    ADD COLUMN `shipping_weight_custom_value`
        VARCHAR(255) DEFAULT NULL AFTER `shipping_weight_mode`,
    ADD COLUMN `shipping_weight_custom_attribute`
        VARCHAR(255) DEFAULT NULL AFTER `shipping_weight_custom_value`,
    ADD COLUMN `shipping_weight_unit_of_measure_mode`
        TINYINT(2) UNSIGNED DEFAULT 1 AFTER `shipping_weight_custom_attribute`,
    ADD COLUMN `shipping_weight_unit_of_measure_custom_value`
        VARCHAR(255) DEFAULT NULL AFTER `shipping_weight_unit_of_measure_mode`,
    ADD COLUMN `shipping_weight_unit_of_measure_custom_attribute`
        VARCHAR(255) DEFAULT NULL AFTER `shipping_weight_unit_of_measure_custom_value`,
    ADD INDEX manufacturer_part_number_mode (manufacturer_part_number_mode),
    ADD INDEX target_audience_mode (target_audience_mode),
    ADD INDEX search_terms_mode (search_terms_mode),
    ADD INDEX package_weight_mode (package_weight_mode),
    ADD INDEX shipping_weight_mode (shipping_weight_mode),
    ADD INDEX package_weight_unit_of_measure_mode (package_weight_unit_of_measure_mode),
    ADD INDEX shipping_weight_unit_of_measure_mode (shipping_weight_unit_of_measure_mode);
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_template_new_product');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'item_package_quantity_mode') === false) {
    $connection->addColumn($tempTable, 'item_package_quantity_mode',
        'TINYINT(2) UNSIGNED DEFAULT 0 AFTER `worldwide_id_custom_attribute`');
}

if ($connection->tableColumnExists($tempTable, 'item_package_quantity_custom_value') === false) {
    $connection->addColumn($tempTable, 'item_package_quantity_custom_value',
        'VARCHAR(255) DEFAULT NULL AFTER `item_package_quantity_mode`');
}

if ($connection->tableColumnExists($tempTable, 'item_package_quantity_custom_attribute') === false) {
    $connection->addColumn($tempTable, 'item_package_quantity_custom_attribute',
        'VARCHAR(255) DEFAULT NULL AFTER `item_package_quantity_custom_value`');
}

if ($connection->tableColumnExists($tempTable, 'number_of_items_mode') === false) {
    $connection->addColumn($tempTable, 'number_of_items_mode',
        'TINYINT(2) UNSIGNED DEFAULT 0 AFTER `item_package_quantity_custom_attribute`');
}

if ($connection->tableColumnExists($tempTable, 'number_of_items_custom_value') === false) {
    $connection->addColumn($tempTable, 'number_of_items_custom_value',
        'VARCHAR(255) DEFAULT NULL AFTER `number_of_items_mode`');
}

if ($connection->tableColumnExists($tempTable, 'number_of_items_custom_attribute') === false) {
    $connection->addColumn($tempTable, 'number_of_items_custom_attribute',
        'VARCHAR(255) DEFAULT NULL AFTER `number_of_items_custom_value`');
}

if (!isset($tempTableIndexList[strtoupper('xsd_hash')])) {
    $connection->addKey($tempTable, 'xsd_hash', 'xsd_hash');
}

if (!isset($tempTableIndexList[strtoupper('node_title')])) {
    $connection->addKey($tempTable, 'node_title', 'node_title');
}

if (!isset($tempTableIndexList[strtoupper('category_path')])) {
    $connection->addKey($tempTable, 'category_path', 'category_path');
}

if (!isset($tempTableIndexList[strtoupper('worldwide_id_mode')])) {
    $connection->addKey($tempTable, 'worldwide_id_mode', 'worldwide_id_mode');
}

if (!isset($tempTableIndexList[strtoupper('item_package_quantity_mode')])) {
    $connection->addKey($tempTable, 'item_package_quantity_mode', 'item_package_quantity_mode');
}

if (!isset($tempTableIndexList[strtoupper('number_of_items_mode')])) {
    $connection->addKey($tempTable, 'number_of_items_mode', 'number_of_items_mode');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_template_new_product_description');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'package_weight_mode') === false) {
    $connection->addColumn($tempTable, 'package_weight_mode',
        'TINYINT(2) UNSIGNED DEFAULT 0 AFTER `manufacturer_part_number_custom_attribute`');
}

if ($connection->tableColumnExists($tempTable, 'package_weight_custom_value') === false) {
    $connection->addColumn($tempTable, 'package_weight_custom_value',
        'VARCHAR(255) DEFAULT NULL AFTER `package_weight_mode`');
}

if ($connection->tableColumnExists($tempTable, 'package_weight_custom_attribute') === false) {
    $connection->addColumn($tempTable, 'package_weight_custom_attribute',
        'VARCHAR(255) DEFAULT NULL AFTER `package_weight_custom_value`');
}

if ($connection->tableColumnExists($tempTable, 'package_weight_unit_of_measure_mode') === false) {
    $connection->addColumn($tempTable, 'package_weight_unit_of_measure_mode',
        'TINYINT(2) UNSIGNED DEFAULT 1 AFTER `package_weight_custom_attribute`');
}

if ($connection->tableColumnExists($tempTable, 'package_weight_unit_of_measure_custom_value') === false) {
    $connection->addColumn($tempTable, 'package_weight_unit_of_measure_custom_value',
        'VARCHAR(255) DEFAULT NULL AFTER `package_weight_unit_of_measure_mode`');
}

if ($connection->tableColumnExists($tempTable, 'package_weight_unit_of_measure_custom_attribute') === false) {
    $connection->addColumn($tempTable, 'package_weight_unit_of_measure_custom_attribute',
        'VARCHAR(255) DEFAULT NULL AFTER `package_weight_unit_of_measure_custom_value`');
}

if ($connection->tableColumnExists($tempTable, 'shipping_weight_mode') === false) {
    $connection->addColumn($tempTable, 'shipping_weight_mode',
        'TINYINT(2) UNSIGNED DEFAULT 0 AFTER `package_weight_custom_attribute`');
}

if ($connection->tableColumnExists($tempTable, 'shipping_weight_custom_value') === false) {
    $connection->addColumn($tempTable, 'shipping_weight_custom_value',
        'VARCHAR(255) DEFAULT NULL AFTER `shipping_weight_mode`');
}

if ($connection->tableColumnExists($tempTable, 'shipping_weight_custom_attribute') === false) {
    $connection->addColumn($tempTable, 'shipping_weight_custom_attribute',
        'VARCHAR(255) DEFAULT NULL AFTER `shipping_weight_custom_value`');
}

if ($connection->tableColumnExists($tempTable, 'shipping_weight_unit_of_measure_mode') === false) {
    $connection->addColumn($tempTable, 'shipping_weight_unit_of_measure_mode',
        'TINYINT(2) UNSIGNED DEFAULT 1 AFTER `shipping_weight_custom_attribute`');
}

if ($connection->tableColumnExists($tempTable, 'shipping_weight_unit_of_measure_custom_value') === false) {
    $connection->addColumn($tempTable, 'shipping_weight_unit_of_measure_custom_value',
        'VARCHAR(255) DEFAULT NULL AFTER `shipping_weight_unit_of_measure_mode`');
}

if ($connection->tableColumnExists($tempTable, 'shipping_weight_unit_of_measure_custom_attribute') === false) {
    $connection->addColumn($tempTable, 'shipping_weight_unit_of_measure_custom_attribute',
        'VARCHAR(255) DEFAULT NULL AFTER `shipping_weight_unit_of_measure_custom_value`');
}

if (!isset($tempTableIndexList[strtoupper('manufacturer_part_number_mode')])) {
    $connection->addKey($tempTable, 'manufacturer_part_number_mode', 'manufacturer_part_number_mode');
}

if (!isset($tempTableIndexList[strtoupper('target_audience_mode')])) {
    $connection->addKey($tempTable, 'target_audience_mode', 'target_audience_mode');
}

if (!isset($tempTableIndexList[strtoupper('search_terms_mode')])) {
    $connection->addKey($tempTable, 'search_terms_mode', 'search_terms_mode');
}

if (!isset($tempTableIndexList[strtoupper('package_weight_mode')])) {
    $connection->addKey($tempTable, 'package_weight_mode', 'package_weight_mode');
}

if (!isset($tempTableIndexList[strtoupper('shipping_weight_mode')])) {
    $connection->addKey($tempTable, 'shipping_weight_mode', 'shipping_weight_mode');
}

if (!isset($tempTableIndexList[strtoupper('package_weight_unit_of_measure_mode')])) {
    $connection->addKey($tempTable, 'package_weight_unit_of_measure_mode', 'package_weight_unit_of_measure_mode');
}

if (!isset($tempTableIndexList[strtoupper('shipping_weight_unit_of_measure_mode')])) {
    $connection->addKey($tempTable, 'shipping_weight_unit_of_measure_mode', 'shipping_weight_unit_of_measure_mode');
}

//#############################################

$installer->run(<<<SQL

DELETE FROM `m2epro_config`
WHERE `group` = '/cron/distribution/'
OR    `group` LIKE '/cache/license/%';

UPDATE `m2epro_config`
SET `group` = '/cron/task/servicing/'
WHERE `group` = '/cron/task/license/';

UPDATE `m2epro_config`
SET `value` = 'http://docs.m2epro.com/display/eBayAmazonRakutenMagentoV5/M2E+Pro'
WHERE `group` = '/documentation/'
AND `key` = 'baseurl';

UPDATE `m2epro_config`
SET `value` = 'http://docs.m2epro.com/display/eBayAmazonRakutenMagentoV5/Video+Tutorials'
WHERE `group` = '/video_tutorials/'
AND `key` = 'baseurl';

SQL
);

//#############################################

$installer->endSetup();

//#############################################