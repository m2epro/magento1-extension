<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE `m2epro_amazon_template_new_product_description`
    CHANGE COLUMN `package_weight_custom_value` `package_weight_custom_value` DECIMAL(10, 2) UNSIGNED DEFAULT NULL,
    CHANGE COLUMN `shipping_weight_custom_value` `shipping_weight_custom_value` DECIMAL(10, 2) UNSIGNED DEFAULT NULL;

    ALTER TABLE `m2epro_buy_dictionary_category`
    ADD INDEX `category_id` (`category_id`);

    ALTER TABLE `m2epro_ebay_marketplace`
    DROP COLUMN `is_get_it_fast`;

    ALTER TABLE `m2epro_ebay_template_shipping`
    DROP COLUMN `get_it_fast`;

    ALTER TABLE `m2epro_play_template_synchronization`
    DROP COLUMN `revise_update_title`,
    DROP COLUMN `revise_update_sub_title`,
    DROP COLUMN `revise_update_description`,
    DROP COLUMN `relist_send_data`;
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_template_new_product_description');

if ($connection->tableColumnExists($tempTable, 'package_weight_custom_value')) {
    $connection->changeColumn(
        $tempTable,
        'package_weight_custom_value',
        'package_weight_custom_value',
        'DECIMAL(10, 2) UNSIGNED DEFAULT NULL'
    );
}

if ($connection->tableColumnExists($tempTable, 'shipping_weight_custom_value')) {
    $connection->changeColumn(
        $tempTable,
        'shipping_weight_custom_value',
        'shipping_weight_custom_value',
        'DECIMAL(10, 2) UNSIGNED DEFAULT NULL'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_buy_dictionary_category');
$tempTableIndexList = $connection->getIndexList($tempTable);

if (!isset($tempTableIndexList[strtoupper('category_id')])) {
    $connection->addKey($tempTable, 'category_id', 'category_id');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_marketplace');

if ($connection->tableColumnExists($tempTable, 'is_get_it_fast') !== false) {
    $connection->dropColumn($tempTable, 'is_get_it_fast');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_shipping');

if ($connection->tableColumnExists($tempTable, 'get_it_fast') !== false) {
    $connection->dropColumn($tempTable, 'get_it_fast');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_play_template_synchronization');

if ($connection->tableColumnExists($tempTable, 'revise_update_title') !== false) {
    $connection->dropColumn($tempTable,'revise_update_title');
}

if ($connection->tableColumnExists($tempTable, 'revise_update_sub_title') !== false) {
    $connection->dropColumn($tempTable,'revise_update_sub_title');
}

if ($connection->tableColumnExists($tempTable, 'revise_update_description') !== false) {
    $connection->dropColumn($tempTable,'revise_update_description');
}

if ($connection->tableColumnExists($tempTable, 'relist_send_data') !== false) {
    $connection->dropColumn($tempTable,'relist_send_data');
}

//#############################################

$tempTable = $installer->getTable('m2epro_config');
$tempRow = $connection->query("
    SELECT * FROM `{$tempTable}`
    WHERE `group` = '/cron/service/'
    AND   `key` = 'disabled'
")->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO m2epro_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/cron/service/', 'disabled', '0', NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00');

SQL
    );
}

//#############################################

$installer->run(<<<SQL

    UPDATE `m2epro_ebay_marketplace`
    SET `is_multivariation` = 1
    WHERE `marketplace_id` = 12;

SQL
);

//#############################################

$installer->endSetup();

//#############################################