<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE `m2epro_ebay_order`
    ADD COLUMN `global_shipping_details` TEXT DEFAULT NULL AFTER `shipping_date`;

    ALTER TABLE `m2epro_ebay_marketplace`
    ADD COLUMN `is_global_shipping_program` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_cash_on_delivery`;

    ALTER TABLE `m2epro_ebay_template_shipping`
    ADD COLUMN `global_shipping_program` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `international_trade`;
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_order');

if ($connection->tableColumnExists($tempTable, 'global_shipping_details') === false) {
    $connection->addColumn(
        $tempTable,
        'global_shipping_details',
        'TEXT DEFAULT NULL AFTER `shipping_date`'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_marketplace');

if ($connection->tableColumnExists($tempTable, 'is_global_shipping_program') === false) {
    $connection->addColumn(
        $tempTable,
        'is_global_shipping_program',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_cash_on_delivery`'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_shipping');

if ($connection->tableColumnExists($tempTable, 'global_shipping_program') === false) {
    $connection->addColumn(
        $tempTable,
        'global_shipping_program',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `international_trade`'
    );
}

//#############################################

$installer->run(<<<SQL

UPDATE `m2epro_ebay_marketplace`
SET `is_global_shipping_program` = 1
WHERE `marketplace_id` = '1';

SQL
);

//#############################################

$installer->endSetup();

//#############################################