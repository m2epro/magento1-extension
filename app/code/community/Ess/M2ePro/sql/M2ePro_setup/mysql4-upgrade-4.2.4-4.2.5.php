<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//########################################

$installer->run(<<<SQL

ALTER TABLE `m2epro_amazon_dictionary_category` ENGINE = MYISAM;
ALTER TABLE `m2epro_amazon_dictionary_marketplace` ENGINE = MYISAM;
ALTER TABLE `m2epro_amazon_dictionary_specific` ENGINE = MYISAM;

CREATE TABLE IF NOT EXISTS `m2epro_ebay_motor_specific`(
  `epid` VARCHAR(255) NOT NULL,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `product_type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `make` VARCHAR(255) NOT NULL,
  `model` VARCHAR(255) NOT NULL,
  `year` SMALLINT(4) UNSIGNED NOT NULL,
  `trim` VARCHAR(255) DEFAULT NULL,
  `engine` VARCHAR(255) DEFAULT NULL,
  `submodel` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`epid`),
  INDEX `product_type` (`product_type`),
  INDEX `make` (`make`),
  INDEX `model` (`model`),
  INDEX `year` (`year`),
  INDEX `trim` (`trim`),
  INDEX `engine` (`engine`),
  INDEX `submodel` (`submodel`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

ALTER TABLE `m2epro_processing_request`
MODIFY COLUMN `component` VARCHAR(10) NOT NULL;

DELETE FROM `m2epro_product_change`;

SQL
);

//########################################

/*
    ALTER TABLE `m2epro_amazon_category`
    ADD COLUMN `template_description_id` INT(11) UNSIGNED NOT NULL after `marketplace_id`,
    ADD COLUMN `identifiers` VARCHAR(255) NOT NULL after `category_path`,
    DROP COLUMN `title`,
    ADD INDEX `template_description_id` (`template_description_id`);

    ALTER TABLE `m2epro_amazon_template_description`
    ADD COLUMN `manufacturer_part_number_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 after `manufacturer_template`,
    ADD COLUMN `manufacturer_part_number_template` VARCHAR(255) NOT NULL after `manufacturer_part_number_mode`,
    DROP COLUMN `editor_type`;

    ALTER TABLE `m2epro_listing_other_log`
    ADD COLUMN `identifier` VARCHAR(32) DEFAULT NULL after `listing_other_id`;

    ALTER TABLE `m2epro_ebay_template_description`
    ADD COLUMN `use_supersize_images` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 after `variation_configurable_images`,
    ADD COLUMN `gallery_images_limit` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1 after `gallery_images_mode`,
    ADD COLUMN `gallery_images_attribute` VARCHAR(255) NOT NULL after `gallery_images_limit`;

    ALTER TABLE `m2epro_ebay_listing_product`
    ADD COLUMN `is_m2epro_listed_item` TINYINT(2) UNSIGNED DEFAULT NULL after `additional_data`,
    ADD INDEX `is_m2epro_listed_item` (`is_m2epro_listed_item`);

    ALTER TABLE `m2epro_ebay_template_general`
    ADD COLUMN `motors_specifics_attribute` VARCHAR(255) DEFAULT NULL AFTER `condition_attribute`;

    ALTER TABLE `m2epro_product_change`
    ADD COLUMN `store_id` INT(11) UNSIGNED DEFAULT NULL after `product_id`,
    ADD INDEX `store_id` (`store_id`);

    ALTER TABLE `m2epro_ebay_template_selling_format`
    ADD COLUMN `customer_group_id` INT(11) UNSIGNED NOT NULL AFTER `best_offer_reject_attribute`,
    ADD INDEX `customer_group_id` (`customer_group_id`);

    ALTER TABLE `m2epro_amazon_template_selling_format`
    ADD COLUMN `customer_group_id` INT(11) UNSIGNED NOT NULL AFTER `sale_price_end_date_custom_attribute`,
    ADD INDEX `customer_group_id` (`customer_group_id`);
*/

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_category');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'template_description_id') === false) {
    $connection->addColumn($tempTable, 'template_description_id', 'INT(11) UNSIGNED NOT NULL after `marketplace_id`');
}

if ($connection->tableColumnExists($tempTable, 'identifiers') === false) {
    $connection->addColumn($tempTable, 'identifiers', 'VARCHAR(255) NOT NULL after `category_path`');
}

if ($connection->tableColumnExists($tempTable, 'title') === true) {
    $connection->dropColumn($tempTable, 'title');
}

if (!isset($tempTableIndexList[strtoupper('template_description_id')])) {
    $connection->addKey($tempTable, 'template_description_id', 'template_description_id');
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_template_description');

if ($connection->tableColumnExists($tempTable, 'editor_type') === true) {
    $connection->dropColumn($tempTable, 'editor_type');
}

if ($connection->tableColumnExists($tempTable, 'manufacturer_part_number_mode') === false) {
    $connection->addColumn(
        $tempTable,
        'manufacturer_part_number_mode',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 1 after `manufacturer_template`'
    );
}

if ($connection->tableColumnExists($tempTable, 'manufacturer_part_number_template') === false) {
    $connection->addColumn(
        $tempTable,
        'manufacturer_part_number_template',
        'VARCHAR(255) NOT NULL after `manufacturer_part_number_mode`'
    );
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_listing_other_log');

if ($connection->tableColumnExists($tempTable, 'identifier') === false) {
    $connection->addColumn($tempTable, 'identifier', 'VARCHAR(32) DEFAULT NULL after `listing_other_id`');
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_description');

if ($connection->tableColumnExists($tempTable, 'use_supersize_images') === false) {
    $connection->addColumn($tempTable, 'use_supersize_images',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 after `variation_configurable_images`');
}

if ($connection->tableColumnExists($tempTable, 'gallery_images_limit') === false) {
    $connection->addColumn($tempTable, 'gallery_images_limit',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 1 after `gallery_images_mode`');
}

if ($connection->tableColumnExists($tempTable, 'gallery_images_attribute') === false) {
    $connection->addColumn($tempTable, 'gallery_images_attribute',
        'VARCHAR(255) NOT NULL after `gallery_images_limit`');

    $installer->run(<<<SQL

UPDATE `m2epro_ebay_template_description`
SET `gallery_images_limit` = `gallery_images_mode`
WHERE `gallery_images_mode` != 0;

UPDATE `m2epro_ebay_template_description`
SET `gallery_images_mode` = 1
WHERE `gallery_images_mode` != 0;

SQL
);
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_listing_product');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'is_m2epro_listed_item') === false) {
    $connection->addColumn($tempTable, 'is_m2epro_listed_item',
        'TINYINT(2) UNSIGNED DEFAULT NULL after `additional_data`');
}

if (!isset($tempTableIndexList[strtoupper('is_m2epro_listed_item')])) {
    $connection->addKey($tempTable, 'is_m2epro_listed_item', 'is_m2epro_listed_item');
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_general');

if ($connection->tableColumnExists($tempTable, 'motors_specifics_attribute') === false) {
    $connection->addColumn($tempTable, 'motors_specifics_attribute',
        'VARCHAR(255) DEFAULT NULL AFTER `condition_attribute`');
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_product_change');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'store_id') === false) {
    $connection->addColumn($tempTable, 'store_id', 'INT(11) UNSIGNED DEFAULT NULL after `product_id`');
}

if (!isset($tempTableIndexList[strtoupper('store_id')])) {
    $connection->addKey($tempTable, 'store_id', 'store_id');
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_selling_format');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'customer_group_id') === false) {
    $connection->addColumn($tempTable, 'customer_group_id',
        'INT(11) UNSIGNED NOT NULL AFTER `best_offer_reject_attribute`');
}

if (!isset($tempTableIndexList[strtoupper('customer_group_id')])) {
    $connection->addKey($tempTable, 'customer_group_id', 'customer_group_id');
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_template_selling_format');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'customer_group_id') === false) {
    $connection->addColumn($tempTable, 'customer_group_id',
        'INT(11) UNSIGNED NOT NULL AFTER `sale_price_end_date_custom_attribute`');
}

if (!isset($tempTableIndexList[strtoupper('customer_group_id')])) {
    $connection->addKey($tempTable, 'customer_group_id', 'customer_group_id');
}

//########################################

$tempTable = $installer->getTable('m2epro_config');
$tempRow = $connection->query("SELECT * FROM `{$tempTable}`
                               WHERE `group` = '/amazon/synchronization/settings/defaults/update_listings_products/'
                               AND   `key` = 'existance_mode'")
                      ->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/amazon/synchronization/settings/defaults/update_listings_products/', 'existance_mode', '1',
'0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/cron/task/license/', 'mode', '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/cron/task/license/', 'interval', '3600', 'in seconds', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/cron/task/license/', 'last_access', NULL, 'date of last access', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/marketplaces/motors_specifics/', 'mode', '1', '0 - disable, \r\n1 - enable',
'2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/marketplaces/motors_specifics/', 'part_size',  '10000',
'amount of products per request', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/other_listing/source/', 'customer_group_id', '0', null,
'2012-05-21 10:47:49', '2012-05-21 10:47:49');

SQL
);
}

//########################################

$installer->run(<<<SQL

UPDATE `m2epro_config`
SET `group` = '/cron/distribution/'
WHERE `group` = '/synchronization/cron/distribution/';

UPDATE `m2epro_config`
SET `key` = 'min_execution_time'
WHERE `group` = '/cron/distribution/'
AND   `key` = 'max_execution_time';

UPDATE `m2epro_config`
SET `group` = '/cache/location_info/'
WHERE `group` = '/backups/';

UPDATE `m2epro_config`
SET `group` = '/cache/amazon/listing/',
    `key` = 'tutorial_shown'
WHERE `group` = '/cache/'
AND `key` = 'amazon_listing_tutorial_showed';

UPDATE `m2epro_config`
SET `value` = 'http://docs.m2epro.com/display/eBayAmazonMagentoV42/'
WHERE `group` = '/documentation/'
AND `key` = 'baseurl';

SQL
);

//########################################

$tempTable = $installer->getTable('m2epro_amazon_category');
$stmt = $connection->query("SELECT COUNT(*) FROM `{$tempTable}`");

if ($stmt->fetchColumn() == 0) {

    $installer->run(<<<SQL

    UPDATE `m2epro_amazon_listing_product`
    SET `category_id` = NULL;

    DELETE FROM `m2epro_amazon_category`;
    DELETE FROM `m2epro_amazon_category_specific`;
    DELETE FROM `m2epro_amazon_dictionary_category`;
    DELETE FROM `m2epro_amazon_dictionary_marketplace`;
    DELETE FROM `m2epro_amazon_dictionary_specific`;

SQL
);
}

//########################################

$tempTable = $installer->getTable('m2epro_amazon_account');
$tempAccounts = $connection->query("SELECT * FROM `{$tempTable}`")->fetchAll();

foreach ($tempAccounts as $account) {

    if (empty($account['magento_orders_settings'])) {
        continue;
    }

    $ordersSettings = (array)json_decode($account['magento_orders_settings'], true);

    if (count($ordersSettings) == 0 ||
        !isset($ordersSettings['listing']) ||
        !isset($ordersSettings['listing_other'])) {
        continue;
    }

    if ($ordersSettings['listing']['store_mode'] == 1 && $ordersSettings['listing']['store_id'] == 0) {
        // store mode == custom & store id == admin
        $ordersSettings['listing']['store_id'] = Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();
    }
    if ($ordersSettings['listing_other']['store_id'] == 0) {
        // store id == admin
        $ordersSettings['listing_other']['store_id'] = Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();
    }

    $ordersSettings['listing_other']['product_tax_class_id'] = 0;
    $ordersSettings['fba_stock_mode'] = 1; // manage stock yes
    $ordersSettings = $connection->quote(json_encode($ordersSettings));

    $connection->query(
        "UPDATE `{$tempTable}`
         SET `magento_orders_settings` = {$ordersSettings}
         WHERE `account_id` = ".(int)$account['account_id']
    );
}

//########################################

$tempTable = $installer->getTable('m2epro_ebay_account');
$tempAccounts = $connection->query("SELECT * FROM `{$tempTable}`")->fetchAll();

foreach ($tempAccounts as $account) {

    if (empty($account['magento_orders_settings'])) {
        continue;
    }

    $ordersSettings = (array)json_decode($account['magento_orders_settings'], true);

    if (count($ordersSettings) == 0 ||
        !isset($ordersSettings['listing']) ||
        !isset($ordersSettings['listing_other'])) {
        continue;
    }

    if ($ordersSettings['listing']['store_mode'] == 1 && $ordersSettings['listing']['store_id'] == 0) {
        // store mode == custom & store id == admin
        $ordersSettings['listing']['store_id'] = Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();
    }

    if ($ordersSettings['listing_other']['store_id'] == 0) {
        // store id == admin
        $ordersSettings['listing_other']['store_id'] = Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();
    }

    $ordersSettings['listing_other']['product_tax_class_id'] = 0;
    $ordersSettings = $connection->quote(json_encode($ordersSettings));

    $connection->query(
        "UPDATE `{$tempTable}`
         SET `magento_orders_settings` = {$ordersSettings}
         WHERE `account_id` = ".(int)$account['account_id']
    );
}

//########################################

$tempStatus = Mage::registry('M2EPRO_IS_INSTALLATION') === true ? '3' : '0';

$installer->run(<<<SQL

    DELETE FROM `m2epro_config`
    WHERE `group` = '/wizard/ebayPartsCompatibility/';

    INSERT INTO  `m2epro_config` (`group`, `key`, `value`, `notice`, `update_date`, `create_date`) VALUES
    ('/wizard/ebayPartsCompatibility/', 'priority', '5',
     '1 - highest', '2012-10-03 10:47:49', '2012-10-03 10:47:49'),
    ('/wizard/ebayPartsCompatibility/', 'step', NULL,
     NULL, '2012-10-15 18:00:15', '2012-10-03 10:47:49'),
    ('/wizard/ebayPartsCompatibility/', 'status', '{$tempStatus}',
     '0 - Not Started, 1 - Active, 2 - Completed, 3 - Skipped', '2012-10-15 18:00:15', '2012-10-03 10:47:49');
SQL
);

//########################################

$installer->endSetup();

//########################################