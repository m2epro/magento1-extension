<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE `m2epro_listing_product`
    CHANGE COLUMN `is_need_synchronize` `synch_status` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `tried_to_list`,
    DROP INDEX `is_need_synchronize`,
    ADD INDEX `synch_status` (`synch_status`);

    ALTER TABLE `m2epro_ebay_template_selling_format`
    ADD COLUMN `ignore_variations` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `charity`;
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_listing_product');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'is_need_synchronize') !== false &&
    $connection->tableColumnExists($tempTable, 'synch_status') === false) {

    $connection->changeColumn(
        $tempTable,
        'is_need_synchronize',
        'synch_status',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `tried_to_list`'
    );
}

if (isset($tempTableIndexList[strtoupper('is_need_synchronize')])) {
    $connection->dropKey($tempTable, 'is_need_synchronize');
}

$tempTableIndexList = $connection->getIndexList($tempTable);

if (!isset($tempTableIndexList[strtoupper('synch_status')])) {
    $connection->addKey($tempTable, 'synch_status', 'synch_status');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_selling_format');

if ($connection->tableColumnExists($tempTable, 'ignore_variations') === false) {
    $connection->addColumn(
        $tempTable,
        'ignore_variations',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `charity`'
    );
}

//#############################################

$installer->run(<<<SQL

UPDATE `m2epro_marketplace`
SET `sorder` = 1
WHERE `id` = 1;

UPDATE `m2epro_marketplace`
SET `sorder` = 8
WHERE `id` = 2;

UPDATE `m2epro_marketplace`
SET `sorder` = 2
WHERE `id` = 3;

UPDATE `m2epro_marketplace`
SET `sorder` = 4
WHERE `id` = 4;

UPDATE `m2epro_marketplace`
SET `sorder` = 5
WHERE `id` = 5;

UPDATE `m2epro_marketplace`
SET `sorder` = 7
WHERE `id` = 6;

UPDATE `m2epro_marketplace`
SET `sorder` = 10
WHERE `id` = 7;

UPDATE `m2epro_marketplace`
SET `sorder` = 3
WHERE `id` = 8;

UPDATE `m2epro_marketplace`
SET `sorder` = 23
WHERE `id` = 9;

UPDATE `m2epro_marketplace`
SET `sorder` = 14
WHERE `id` = 10;

UPDATE `m2epro_marketplace`
SET `sorder` = 6
WHERE `id` = 11;

UPDATE `m2epro_marketplace`
SET `sorder` = 16
WHERE `id` = 12;

UPDATE `m2epro_marketplace`
SET `sorder` = 19
WHERE `id` = 13;

UPDATE `m2epro_marketplace`
SET `sorder` = 22
WHERE `id` = 14;

UPDATE `m2epro_marketplace`
SET `sorder` = 11
WHERE `id` = 15;

UPDATE `m2epro_marketplace`
SET `sorder` = 12
WHERE `id` = 16;

UPDATE `m2epro_marketplace`
SET `sorder` = 13
WHERE `id` = 17;

UPDATE `m2epro_marketplace`
SET `sorder` = 15
WHERE `id` = 18;

UPDATE `m2epro_marketplace`
SET `sorder` = 9
WHERE `id` = 19;

UPDATE `m2epro_marketplace`
SET `sorder` = 17
WHERE `id` = 20;

UPDATE `m2epro_marketplace`
SET `sorder` = 18
WHERE `id` = 21;

UPDATE `m2epro_marketplace`
SET `sorder` = 20
WHERE `id` = 22;

UPDATE `m2epro_marketplace`
SET `sorder` = 21
WHERE `id` = 23;

SQL
);

//#############################################

$installer->endSetup();

//#############################################