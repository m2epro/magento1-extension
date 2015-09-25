<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE `m2epro_amazon_listing_product`
    ADD COLUMN `online_sale_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL AFTER `online_price`,
    ADD INDEX `online_sale_price` (`online_sale_price`);

    ALTER TABLE `m2epro_amazon_listing_product_variation`
    ADD COLUMN `online_sale_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL AFTER `online_price`,
    ADD INDEX `online_sale_price` (`online_sale_price`);
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_listing_product');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'online_sale_price') === false) {
    $connection->addColumn($tempTable, 'online_sale_price',
        'DECIMAL(12, 4) UNSIGNED DEFAULT NULL AFTER `online_price`');
}
if (!isset($tempTableIndexList[strtoupper('online_sale_price')])) {
    $connection->addKey($tempTable, 'online_sale_price', 'online_sale_price');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_listing_product_variation');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'online_sale_price') === false) {
    $connection->addColumn($tempTable, 'online_sale_price',
        'DECIMAL(12, 4) UNSIGNED DEFAULT NULL AFTER `online_price`');
}
if (!isset($tempTableIndexList[strtoupper('online_sale_price')])) {
    $connection->addKey($tempTable, 'online_sale_price', 'online_sale_price');
}

//#############################################

/*
    ALTER TABLE `m2epro_amazon_dictionary_specific`
    ADD COLUMN `recommended_values` TEXT NULL DEFAULT NULL AFTER `values`,
    ADD COLUMN `data_definition` TEXT NULL DEFAULT NULL AFTER `params`;

    ALTER TABLE `m2epro_amazon_category_description`
    CHANGE COLUMN `manufacturer_part_number_template` `manufacturer_part_number_custom_value` VARCHAR(255) NOT NULL,
    ADD COLUMN `manufacturer_part_number_custom_attribute` VARCHAR(255) NOT NULL
        AFTER `manufacturer_part_number_custom_value`,
    ADD COLUMN `target_audience_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0
        AFTER `manufacturer_part_number_custom_attribute`,
    ADD COLUMN `target_audience_custom_value` VARCHAR(255) NOT NULL AFTER `target_audience_mode`,
    ADD COLUMN `target_audience_custom_attribute` VARCHAR(255) NOT NULL AFTER `target_audience_custom_value`,
    ADD COLUMN `gallery_images_limit` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1 AFTER `gallery_images_mode`,
    ADD COLUMN `gallery_images_attribute` VARCHAR(255) NOT NULL AFTER `gallery_images_limit`;

    ALTER TABLE `m2epro_amazon_category_specific`
    ADD COLUMN `recommended_value` VARCHAR(255) DEFAULT NULL AFTER `mode`;
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_dictionary_specific');

if ($connection->tableColumnExists($tempTable, 'recommended_values') === false) {
    $connection->addColumn($tempTable, 'recommended_values', 'TEXT NULL DEFAULT NULL AFTER `values`');
}

if ($connection->tableColumnExists($tempTable, 'data_definition') === false) {
    $connection->addColumn($tempTable, 'data_definition', 'TEXT NULL DEFAULT NULL AFTER `params`');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_category_description');

if ($connection->tableColumnExists($tempTable, 'manufacturer_part_number_template') !== false &&
    $connection->tableColumnExists($tempTable, 'manufacturer_part_number_custom_value') === false) {

    $connection->changeColumn(
        $tempTable,
        'manufacturer_part_number_template',
        'manufacturer_part_number_custom_value',
        'VARCHAR(255) NOT NULL'
    );
}

if ($connection->tableColumnExists($tempTable, 'manufacturer_part_number_custom_attribute') === false) {
    $connection->addColumn(
        $tempTable,
        'manufacturer_part_number_custom_attribute',
        'VARCHAR(255) NOT NULL AFTER `manufacturer_part_number_custom_value`'
    );
}

if ($connection->tableColumnExists($tempTable, 'target_audience_mode') === false) {
    $connection->addColumn(
        $tempTable,
        'target_audience_mode',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `manufacturer_part_number_custom_value`'
    );
}

if ($connection->tableColumnExists($tempTable, 'target_audience_custom_value') === false) {
    $connection->addColumn(
        $tempTable,
        'target_audience_custom_value',
        'VARCHAR(255) NOT NULL AFTER `target_audience_mode`'
    );
}

if ($connection->tableColumnExists($tempTable, 'target_audience_custom_attribute') === false) {
    $connection->addColumn(
        $tempTable,
        'target_audience_custom_attribute',
        'VARCHAR(255) NOT NULL AFTER `target_audience_custom_value`'
    );
}

if ($connection->tableColumnExists($tempTable, 'gallery_images_limit') === false) {
    $connection->addColumn(
        $tempTable,
        'gallery_images_limit',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 1 AFTER `gallery_images_mode`'
    );
}

if ($connection->tableColumnExists($tempTable, 'gallery_images_attribute') === false) {
    $connection->addColumn(
        $tempTable,
        'gallery_images_attribute',
        'VARCHAR(255) NOT NULL AFTER `gallery_images_limit`'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_category_specific');

if ($connection->tableColumnExists($tempTable, 'recommended_value') === false) {
    $connection->addColumn($tempTable, 'recommended_value', 'VARCHAR(255) DEFAULT NULL AFTER `mode`');
}

//#############################################

$installer->run(<<<SQL

UPDATE `m2epro_config`
SET `value` = '1'
WHERE `group` = '/amazon/synchronization/settings/marketplaces/'
AND   `key` = 'mode';

SQL
);

//#############################################

$installer->run(<<<SQL

CREATE TABLE IF NOT EXISTS m2epro_translation_custom_suggestion (
  id SMALLINT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  language_code VARCHAR(10) NOT NULL,
  original_text TEXT NOT NULL,
  custom_text TEXT NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX language_code (language_code)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_translation_language (
  id SMALLINT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(10) NOT NULL,
  title VARCHAR(50) NOT NULL,
  need_synch TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX code (code),
  INDEX need_synch (need_synch),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_translation_text (
  id SMALLINT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  language_id SMALLINT(6) UNSIGNED NOT NULL,
  `group` VARCHAR(50) DEFAULT NULL,
  original_text TEXT NOT NULL,
  suggestions TEXT DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX `group` (`group`),
  INDEX language_id (language_id)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_config');
$tempRow = $connection->query("SELECT * FROM `{$tempTable}`
                               WHERE `group` = '/translation/synchronization/'
                               AND   `key` = 'last_access'")
    ->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/translation/synchronization/', 'last_access', NULL, NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/translation/synchronization/', 'interval', '604800', NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49');

SQL
    );
}

//#############################################

$installer->endSetup();

//#############################################