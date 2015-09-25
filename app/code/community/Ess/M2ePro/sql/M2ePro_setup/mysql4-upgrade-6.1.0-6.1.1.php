<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE `m2epro_ebay_marketplace`
    ADD COLUMN `is_map` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_stp_advanced`,
    ADD INDEX `is_map` (`is_map`);

    ALTER TABLE `m2epro_ebay_template_selling_format`
    ADD COLUMN `price_discount_map_mode` TINYINT(2) UNSIGNED NOT NULL AFTER `price_discount_stp_type`,
    ADD COLUMN `price_discount_map_attribute` VARCHAR(255) NOT NULL AFTER `price_discount_map_mode`,
    ADD COLUMN `price_discount_map_exposure_type` TINYINT(2) UNSIGNED NOT NULL AFTER `price_discount_map_attribute`;

    ALTER TABLE `m2epro_amazon_listing_product`
    ADD COLUMN `ignore_next_inventory_synch` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_upc_worldwide_id`,
    ADD INDEX `ignore_next_inventory_synch` (`ignore_next_inventory_synch`);
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_marketplace');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'is_map') === false) {
    $connection->addColumn(
        $tempTable,
        'is_map',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_stp_advanced`'
    );
}

if (!isset($tempTableIndexList[strtoupper('is_map')])) {
    $connection->addKey($tempTable, 'is_map', 'is_map');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_selling_format');

if ($connection->tableColumnExists($tempTable, 'price_discount_map_mode') === false) {
    $connection->addColumn(
        $tempTable,
        'price_discount_map_mode',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `price_discount_stp_type`'
    );
}

if ($connection->tableColumnExists($tempTable, 'price_discount_map_attribute') === false) {
    $connection->addColumn(
        $tempTable,
        'price_discount_map_attribute',
        'VARCHAR(255) NOT NULL AFTER `price_discount_map_mode`'
    );
}

if ($connection->tableColumnExists($tempTable, 'price_discount_map_exposure_type') === false) {
    $connection->addColumn(
        $tempTable,
        'price_discount_map_exposure_type',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `price_discount_map_attribute`'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_listing_product');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'ignore_next_inventory_synch') === false) {
    $connection->addColumn(
        $tempTable,
        'ignore_next_inventory_synch',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_upc_worldwide_id`');
}

if (!isset($tempTableIndexList[strtoupper('ignore_next_inventory_synch')])) {
    $connection->addKey($tempTable, 'ignore_next_inventory_synch', 'ignore_next_inventory_synch');
}

//#############################################

$tempTable = $installer->getTable('m2epro_primary_config');
$tempQuery = <<<SQL
    SELECT * FROM `{$tempTable}`
    WHERE `group` = '/M2ePro/license/valid/'
    AND   `key` = 'domain'
SQL;
$tempRow = $connection->query($tempQuery)->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO m2epro_primary_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  ('/M2ePro/license/valid/', 'domain', NULL, '0 - Not valid\r\n1 - Valid', '2013-05-08 00:00:00','2013-05-08 00:00:00'),
  ('/M2ePro/license/valid/', 'ip', NULL, '0 - Not valid\r\n1 - Valid', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/license/valid/', 'directory', NULL, '0 - Not valid\r\n1 - Valid',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00');

SQL
);
}

$installer->run(<<<SQL

DELETE FROM `m2epro_config`
WHERE `group` = '/license/validation/directory/notification/' OR
      `group` = '/license/validation/domain/notification/' OR
      `group` = '/license/validation/ip/notification/';

SQL
);

//#############################################

$installer->run(<<<SQL

UPDATE `m2epro_ebay_marketplace`
SET `is_charity` = 1
WHERE `marketplace_id` IN (2, 4, 5, 6, 7, 8, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23);

UPDATE `m2epro_ebay_marketplace`
SET `is_stp` = 1
WHERE `marketplace_id` = 4;

UPDATE `m2epro_ebay_marketplace`
SET `is_map` = 1
WHERE `marketplace_id` = 1;

SQL
);

//#############################################

/** @var Ess_M2ePro_Model_Upgrade_Migration_ToVersion611 $migrationInstance */
$migrationInstance = Mage::getModel('M2ePro/Upgrade_Migration_ToVersion611');
$migrationInstance->setInstaller($installer);
$migrationInstance->migrate();

//#############################################

$installer->endSetup();

//#############################################