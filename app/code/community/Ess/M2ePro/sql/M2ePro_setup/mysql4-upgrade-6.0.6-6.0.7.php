<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE `m2epro_listing_other`
    ADD COLUMN `additional_data` TEXT DEFAULT NULL AFTER `component_mode`;

    ALTER TABLE `m2epro_buy_template_new_product_core`
    ADD COLUMN `gtin_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 2 AFTER `seller_sku_custom_attribute`;

    ALTER TABLE `m2epro_ebay_template_description`
    ADD COLUMN `default_image_url` VARCHAR(255) DEFAULT NULL AFTER `gallery_images_attribute`;

    ALTER TABLE `m2epro_ebay_template_shipping`
    CHANGE COLUMN `dispatch_time` `dispatch_time_value` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    ADD COLUMN `dispatch_time_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER vat_percent,
    ADD COLUMN `dispatch_time_attribute` VARCHAR(255) NOT NULL AFTER `dispatch_time_value`;
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_listing_other');

if ($connection->tableColumnExists($tempTable, 'additional_data') === false) {
    $connection->addColumn(
        $tempTable,
        'additional_data',
        'TEXT DEFAULT NULL AFTER `component_mode`'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_buy_template_new_product_core');

if ($connection->tableColumnExists($tempTable, 'gtin_mode') === false) {
    $connection->addColumn(
        $tempTable,
        'gtin_mode',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 2 AFTER `seller_sku_custom_attribute`'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_description');

if ($connection->tableColumnExists($tempTable, 'default_image_url') === false) {
    $connection->addColumn(
        $tempTable,
        'default_image_url',
        'VARCHAR(255) DEFAULT NULL AFTER `gallery_images_attribute`'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_shipping');

if ($connection->tableColumnExists($tempTable, 'dispatch_time') !== false &&
    $connection->tableColumnExists($tempTable, 'dispatch_time_value') === false) {
    $connection->changeColumn($tempTable,'dispatch_time','dispatch_time_value', 'INT(11) UNSIGNED NOT NULL DEFAULT 0');
}

if ($connection->tableColumnExists($tempTable, 'dispatch_time_mode') === false) {
    $connection->addColumn(
        $tempTable,
        'dispatch_time_mode',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `vat_percent`'
    );
}

if ($connection->tableColumnExists($tempTable, 'dispatch_time_attribute') === false) {
    $connection->addColumn(
        $tempTable,
        'dispatch_time_attribute',
        'VARCHAR(255) NOT NULL AFTER `dispatch_time_value`'
    );
}

//#############################################

$tempTable = $installer->getTable('m2epro_config');
$tempQuery = <<<SQL
    SELECT * FROM `{$tempTable}`
    WHERE `group` = '/buy/template/new_sku/'
    AND   `key` = 'upc_exemption'
SQL;
$tempRow = $connection->query($tempQuery)->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/buy/template/new_sku/', 'upc_exemption', '0', '0 - disable\r\n1 - enable',
 '2013-09-18 00:00:00', '2013-09-18 00:00:00'),
('/view/synchronization/revise_total/', 'show', '0', '0 - disable, \r\n1 - enable',
 '2013-05-08 00:00:00', '2013-05-08 00:00:00');

SQL
);
}

//#############################################

$tempTable = $installer->getTable('m2epro_synchronization_config');
$tempQuery = <<<SQL
    SELECT * FROM `{$tempTable}`
    WHERE `group` = '/ebay/templates/revise/total/'
    AND   `key` = 'last_listing_product_id'
SQL;
$tempRow = $connection->query($tempQuery)->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO `m2epro_synchronization_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/ebay/templates/revise/total/', 'last_listing_product_id', NULL, NULL,
 '2013-09-18 00:00:00', '2013-09-18 00:00:00'),
('/ebay/templates/revise/total/', 'start_date', NULL, NULL,
 '2013-09-18 00:00:00', '2013-09-18 00:00:00'),
('/ebay/templates/revise/total/', 'end_date', NULL, NULL,
 '2013-09-18 00:00:00', '2013-09-18 00:00:00'),

('/amazon/templates/revise/total/', 'last_listing_product_id', NULL, NULL,
 '2013-09-18 00:00:00', '2013-09-18 00:00:00'),
('/amazon/templates/revise/total/', 'start_date', NULL, NULL,
 '2013-09-18 00:00:00', '2013-09-18 00:00:00'),
('/amazon/templates/revise/total/', 'end_date', NULL, NULL,
 '2013-09-18 00:00:00', '2013-09-18 00:00:00'),

('/buy/templates/revise/total/', 'last_listing_product_id', NULL, NULL,
 '2013-09-18 00:00:00', '2013-09-18 00:00:00'),
('/buy/templates/revise/total/', 'start_date', NULL, NULL,
 '2013-09-18 00:00:00', '2013-09-18 00:00:00'),
('/buy/templates/revise/total/', 'end_date', NULL, NULL,
 '2013-09-18 00:00:00', '2013-09-18 00:00:00'),

('/play/templates/revise/total/', 'last_listing_product_id', NULL, NULL,
 '2013-09-18 00:00:00', '2013-09-18 00:00:00'),
('/play/templates/revise/total/', 'start_date', NULL, NULL,
 '2013-09-18 00:00:00', '2013-09-18 00:00:00'),
('/play/templates/revise/total/', 'end_date', NULL, NULL,
 '2013-09-18 00:00:00', '2013-09-18 00:00:00'),

('/defaults/inspector/auto_actions/', 'last_magento_product_id', NULL, NULL,
 '2013-09-18 00:00:00', '2013-09-18 00:00:00'),

('/ebay/defaults/remove_duplicates/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2012-05-21 10:47:49', '2012-05-21 10:47:49');

SQL
);
}

//#############################################

$tempTable = $installer->getTable('m2epro_synchronization_config');
$oldData = $connection->query("
SELECT *
FROM `{$tempTable}`
WHERE `group` = '/defaults/inspector/circle/' AND `key` = 'mode'
")->fetch();

if ($oldData !== false && isset($oldData['value'])) {

    $installer->run(<<<SQL

UPDATE `m2epro_synchronization_config`
SET `value` = '{$oldData['value']}', `notice` = '0 - disable, \r\n1 - enable'
WHERE `group` = '/defaults/inspector/' AND `key` = 'mode';

UPDATE `m2epro_synchronization_config`
SET `group` = '/defaults/inspector/product_changes/circle/'
WHERE `group` = '/defaults/inspector/circle/';

UPDATE `m2epro_synchronization_config`
SET `value` = 'circle', `notice` = 'circle', `group` = '/defaults/inspector/product_changes/'
WHERE `group` = '/defaults/inspector/product_changes/circle/' AND `key` = 'mode';

SQL
);
}

//#############################################

$tempTable = $installer->getTable('m2epro_cache_config');

$oldData = $connection->query("
SELECT `group`, `key`, `value`
FROM `{$tempTable}`
WHERE `group` = '/ebay/category/recent/ebay/main/' OR
      `group` = '/ebay/category/recent/ebay/secondary/' OR
      `group` = '/ebay/category/recent/store/main/' OR
      `group` = '/ebay/category/recent/store/secondary/'
")->fetchAll();

foreach ($oldData as $data) {

    if (empty($data['value'])) {
        continue;
    }

    if (strpos($data['value'], '{') !== 0) {
        continue;
    }

    $newValue = '';
    $recentCategories = json_decode($data['value'], true);
    if (!empty($recentCategories)) {
        $ids = (array)array_keys($recentCategories);
        $newValue = implode(',', $ids);
    }

    $newValueMySql = $connection->quote($newValue);
    $groupMySql = $connection->quote($data['group']);
    $keyMySql = $connection->quote($data['key']);

    $installer->run(<<<SQL

UPDATE `m2epro_cache_config`
SET `value` = {$newValueMySql}
WHERE `group` = {$groupMySql} AND
      `key` = {$keyMySql}

SQL
);
}

//#############################################

$installer->run(<<<SQL

UPDATE `m2epro_ebay_marketplace`
SET `is_local_shipping_rate_table` = 1
WHERE `marketplace_id` = 4;

UPDATE `m2epro_listing`
SET `additional_data` = REPLACE(`additional_data`,'category_settings_mode','ebay_category_settings_mode');

SQL
);

//#############################################

if (Mage::registry('M2EPRO_IS_INSTALLATION') === true) {

    $installer->run(<<<SQL

UPDATE `m2epro_config`
SET `value` = '1'
WHERE `group` = '/component/amazon/'
AND   `key` = 'mode';

UPDATE `m2epro_config`
SET `value` = '1'
WHERE `group` = '/component/buy/'
AND   `key` = 'mode';

UPDATE `m2epro_config`
SET `value` = '1'
WHERE `group` = '/component/play/'
AND   `key` = 'mode';

SQL
);
}

//#############################################

$installer->endSetup();

//#############################################