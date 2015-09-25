<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE `m2epro_ebay_listing_product`
    ADD COLUMN `online_sku` VARCHAR(255) DEFAULT NULL AFTER `ebay_item_id`,
    ADD COLUMN `online_title` VARCHAR(255) DEFAULT NULL AFTER `online_sku`,
    ADD INDEX `online_sku` (`online_sku`),
    ADD INDEX `online_title` (`online_title`);

    ALTER TABLE `m2epro_ebay_dictionary_marketplace`
    ADD COLUMN `charities` LONGTEXT NOT NULL AFTER `tax_categories`;

    ALTER TABLE `m2epro_ebay_template_selling_format`
    ADD COLUMN `charity` VARCHAR(255) DEFAULT NULL AFTER `best_offer_reject_attribute`;
*/

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_listing_product');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'online_sku') === false) {
    $connection->addColumn(
        $tempTable,
        'online_sku',
        'VARCHAR(255) DEFAULT NULL AFTER `ebay_item_id`'
    );
}

if ($connection->tableColumnExists($tempTable, 'online_title') === false) {
    $connection->addColumn(
        $tempTable,
        'online_title',
        'VARCHAR(255) DEFAULT NULL AFTER `online_sku`'
    );
}

if (!isset($tempTableIndexList[strtoupper('online_sku')])) {
    $connection->addKey($tempTable, 'online_sku', 'online_sku');
}

if (!isset($tempTableIndexList[strtoupper('online_title')])) {
    $connection->addKey($tempTable, 'online_title', 'online_title');
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_dictionary_marketplace');

if ($connection->tableColumnExists($tempTable, 'charities') === false) {
    $connection->addColumn(
        $tempTable,
        'charities',
        'LONGTEXT NOT NULL AFTER `tax_categories`');
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_selling_format');

if ($connection->tableColumnExists($tempTable, 'charity') === false) {
    $connection->addColumn(
        $tempTable,
        'charity',
        'VARCHAR(255) DEFAULT NULL AFTER `best_offer_reject_attribute`');
}

//#############################################

$tempTable = $installer->getTable('m2epro_config');
$tempQuery = <<<SQL
    SELECT * FROM `{$tempTable}`
    WHERE `group` = '/view/requirements/popup/'
    AND   `key` = 'closed'
SQL;
$tempRow = $connection->query($tempQuery)->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/view/analytic/', 'url', 'http://analytics.m2epro.com/', 'analytic service url', '2013-05-08 00:00:00',
 '2013-05-08 00:00:00'),
('/view/analytic/', 'view', 'both', 'both, ebay, common', '2013-05-08 00:00:00',
 '2013-05-08 00:00:00'),
('/view/analytic/', 'navigation_mode', 'disabled', 'disabled, standard, advanced', '2013-05-08 00:00:00',
 '2013-05-08 00:00:00'),
('/view/analytic/', 'action_mode', 'disabled', 'disabled, all, special', '2013-05-08 00:00:00',
 '2013-05-08 00:00:00'),
('/view/requirements/popup/', 'closed', '0', '0 - false, - true',
 '2013-05-08 00:00:00', '2013-05-08 00:00:00');

SQL
);
}

//#############################################

$installer->run(<<<SQL

UPDATE `m2epro_primary_config`
SET `value` = 'https://m2epro.com/server/'
WHERE `group` = '/server/'
AND   `key` = 'baseurl';

DELETE FROM `m2epro_primary_config`
WHERE `group` = '/M2ePro/server/'
AND   `key` = 'directory';

UPDATE `m2epro_config`
SET `value` = 'http://docs.m2epro.com/display/eBayMagentoV6/M2E+Pro+v.6.x+Overview'
WHERE `group` = '/support/'
AND   `key` = 'documentation_url';

UPDATE `m2epro_config`
SET `value` = 'http://docs.m2epro.com/display/eBayMagentoV6/Video+Tutorials'
WHERE `group` = '/support/'
AND   `key` = 'video_tutorials_url';

SQL
);

//#############################################

$installer->endSetup();

//#############################################