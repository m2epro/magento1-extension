<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//########################################

/*
    ALTER TABLE `m2epro_amazon_template_synchronization`
    DROP INDEX `relist_shedule_type`,
    DROP INDEX `relist_shedule_week`,
    ADD INDEX `relist_schedule_type` (`relist_schedule_type`),
    ADD INDEX `relist_schedule_week` (`relist_schedule_week`);

    ALTER TABLE `m2epro_ebay_template_synchronization`
    DROP INDEX `relist_shedule_type`,
    DROP INDEX `relist_shedule_week`,
    ADD INDEX `relist_schedule_type` (`relist_schedule_type`),
    ADD INDEX `relist_schedule_week` (`relist_schedule_week`);
*/

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_template_synchronization');
$tempTableIndexList = $connection->getIndexList($tempTable);

if (isset($tempTableIndexList[strtoupper('relist_shedule_type')])) {
    $connection->dropKey($tempTable, 'relist_shedule_type');
}
if (isset($tempTableIndexList[strtoupper('relist_shedule_week')])) {
    $connection->dropKey($tempTable, 'relist_shedule_week');
}

if (!isset($tempTableIndexList[strtoupper('relist_schedule_type')])) {
    $connection->addKey($tempTable, 'relist_schedule_type', 'relist_schedule_type');
}
if (!isset($tempTableIndexList[strtoupper('relist_schedule_week')])) {
    $connection->addKey($tempTable, 'relist_schedule_week', 'relist_schedule_week');
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_synchronization');
$tempTableIndexList = $connection->getIndexList($tempTable);

if (isset($tempTableIndexList[strtoupper('relist_shedule_type')])) {
    $connection->dropKey($tempTable, 'relist_shedule_type');
}
if (isset($tempTableIndexList[strtoupper('relist_shedule_week')])) {
    $connection->dropKey($tempTable, 'relist_shedule_week');
}

if (!isset($tempTableIndexList[strtoupper('relist_schedule_type')])) {
    $connection->addKey($tempTable, 'relist_schedule_type', 'relist_schedule_type');
}
if (!isset($tempTableIndexList[strtoupper('relist_schedule_week')])) {
    $connection->addKey($tempTable, 'relist_schedule_week', 'relist_schedule_week');
}

//########################################

if ((string)Mage::getConfig()->getTablePrefix() != '') {

    $tablesList = $connection->listTables();

    $tempTableWithoutPrefix = 'm2epro_amazon_category_description';
    $tempTable = $installer->getTable($tempTableWithoutPrefix);

    if (in_array($tempTableWithoutPrefix, $tablesList) && !in_array($tempTable, $tablesList)) {
        $query = sprintf('ALTER TABLE %s RENAME TO %s', $tempTableWithoutPrefix, $tempTable);
        $connection->query($query);
    }

    $tempTableWithoutPrefix = 'm2epro_ebay_motor_specific';
    $tempTable = $installer->getTable($tempTableWithoutPrefix);

    if (in_array($tempTableWithoutPrefix, $tablesList) && !in_array($tempTable, $tablesList)) {
        $query = sprintf('ALTER TABLE %s RENAME TO %s', $tempTableWithoutPrefix, $tempTable);
        $connection->query($query);
    }
}

//########################################

$tempTable = $installer->getTable('m2epro_config');
$priceMode = $connection->query("
    SELECT `value`
    FROM `{$tempTable}`
    WHERE `group` = '/ebay/synchronization/settings/other_listing/source/'
      AND `key` = 'price'
")->fetchColumn();

$finalPriceMode = 4;
if ((int)$priceMode != $finalPriceMode) {

    $installer->run(<<<SQL

UPDATE `m2epro_config`
SET `value` = ''
WHERE `group` = '/ebay/synchronization/settings/other_listing/source/'
  AND `key` = 'customer_group_id';

SQL
    );
}

//########################################

$installer->run(<<<SQL

UPDATE `m2epro_config`
SET `value` = 'http://docs.m2epro.com/display/eBayAmazonMagentoV42/Video+Tutorials'
WHERE `group` = '/video_tutorials/'
AND `key` = 'baseurl';

SQL
);

//########################################

$installer->endSetup();

//########################################