<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE `m2epro_amazon_template_new_product`
    ADD COLUMN `worldwide_id_mode` TINYINT(2) UNSIGNED DEFAULT 0 AFTER `registered_parameter`,
    ADD COLUMN `worldwide_id_custom_attribute` VARCHAR(255) DEFAULT NULL AFTER `worldwide_id_mode`;

    ALTER TABLE `m2epro_buy_dictionary_category`
    ADD COLUMN `node_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
    DROP INDEX `parent_id`,
    ADD INDEX `parent_id` (`parent_id`),
    ADD INDEX `node_id` (`node_id`);
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_template_new_product');

if ($connection->tableColumnExists($tempTable, 'worldwide_id_mode') === false) {
    $connection->addColumn($tempTable, 'worldwide_id_mode',
        'TINYINT(2) UNSIGNED DEFAULT 0 AFTER `registered_parameter`');
}

if ($connection->tableColumnExists($tempTable, 'worldwide_id_custom_attribute') === false) {
    $connection->addColumn($tempTable, 'worldwide_id_custom_attribute',
        'VARCHAR(255) DEFAULT NULL AFTER `worldwide_id_mode`');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_buy_dictionary_category');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'node_id') === false) {
    $connection->addColumn($tempTable, 'node_id', 'INT(11) UNSIGNED NOT NULL AFTER `id`');
}

if (isset($tempTableIndexList[strtoupper('parent_id')])) {
    $connection->dropKey($tempTable, 'parent_id');
}

$tempTableIndexList = $connection->getIndexList($tempTable);

if (!isset($tempTableIndexList[strtoupper('parent_id')])) {
    $connection->addKey($tempTable, 'parent_id', 'parent_id');
}

if (!isset($tempTableIndexList[strtoupper('node_id')])) {
    $connection->addKey($tempTable, 'node_id', 'node_id');
}

//#############################################

$installer->run(<<<SQL

CREATE TABLE IF NOT EXISTS m2epro_buy_template_new_product (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id INT(11) UNSIGNED NOT NULL,
  node_title VARCHAR(255) NOT NULL,
  category_path VARCHAR(255) NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX category_id (category_id),
  INDEX category_path (category_path),
  INDEX node_title (node_title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_buy_template_new_product_attribute(
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  template_new_product_id INT(11) UNSIGNED NOT NULL,
  attribute_name VARCHAR(255) NOT NULL,
  mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  recommended_value TEXT DEFAULT NULL,
  custom_value TEXT DEFAULT NULL,
  custom_attribute VARCHAR(255) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX attribute_name (attribute_name),
  INDEX mode (mode),
  INDEX template_new_product_id (template_new_product_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_buy_template_new_product_core(
  template_new_product_id INT(11) UNSIGNED NOT NULL,
  seller_sku_mode TINYINT(2) UNSIGNED NOT NULL,
  seller_sku_custom_value VARCHAR(255) DEFAULT NULL,
  seller_sku_custom_attribute VARCHAR(255) DEFAULT NULL,
  gtin_mode TINYINT(2) UNSIGNED NOT NULL,
  gtin_custom_value VARCHAR(255) DEFAULT NULL,
  gtin_custom_attribute VARCHAR(255) DEFAULT NULL,
  isbn_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  isbn_custom_value VARCHAR(255) DEFAULT NULL,
  isbn_custom_attribute VARCHAR(255) DEFAULT NULL,
  asin_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  asin_custom_value VARCHAR(255) DEFAULT NULL,
  asin_custom_attribute VARCHAR(255) DEFAULT NULL,
  mfg_name_template VARCHAR(255) NOT NULL,
  mfg_part_number_mode TINYINT(2) UNSIGNED NOT NULL,
  mfg_part_number_custom_value VARCHAR(255) DEFAULT NULL,
  mfg_part_number_custom_attribute VARCHAR(255) DEFAULT NULL,
  product_set_id_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  product_set_id_custom_value VARCHAR(255) DEFAULT NULL,
  product_set_id_custom_attribute VARCHAR(255) DEFAULT NULL,
  title_mode TINYINT(2) UNSIGNED NOT NULL,
  title_template VARCHAR(255) NOT NULL,
  description_mode TINYINT(2) UNSIGNED NOT NULL,
  description_template TEXT NOT NULL,
  main_image_mode TINYINT(2) UNSIGNED NOT NULL,
  main_image_attribute VARCHAR(255) NOT NULL,
  additional_images_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  additional_images_attribute VARCHAR(255) DEFAULT NULL,
  additional_images_limit INT(11) UNSIGNED DEFAULT NULL,
  keywords_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  keywords_custom_value VARCHAR(255) DEFAULT NULL,
  keywords_custom_attribute VARCHAR(255) DEFAULT NULL,
  features_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  features_template TEXT DEFAULT NULL,
  weight_mode TINYINT(4) DEFAULT NULL,
  weight_custom_value DECIMAL(10, 0) UNSIGNED DEFAULT NULL,
  weight_custom_attribute VARCHAR(255) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (template_new_product_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

//#############################################

$installer->endSetup();

//#############################################