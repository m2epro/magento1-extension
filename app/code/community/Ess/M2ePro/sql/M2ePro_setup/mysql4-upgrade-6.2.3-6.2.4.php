<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE `m2epro_amazon_account`
    ADD COLUMN `token` VARCHAR(255) DEFAULT NULL after `merchant_id`;

    ALTER TABLE `m2epro_ebay_marketplace`
        ADD COLUMN `is_click_and_collect` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_charity`,
        ADD INDEX `is_click_and_collect` (`is_click_and_collect`);
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_account');
$columnName = 'token';

if ($connection->tableColumnExists($tempTable, $columnName) === false) {
    $connection->addColumn(
        $tempTable, $columnName, 'VARCHAR(255) DEFAULT NULL after `merchant_id`'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_marketplace');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'is_click_and_collect') === false) {
    $connection->addColumn(
        $tempTable,
        'is_click_and_collect',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_charity`'
    );
}

if (!isset($tempTableIndexList[strtoupper('is_click_and_collect')])) {
    $connection->addKey($tempTable, 'is_click_and_collect', 'is_click_and_collect');
}

//#############################################

$installer->run(<<<SQL

UPDATE `m2epro_ebay_marketplace`
SET `is_click_and_collect` = 1
WHERE `marketplace_id` = 3 OR -- UK --
      `marketplace_id` = 4;   -- AU --

SQL
);

//#############################################

$installer->endSetup();

//#############################################