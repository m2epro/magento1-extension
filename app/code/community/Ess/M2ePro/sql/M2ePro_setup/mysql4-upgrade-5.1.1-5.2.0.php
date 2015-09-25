<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE `m2epro_amazon_listing_product`
    ADD COLUMN `additional_data` TEXT DEFAULT NULL AFTER `end_date`,
    ADD COLUMN `is_variation_product` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `template_new_product_id`,
    ADD COLUMN `is_variation_matched` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_variation_product`,
    ADD INDEX `is_variation_product` (`is_variation_product`),
    ADD INDEX `is_variation_matched` (`is_variation_matched`);

    ALTER TABLE `m2epro_buy_listing_product`
    ADD COLUMN `additional_data` TEXT DEFAULT NULL AFTER `end_date`,
    ADD COLUMN `is_variation_product` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `template_new_product_id`,
    ADD COLUMN `is_variation_matched` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_variation_product`,
    ADD INDEX `is_variation_product` (`is_variation_product`),
    ADD INDEX `is_variation_matched` (`is_variation_matched`);

    ALTER TABLE `m2epro_play_listing_product`
    ADD COLUMN `additional_data` TEXT DEFAULT NULL AFTER `end_date`,
    ADD COLUMN `is_variation_product` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `general_id_type`,
    ADD COLUMN `is_variation_matched` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_variation_product`,
    ADD INDEX `is_variation_product` (`is_variation_product`),
    ADD INDEX `is_variation_matched` (`is_variation_matched`);

    ALTER TABLE `m2epro_amazon_template_general`
    ADD COLUMN `generate_sku_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `sku_custom_attribute`,
    ADD INDEX `generate_sku_mode` (`generate_sku_mode`);

    ALTER TABLE `m2epro_buy_template_general`
    ADD COLUMN `generate_sku_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `sku_custom_attribute`,
    ADD INDEX `generate_sku_mode` (`generate_sku_mode`);

    ALTER TABLE `m2epro_play_template_general`
    ADD COLUMN `generate_sku_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `sku_custom_attribute`,
    ADD INDEX `generate_sku_mode` (`generate_sku_mode`);

    ALTER TABLE `m2epro_amazon_listing_product_variation`
    DROP COLUMN `online_price`,
    DROP COLUMN `online_sale_price`,
    DROP COLUMN `online_qty`;

    ALTER TABLE `m2epro_amazon_template_selling_format`
    ADD COLUMN `price_variation_mode` TINYINT(2) UNSIGNED NOT NULL after `sale_price_coefficient`,
    ADD INDEX `price_variation_mode` (`price_variation_mode`);

    UPDATE `m2epro_amazon_template_selling_format`
    SET `price_variation_mode` = 1;

    ALTER TABLE `m2epro_buy_template_selling_format`
    ADD COLUMN `price_variation_mode` TINYINT(2) UNSIGNED NOT NULL after `price_coefficient`,
    ADD INDEX `price_variation_mode` (`price_variation_mode`);

    UPDATE `m2epro_buy_template_selling_format`
    SET `price_variation_mode` = 1;

    ALTER TABLE `m2epro_play_template_selling_format`
    ADD COLUMN `price_variation_mode` TINYINT(2) UNSIGNED NOT NULL after `price_euro_coefficient`,
    ADD INDEX `price_variation_mode` (`price_variation_mode`);

    UPDATE `m2epro_play_template_selling_format`
    SET `price_variation_mode` = 1;

    ALTER TABLE `m2epro_amazon_item`
    ADD COLUMN `variation_options` TEXT DEFAULT NULL after `store_id`;

    ALTER TABLE `m2epro_buy_item`
    ADD COLUMN `variation_options` TEXT DEFAULT NULL after `store_id`;

    ALTER TABLE `m2epro_play_item`
    ADD COLUMN `variation_options` TEXT DEFAULT NULL after `store_id`;
*/

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_listing_product');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'additional_data') === false) {
    $connection->addColumn(
        $tempTable,
        'additional_data',
        'TEXT DEFAULT NULL AFTER `end_date`'
    );
}

if ($connection->tableColumnExists($tempTable, 'is_variation_product') === false) {
    $connection->addColumn(
        $tempTable,
        'is_variation_product',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `template_new_product_id`'
    );
}

if ($connection->tableColumnExists($tempTable, 'is_variation_matched') === false) {
    $connection->addColumn(
        $tempTable,
        'is_variation_matched',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_variation_product`'
    );
}

if (!isset($tempTableIndexList[strtoupper('is_variation_product')])) {
    $connection->addKey($tempTable, 'is_variation_product', 'is_variation_product');
}

if (!isset($tempTableIndexList[strtoupper('is_variation_matched')])) {
    $connection->addKey($tempTable, 'is_variation_matched', 'is_variation_matched');
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_buy_listing_product');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'additional_data') === false) {
    $connection->addColumn(
        $tempTable,
        'additional_data',
        'TEXT DEFAULT NULL AFTER `end_date`'
    );
}

if ($connection->tableColumnExists($tempTable, 'is_variation_product') === false) {
    $connection->addColumn(
        $tempTable,
        'is_variation_product',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `template_new_product_id`'
    );
}

if ($connection->tableColumnExists($tempTable, 'is_variation_matched') === false) {
    $connection->addColumn(
        $tempTable,
        'is_variation_matched',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_variation_product`'
    );
}

if (!isset($tempTableIndexList[strtoupper('is_variation_product')])) {
    $connection->addKey($tempTable, 'is_variation_product', 'is_variation_product');
}

if (!isset($tempTableIndexList[strtoupper('is_variation_matched')])) {
    $connection->addKey($tempTable, 'is_variation_matched', 'is_variation_matched');
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_play_listing_product');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'additional_data') === false) {
    $connection->addColumn(
        $tempTable,
        'additional_data',
        'TEXT DEFAULT NULL AFTER `end_date`'
    );
}

if ($connection->tableColumnExists($tempTable, 'is_variation_product') === false) {
    $connection->addColumn(
        $tempTable,
        'is_variation_product',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `general_id_type`'
    );
}

if ($connection->tableColumnExists($tempTable, 'is_variation_matched') === false) {
    $connection->addColumn(
        $tempTable,
        'is_variation_matched',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_variation_product`'
    );
}

if (!isset($tempTableIndexList[strtoupper('is_variation_product')])) {
    $connection->addKey($tempTable, 'is_variation_product', 'is_variation_product');
}

if (!isset($tempTableIndexList[strtoupper('is_variation_matched')])) {
    $connection->addKey($tempTable, 'is_variation_matched', 'is_variation_matched');
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_template_general');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'generate_sku_mode') === false) {
    $connection->addColumn(
        $tempTable,
        'generate_sku_mode',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `sku_custom_attribute`'
    );
}

if (!isset($tempTableIndexList[strtoupper('generate_sku_mode')])) {
    $connection->addKey($tempTable, 'generate_sku_mode', 'generate_sku_mode');
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_buy_template_general');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'generate_sku_mode') === false) {
    $connection->addColumn(
        $tempTable,
        'generate_sku_mode',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `sku_custom_attribute`'
    );
}

if (!isset($tempTableIndexList[strtoupper('generate_sku_mode')])) {
    $connection->addKey($tempTable, 'generate_sku_mode', 'generate_sku_mode');
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_play_template_general');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'generate_sku_mode') === false) {
    $connection->addColumn(
        $tempTable,
        'generate_sku_mode',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `sku_custom_attribute`'
    );
}

if (!isset($tempTableIndexList[strtoupper('generate_sku_mode')])) {
    $connection->addKey($tempTable, 'generate_sku_mode', 'generate_sku_mode');
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_listing_product_variation');

if ($connection->tableColumnExists($tempTable, 'online_price') === true) {
    $connection->dropColumn($tempTable, 'online_price');
}

if ($connection->tableColumnExists($tempTable, 'online_sale_price') === true) {
    $connection->dropColumn($tempTable, 'online_sale_price');
}

if ($connection->tableColumnExists($tempTable, 'online_qty') === true) {
    $connection->dropColumn($tempTable, 'online_qty');
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_template_selling_format');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'price_variation_mode') === false) {
    $connection->addColumn(
        $tempTable,
        'price_variation_mode',
        'TINYINT(2) UNSIGNED NOT NULL after `sale_price_coefficient`'
    );
}

if (!isset($tempTableIndexList[strtoupper('price_variation_mode')])) {
    $connection->addKey($tempTable, 'price_variation_mode', 'price_variation_mode');
}

$installer->run(<<<SQL
  UPDATE `m2epro_amazon_template_selling_format`
  SET `price_variation_mode` = 1;
SQL
);

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_buy_template_selling_format');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'price_variation_mode') === false) {
    $connection->addColumn(
        $tempTable,
        'price_variation_mode',
        'TINYINT(2) UNSIGNED NOT NULL after `price_coefficient`'
    );
}

if (!isset($tempTableIndexList[strtoupper('price_variation_mode')])) {
    $connection->addKey($tempTable, 'price_variation_mode', 'price_variation_mode');
}

$installer->run(<<<SQL
  UPDATE `m2epro_buy_template_selling_format`
  SET `price_variation_mode` = 1;
SQL
);

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_play_template_selling_format');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'price_variation_mode') === false) {
    $connection->addColumn(
        $tempTable,
        'price_variation_mode',
        'TINYINT(2) UNSIGNED NOT NULL after `price_euro_coefficient`'
    );
}

if (!isset($tempTableIndexList[strtoupper('price_variation_mode')])) {
    $connection->addKey($tempTable, 'price_variation_mode', 'price_variation_mode');
}

$installer->run(<<<SQL
  UPDATE `m2epro_play_template_selling_format`
  SET `price_variation_mode` = 1;
SQL
);

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_item');

if ($connection->tableColumnExists($tempTable, 'variation_options') === false) {
    $connection->addColumn(
        $tempTable,
        'variation_options',
        'TEXT DEFAULT NULL after `store_id`'
    );
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_buy_item');

if ($connection->tableColumnExists($tempTable, 'variation_options') === false) {
    $connection->addColumn(
        $tempTable,
        'variation_options',
        'TEXT DEFAULT NULL after `store_id`'
    );
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_play_item');

if ($connection->tableColumnExists($tempTable, 'variation_options') === false) {
    $connection->addColumn(
        $tempTable,
        'variation_options',
        'TEXT DEFAULT NULL after `store_id`'
    );
}

//#############################################

$installer->endSetup();

//#############################################