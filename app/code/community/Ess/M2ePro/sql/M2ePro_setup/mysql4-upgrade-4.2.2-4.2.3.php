<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE `m2epro_ebay_account`
    ADD COLUMN `marketplaces_data` TEXT DEFAULT NULL AFTER `token_expired_date`;

    ALTER TABLE `m2epro_ebay_marketplace`
    DROP COLUMN `related_store_id`;
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_account');

if ($connection->tableColumnExists($tempTable, 'marketplaces_data') === false) {
    $connection->addColumn($tempTable, 'marketplaces_data', 'TEXT DEFAULT NULL AFTER `token_expired_date`');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_marketplace');
$existsMarketplaces = $connection->query("SELECT * FROM `{$tempTable}`")->fetchAll();

$tempMarketplacesData = array();
foreach ($existsMarketplaces as $marketplace) {
    $tempMarketplacesData[$marketplace['marketplace_id']]['related_store_id'] = (int)$marketplace['related_store_id'];
}

$tempMarketplacesDataMySql = $connection->quote(json_encode($tempMarketplacesData));

$tempTable = $installer->getTable('m2epro_ebay_account');
$connection->query(
    "UPDATE `{$tempTable}`
     SET `marketplaces_data` = {$tempMarketplacesDataMySql}"
);

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_marketplace');

if ($connection->tableColumnExists($tempTable, 'related_store_id') !== false) {
    $connection->dropColumn($tempTable, 'related_store_id');
}

//#############################################

$tempTable = $installer->getTable('m2epro_config');
$tempRow = $connection->query("SELECT * FROM `{$tempTable}`
                               WHERE `group` = '/listings/categories_add_actions/'
                               AND   `key` = 'ignore_not_visible'")
                      ->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/listings/categories_add_actions/', 'ignore_not_visible', '0', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49');

SQL
);
}

//#############################################

$installer->run(<<<SQL

UPDATE `m2epro_amazon_listing_product`
SET `category_id` = NULL,
    `general_id` = NULL,
    `general_id_search_status` = 0,
    `general_id_search_suggest_data` = NULL,
    `existance_check_status` = 0,
    `worldwide_id` = NULL,
    `sku` = NULL,
    `online_price` = NULL,
    `online_qty` = NULL,
    `is_afn_channel` = NULL,
    `is_isbn_general_id` = NULL,
    `is_upc_worldwide_id` = NULL,
    `start_date` = NULL,
    `end_date` = NULL
WHERE `listing_product_id` IN (SELECT `id`
                               FROM `m2epro_listing_product`
                               WHERE `status` != 0
                               AND   `component_mode` = 'amazon')
AND (`sku` IS NULL
    OR `sku` = ''
    OR `general_id` IS NULL
    OR `general_id` = '');

UPDATE `m2epro_listing_product`
SET `status` = 0,
    `status_changer` = 0
WHERE `id` IN (SELECT `listing_product_id`
               FROM `m2epro_amazon_listing_product`
               WHERE `sku` IS NULL
               OR `sku` = ''
               OR `general_id` IS NULL
               OR `general_id` = '')
AND `status` != 0;

SQL
);

//#############################################

$tempTable = $installer->getTable('m2epro_processing_request');
$tempRow = $connection->query("SELECT `id`
                               FROM `{$tempTable}`
                               WHERE `hash` = ''
                               OR    `processing_hash` = ''")
                      ->fetch();

if ($tempRow !== false) {
    $tempTable = $installer->getTable('m2epro_processing_request');
    $connection->query("DELETE FROM `{$tempTable}`");
    $tempTable = $installer->getTable('m2epro_lock_item');
    $connection->query("DELETE FROM `{$tempTable}`");
    $tempTable = $installer->getTable('m2epro_locked_object');
    $connection->query("DELETE FROM `{$tempTable}`");
    $tempTable = $installer->getTable('m2epro_amazon_processed_inventory');
    $connection->query("DELETE FROM `{$tempTable}`");
}

//#############################################

$tempTable = $installer->getTable('m2epro_ebay_account');
$tempAccounts = $connection->query("SELECT * FROM `{$tempTable}`")->fetchAll();

foreach ($tempAccounts as $account) {

    $shippingDiscountProfiles = json_decode($account['ebay_shipping_discount_profiles'], true);

    if (!is_array($shippingDiscountProfiles)) {
        continue;
    }

    // all existing profiles are for US marketplace
    $shippingDiscountProfiles = $connection->quote(json_encode(array(1 => $shippingDiscountProfiles)));

    $connection->query(
        "UPDATE `{$tempTable}`
         SET `ebay_shipping_discount_profiles` = {$shippingDiscountProfiles}
         WHERE `account_id` = ".(int)$account['account_id']
    );
}

//#############################################

$installer->endSetup();

//#############################################