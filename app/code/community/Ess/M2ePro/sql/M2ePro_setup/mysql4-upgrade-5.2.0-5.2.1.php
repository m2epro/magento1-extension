<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE `m2epro_play_account`
    ADD COLUMN `panel_mode` VARCHAR(255) NOT NULL DEFAULT 'trade' AFTER `login`,
    ADD INDEX `panel_mode` (`panel_mode`);
*/

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_play_account');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'panel_mode') === false) {
    $connection->addColumn(
        $tempTable,
        'panel_mode',
        'VARCHAR(255) NOT NULL DEFAULT \'trade\' AFTER `login`'
    );
}

if (!isset($tempTableIndexList[strtoupper('panel_mode')])) {
    $connection->addKey($tempTable, 'panel_mode', 'panel_mode');
}

//#############################################

$installer->run(<<<SQL

UPDATE `m2epro_config`
SET `value` = 'http://docs.m2epro.com/display/eBayAmazonRakutenPlayMagentoV52/'
WHERE `group` = '/documentation/'
AND `key` = 'baseurl';

UPDATE `m2epro_config`
SET `value` = 'http://docs.m2epro.com/display/eBayAmazonRakutenPlayMagentoV52/Video+Tutorials'
WHERE `group` = '/video_tutorials/'
AND `key` = 'baseurl';

DELETE FROM `m2epro_config`
WHERE `group` = '/play/synchronization/settings/orders/'
AND   `key` = 'max_deactivate_time';

DELETE FROM `m2epro_config`
WHERE `group` = '/buy/synchronization/settings/orders/'
AND   `key` = 'max_deactivate_time';

UPDATE `m2epro_config`
SET `value` = '3600'
WHERE `group` = '/buy/synchronization/settings/orders/receive/'
AND `key` = 'interval';

UPDATE `m2epro_config`
SET `value` = '3600'
WHERE `group` = '/buy/synchronization/settings/orders/update/'
AND `key` = 'interval';

SQL
);

//#############################################

$tempTable = $installer->getTable('m2epro_config');
$tempQuery = <<<SQL
    SELECT * FROM `{$tempTable}`
    WHERE `group` = '/play/synchronization/settings/orders/receive/'
    AND   `key` = 'mode'
SQL;
$tempRow = $connection->query($tempQuery)->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/order/magento/settings/', 'create_with_first_product_options_when_variation_unavailable', '1',
 '0 - disable, \r\n1 - enable', '2013-05-30 00:00:00', '2013-05-30 00:00:00'),
('/play/synchronization/settings/orders/receive/', 'mode', '1', '0 - disable, \r\n1 - enable',
'2013-06-05 00:00:00', '2013-06-05 00:00:00'),
('/play/synchronization/settings/orders/receive/', 'interval', '3600', 'in seconds',
'2013-06-05 00:00:00', '2013-06-05 00:00:00'),
('/play/synchronization/settings/orders/receive/', 'max_deactivate_time', '86400', 'in seconds',
'2013-06-05 00:00:00', '2013-06-05 00:00:00'),
('/play/synchronization/settings/orders/update/', 'mode', '1', '0 - disable, \r\n1 - enable',
'2013-06-05 00:00:00', '2013-06-05 00:00:00'),
('/play/synchronization/settings/orders/update/', 'interval', '3600', 'in seconds',
'2013-06-05 00:00:00', '2013-06-05 00:00:00'),
('/play/synchronization/settings/orders/update/', 'max_deactivate_time', '86400', 'in seconds',
'2013-06-05 00:00:00', '2013-06-05 00:00:00'),
('/cron/', 'double_run_protection', '0', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49');

SQL
);

}

//#############################################

$currentDateTime = date('c');

$mainTable = $installer->getTable('m2epro_listing_product');
$secondTable = $installer->getTable('m2epro_amazon_listing_product');
$listingTable = $installer->getTable('m2epro_listing');
$templateTable = $installer->getTable('m2epro_template_general');
$itemTable = $installer->getTable('m2epro_amazon_item');

$tempRows = $connection->query("
            SELECT `lp`.`id` as `listing_product_id`,
                   `alp`.`additional_data`,
                   `alp`.`is_variation_product`,
                   `alp`.`is_variation_matched`,
                   `tg`.`marketplace_id`,
                   `tg`.`account_id`,
                   `alp`.`sku`,
                   `l`.`store_id`,
                   `lp`.`product_id`

            FROM `{$mainTable}` AS `lp`
            INNER JOIN `{$secondTable}` AS `alp` ON `lp`.`id` = `alp`.`listing_product_id`
            INNER JOIN `{$listingTable}` AS `l` ON `lp`.`listing_id` = `l`.`id`
            INNER JOIN `{$templateTable}` AS `tg` ON `l`.`template_general_id` = `tg`.`id`
            LEFT JOIN `{$itemTable}` AS `ai` ON (`lp`.`product_id` = `ai`.`product_id` AND
                                                 `tg`.`marketplace_id` = `ai`.`marketplace_id` AND
                                                 `tg`.`account_id` = `ai`.`account_id` AND
                                                 `alp`.`sku` = `ai`.`sku`)

            WHERE `alp`.`sku` IS NOT NULL
            AND `ai`.`id` IS NULL")->fetchAll();

$insertSql = '';
foreach ($tempRows as $tempRow) {

    if ((bool)(int)$tempRow['is_variation_product'] && !(bool)(int)$tempRow['is_variation_matched']) {
        continue;
    }

    !empty($insertSql) && $insertSql .= ' , ';

    $accountId = (int)$tempRow['account_id'];
    $marketplaceId = (int)$tempRow['marketplace_id'];
    $skuMySql = $connection->quote($tempRow['sku']);
    $storeId = (int)$tempRow['store_id'];
    $productId = (int)$tempRow['product_id'];

    $variationOptions = 'NULL';
    if ((bool)(int)$tempRow['is_variation_product'] && (bool)(int)$tempRow['is_variation_matched']) {
        $tempAdditionalData = (string)$tempRow['additional_data'];
        $tempAdditionalData != '' && $tempAdditionalData = json_decode($tempAdditionalData,true);
        if (isset($tempAdditionalData['variation_options'])) {
            $variationOptions = $connection->quote(json_encode($tempAdditionalData['variation_options']));
        }
    }

    $insertSql .= "(
        {$accountId},{$marketplaceId},{$skuMySql},{$productId},{$storeId},
        {$variationOptions},'{$currentDateTime}','{$currentDateTime}'
    )";
}

if (!empty($insertSql)) {

    $insertSql = 'INSERT INTO `'.$itemTable.'` (`account_id`,`marketplace_id`,`sku`,`product_id`,`store_id`,
                  `variation_options`,`update_date`,`create_date`) VALUES '.$insertSql.';';

    $connection->query($insertSql);
}

//#############################################

$currentDateTime = date('c');

$mainTable = $installer->getTable('m2epro_listing_product');
$secondTable = $installer->getTable('m2epro_buy_listing_product');
$listingTable = $installer->getTable('m2epro_listing');
$templateTable = $installer->getTable('m2epro_template_general');
$itemTable = $installer->getTable('m2epro_buy_item');

$tempRows = $connection->query("
            SELECT `lp`.`id` as `listing_product_id`,
                   `alp`.`additional_data`,
                   `alp`.`is_variation_product`,
                   `alp`.`is_variation_matched`,
                   `tg`.`marketplace_id`,
                   `tg`.`account_id`,
                   `alp`.`sku`,
                   `l`.`store_id`,
                   `lp`.`product_id`

            FROM `{$mainTable}` AS `lp`
            INNER JOIN `{$secondTable}` AS `alp` ON `lp`.`id` = `alp`.`listing_product_id`
            INNER JOIN `{$listingTable}` AS `l` ON `lp`.`listing_id` = `l`.`id`
            INNER JOIN `{$templateTable}` AS `tg` ON `l`.`template_general_id` = `tg`.`id`
            LEFT JOIN `{$itemTable}` AS `ai` ON (`lp`.`product_id` = `ai`.`product_id` AND
                                                 `tg`.`marketplace_id` = `ai`.`marketplace_id` AND
                                                 `tg`.`account_id` = `ai`.`account_id` AND
                                                 `alp`.`sku` = `ai`.`sku`)

            WHERE `alp`.`sku` IS NOT NULL
            AND `ai`.`id` IS NULL")->fetchAll();

$insertSql = '';
foreach ($tempRows as $tempRow) {

    if ((bool)(int)$tempRow['is_variation_product'] && !(bool)(int)$tempRow['is_variation_matched']) {
        continue;
    }

    !empty($insertSql) && $insertSql .= ' , ';

    $accountId = (int)$tempRow['account_id'];
    $marketplaceId = (int)$tempRow['marketplace_id'];
    $skuMySql = $connection->quote($tempRow['sku']);
    $storeId = (int)$tempRow['store_id'];
    $productId = (int)$tempRow['product_id'];

    $variationOptions = 'NULL';
    if ((bool)(int)$tempRow['is_variation_product'] && (bool)(int)$tempRow['is_variation_matched']) {
        $tempAdditionalData = (string)$tempRow['additional_data'];
        $tempAdditionalData != '' && $tempAdditionalData = json_decode($tempAdditionalData,true);
        if (isset($tempAdditionalData['variation_options'])) {
            $variationOptions = $connection->quote(json_encode($tempAdditionalData['variation_options']));
        }
    }

    $insertSql .= "(
        {$accountId},{$marketplaceId},{$skuMySql},{$productId},{$storeId},{$variationOptions},
        '{$currentDateTime}','{$currentDateTime}'
    )";
}

if (!empty($insertSql)) {

    $insertSql = 'INSERT INTO `'.$itemTable.'` (`account_id`,`marketplace_id`,`sku`,`product_id`,`store_id`,
                  `variation_options`,`update_date`,`create_date`) VALUES '.$insertSql.';';

    $connection->query($insertSql);
}

//#############################################

$currentDateTime = date('c');

$mainTable = $installer->getTable('m2epro_listing_product');
$secondTable = $installer->getTable('m2epro_play_listing_product');
$listingTable = $installer->getTable('m2epro_listing');
$templateTable = $installer->getTable('m2epro_template_general');
$itemTable = $installer->getTable('m2epro_play_item');

$tempRows = $connection->query("
            SELECT `lp`.`id` as `listing_product_id`,
                   `alp`.`additional_data`,
                   `alp`.`is_variation_product`,
                   `alp`.`is_variation_matched`,
                   `tg`.`marketplace_id`,
                   `tg`.`account_id`,
                   `alp`.`sku`,
                   `l`.`store_id`,
                   `lp`.`product_id`

            FROM `{$mainTable}` AS `lp`
            INNER JOIN `{$secondTable}` AS `alp` ON `lp`.`id` = `alp`.`listing_product_id`
            INNER JOIN `{$listingTable}` AS `l` ON `lp`.`listing_id` = `l`.`id`
            INNER JOIN `{$templateTable}` AS `tg` ON `l`.`template_general_id` = `tg`.`id`
            LEFT JOIN `{$itemTable}` AS `ai` ON (`lp`.`product_id` = `ai`.`product_id` AND
                                                 `tg`.`marketplace_id` = `ai`.`marketplace_id` AND
                                                 `tg`.`account_id` = `ai`.`account_id` AND
                                                 `alp`.`sku` = `ai`.`sku`)

            WHERE `alp`.`sku` IS NOT NULL
            AND `ai`.`id` IS NULL")->fetchAll();

$insertSql = '';
foreach ($tempRows as $tempRow) {

    if ((bool)(int)$tempRow['is_variation_product'] && !(bool)(int)$tempRow['is_variation_matched']) {
        continue;
    }

    !empty($insertSql) && $insertSql .= ' , ';

    $accountId = (int)$tempRow['account_id'];
    $marketplaceId = (int)$tempRow['marketplace_id'];
    $skuMySql = $connection->quote($tempRow['sku']);
    $storeId = (int)$tempRow['store_id'];
    $productId = (int)$tempRow['product_id'];

    $variationOptions = 'NULL';
    if ((bool)(int)$tempRow['is_variation_product'] && (bool)(int)$tempRow['is_variation_matched']) {
        $tempAdditionalData = (string)$tempRow['additional_data'];
        $tempAdditionalData != '' && $tempAdditionalData = json_decode($tempAdditionalData,true);
        if (isset($tempAdditionalData['variation_options'])) {
            $variationOptions = $connection->quote(json_encode($tempAdditionalData['variation_options']));
        }
    }

    $insertSql .= "(
        {$accountId},{$marketplaceId},{$skuMySql},{$productId},{$storeId},{$variationOptions},
        '{$currentDateTime}','{$currentDateTime}'
    )";
}

if (!empty($insertSql)) {

    $insertSql = 'INSERT INTO `'.$itemTable.'` (`account_id`,`marketplace_id`,`sku`,`product_id`,`store_id`,
                  `variation_options`,`update_date`,`create_date`) VALUES '.$insertSql.';';

    $connection->query($insertSql);
}

//#############################################

$installer->endSetup();

//#############################################