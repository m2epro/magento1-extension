<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//########################################

/*
    ### registry table
    ### -------------------------------

    ALTER TABLE `m2epro_registry`
        MODIFY COLUMN `value` LONGTEXT DEFAULT NULL;

    ### -------------------------------

    ### dictionary tables improvements
    ### -------------------------------

    ALTER TABLE `m2epro_ebay_dictionary_marketplace`
        ADD COLUMN `additional_data` LONGTEXT DEFAULT NULL AFTER `shipping_locations_exclude`,
        DROP COLUMN `categories_features_defaults`;

    DROP TABLE IF EXISTS `m2epro_ebay_dictionary_shipping_category`;

    ALTER TABLE `m2epro_amazon_dictionary_marketplace`
        DROP COLUMN `vocabulary`;

    ### -------------------------------

    ALTER TABLE `m2epro_ebay_dictionary_category`
        CHANGE COLUMN `parent_category_id` `parent_category_id` INT(11) UNSIGNED DEFAULT NULL AFTER `category_id`,
        CHANGE COLUMN `title` `title` VARCHAR(255) NOT NULL AFTER `parent_category_id`,
        CHANGE COLUMN `features` `features` LONGTEXT DEFAULT NULL AFTER `title`,
        CHANGE COLUMN `item_specifics` `item_specifics` LONGTEXT DEFAULT NULL AFTER `features`,
        ADD COLUMN `path` VARCHAR(500) DEFAULT NULL AFTER `title`,
        DROP COLUMN `level`,
        ADD INDEX `path` (`path`),
        DROP INDEX `level`;

    ALTER TABLE `m2epro_amazon_dictionary_category`
        CHANGE COLUMN `marketplace_id` `marketplace_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
        CHANGE COLUMN `category_id` `category_id` INT(11) UNSIGNED NOT NULL AFTER `marketplace_id`,
        CHANGE COLUMN `browsenode_id` `browsenode_id` DECIMAL(20, 0) UNSIGNED NOT NULL AFTER `parent_category_id`,
        CHANGE COLUMN `product_data_nick` `product_data_nick` VARCHAR(255) DEFAULT NULL AFTER `browsenode_id`,
        CHANGE COLUMN `path` `path` VARCHAR(500) DEFAULT NULL,
        CHANGE COLUMN `is_listable` `is_leaf` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
        DROP INDEX `is_listable`,
        ADD INDEX `is_leaf` (`is_leaf`);

    ALTER TABLE `m2epro_buy_dictionary_category`
        CHANGE COLUMN `parent_category_id` `parent_category_id` INT(11) UNSIGNED DEFAULT NULL AFTER `category_id`,
        CHANGE COLUMN `node_id` `node_id` INT(11) UNSIGNED NOT NULL AFTER `parent_category_id`,
        CHANGE COLUMN `attributes` `attributes` LONGTEXT DEFAULT NULL AFTER `path`,
        CHANGE COLUMN `is_listable` `is_leaf` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `attributes`,
        CHANGE COLUMN `path` `path` VARCHAR(500) DEFAULT NULL,
        DROP COLUMN `sorder`,
        DROP INDEX `sorder`,
        DROP INDEX `is_listable`,
        ADD INDEX `is_leaf` (`is_leaf`);

    ### -------------------------------

    ALTER TABLE `m2epro_ebay_dictionary_motor_specific`
        ADD COLUMN `is_custom` TINYINT(2) UNSIGNED NOT NULL AFTER `submodel`,
        ADD INDEX is_custom (is_custom);

    ALTER TABLE `m2epro_ebay_dictionary_motor_ktype`
        ADD COLUMN `is_custom` TINYINT(2) UNSIGNED NOT NULL AFTER `engine`,
        DROP COLUMN `marketplace_id`,
        ADD INDEX is_custom (is_custom),
        DROP INDEX marketplace_id;

    ### -------------------------------

    ### ebay prices migration
    ### -------------------------------

    ALTER TABLE `m2epro_ebay_listing_product`
        ADD COLUMN `online_current_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL AFTER `online_title`,
        ADD INDEX `online_current_price` (`online_current_price`);

    ALTER TABLE `m2epro_ebay_template_selling_format`
        ADD COLUMN `fixed_price_mode` TINYINT(2) UNSIGNED NOT NULL AFTER `price_variation_mode`,
        ADD COLUMN `fixed_price_coefficient` VARCHAR(255) NOT NULL AFTER `fixed_price_mode`,
        ADD COLUMN `fixed_price_custom_attribute` VARCHAR(255) NOT NULL NULL AFTER `fixed_price_coefficient`;

    ### -------------------------------

    ### sku modification feature
    ### -------------------------------

    ALTER TABLE `m2epro_amazon_listing`
        ADD COLUMN `sku_modification_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `sku_custom_attribute`,
        ADD COLUMN `sku_modification_custom_value` VARCHAR(255) NOT NULL AFTER `sku_modification_mode`;

    ALTER TABLE `m2epro_buy_listing`
        ADD COLUMN `sku_modification_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `sku_custom_attribute`,
        ADD COLUMN `sku_modification_custom_value` VARCHAR(255) NOT NULL AFTER `sku_modification_mode`;

    ### -------------------------------

    ### other
    ### -------------------------------

    ALTER TABLE `m2epro_amazon_listing_other`
        CHANGE COLUMN `title` `title` VARCHAR(255) DEFAULT NULL AFTER `sku`;

    ALTER TABLE `m2epro_listing`
        MODIFY COLUMN `additional_data` LONGTEXT DEFAULT NULL AFTER `source_products`;

    ALTER TABLE `m2epro_listing_log`
        MODIFY COLUMN `additional_data` LONGTEXT DEFAULT NULL AFTER `product_title`;

    ALTER TABLE `m2epro_listing_other`
        MODIFY COLUMN `additional_data` LONGTEXT DEFAULT NULL AFTER `component_mode`;

    ALTER TABLE `m2epro_listing_product`
        MODIFY COLUMN `additional_data` LONGTEXT DEFAULT NULL AFTER `component_mode`;

    ALTER TABLE `m2epro_listing_product_variation`
        ADD COLUMN `additional_data` LONGTEXT DEFAULT NULL AFTER `component_mode`;

    ### -------------------------------
*/

// ---------------------------------------

$installer->run(<<<SQL

    DROP TABLE IF EXISTS `m2epro_ebay_dictionary_shipping_category`;

    TRUNCATE TABLE `m2epro_ebay_dictionary_marketplace`;
    TRUNCATE TABLE `m2epro_ebay_dictionary_category`;
    TRUNCATE TABLE `m2epro_ebay_dictionary_shipping`;
    TRUNCATE TABLE `m2epro_ebay_dictionary_motor_specific`;
    TRUNCATE TABLE `m2epro_ebay_dictionary_motor_ktype`;

    ALTER TABLE `m2epro_registry`
        MODIFY COLUMN `value` LONGTEXT DEFAULT NULL;

SQL
);

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_dictionary_marketplace');

if ($connection->tableColumnExists($tempTable, 'vocabulary') !== false) {

    $existVocabulary = $connection->query("
        SELECT `vocabulary`
        FROM `{$tempTable}`
        WHERE `vocabulary` IS NOT NULL
        LIMIT 1
    ")->fetchColumn();

    if (!empty($existVocabulary)) {

        $tempTable = $installer->getTable('m2epro_registry');

        $connection->insert($tempTable, array(
            'key'   => 'amazon_vocabulary_server',
            'value' => $existVocabulary
        ));
    }
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_dictionary_marketplace');

if ($connection->tableColumnExists($tempTable, 'additional_data') === false) {
    $connection->addColumn(
        $tempTable, 'additional_data',
        'LONGTEXT DEFAULT NULL AFTER `shipping_locations_exclude`'
    );
}

if ($connection->tableColumnExists($tempTable, 'categories_features_defaults') !== false) {
    $connection->dropColumn($tempTable, 'categories_features_defaults');
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_dictionary_marketplace');

if ($connection->tableColumnExists($tempTable, 'vocabulary') !== false) {
    $connection->dropColumn($tempTable, 'vocabulary');
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_dictionary_category');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'parent_category_id') !== false) {
    $connection->changeColumn(
        $tempTable, 'parent_category_id', 'parent_category_id',
        'INT(11) UNSIGNED DEFAULT NULL AFTER `category_id`'
    );
}

if ($connection->tableColumnExists($tempTable, 'title') !== false) {
    $connection->changeColumn(
        $tempTable, 'title', 'title',
        'VARCHAR(255) NOT NULL AFTER `parent_category_id`'
    );
}

if ($connection->tableColumnExists($tempTable, 'features') !== false) {
    $connection->changeColumn(
        $tempTable, 'features', 'features',
        'LONGTEXT DEFAULT NULL AFTER `title`'
    );
}

if ($connection->tableColumnExists($tempTable, 'item_specifics') !== false) {
    $connection->changeColumn(
        $tempTable, 'item_specifics', 'item_specifics',
        'LONGTEXT DEFAULT NULL AFTER `features`'
    );
}

if ($connection->tableColumnExists($tempTable, 'path') === false) {
    $connection->addColumn(
        $tempTable, 'path',
        'VARCHAR(500) DEFAULT NULL AFTER `title`'
    );
}

if ($connection->tableColumnExists($tempTable, 'level') !== false) {
    $connection->dropColumn($tempTable, 'level');
}

if (!isset($tempTableIndexList[strtoupper('path')])) {
    $connection->addKey($tempTable, 'path', 'path');
}

if (isset($tempTableIndexList[strtoupper('level')])) {
    $connection->dropKey($tempTable, 'level');
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_dictionary_category');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'marketplace_id') !== false) {
    $connection->changeColumn(
        $tempTable, 'marketplace_id', 'marketplace_id',
        'INT(11) UNSIGNED NOT NULL AFTER `id`'
    );
}

if ($connection->tableColumnExists($tempTable, 'category_id') !== false) {
    $connection->changeColumn(
        $tempTable, 'category_id', 'category_id',
        'INT(11) UNSIGNED NOT NULL AFTER `marketplace_id`'
    );
}

if ($connection->tableColumnExists($tempTable, 'browsenode_id') !== false) {
    $connection->changeColumn(
        $tempTable, 'browsenode_id', 'browsenode_id',
        'DECIMAL(20, 0) UNSIGNED NOT NULL AFTER `parent_category_id`'
    );
}

if ($connection->tableColumnExists($tempTable, 'product_data_nick') !== false) {
    $connection->changeColumn(
        $tempTable, 'product_data_nick', 'product_data_nick',
        'VARCHAR(255) DEFAULT NULL AFTER `browsenode_id`'
    );
}

if ($connection->tableColumnExists($tempTable, 'path') !== false) {
    $connection->changeColumn($tempTable, 'path', 'path', 'VARCHAR(500) DEFAULT NULL');
}

if ($connection->tableColumnExists($tempTable, 'is_leaf') === false &&
    $connection->tableColumnExists($tempTable, 'is_listable') !== false) {
    $connection->changeColumn($tempTable, 'is_listable', 'is_leaf', 'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0');
}

if (isset($tempTableIndexList[strtoupper('is_listable')])) {
    $connection->dropKey($tempTable, 'is_listable');
}

if (!isset($tempTableIndexList[strtoupper('is_leaf')])) {
    $connection->addKey($tempTable, 'is_leaf', 'is_leaf');
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_buy_dictionary_category');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'parent_category_id') !== false) {
    $connection->changeColumn(
        $tempTable, 'parent_category_id', 'parent_category_id',
        'INT(11) UNSIGNED DEFAULT NULL AFTER `category_id`'
    );
}

if ($connection->tableColumnExists($tempTable, 'node_id') !== false) {
    $connection->changeColumn(
        $tempTable, 'node_id', 'node_id',
        'INT(11) UNSIGNED NOT NULL AFTER `parent_category_id`'
    );
}

if ($connection->tableColumnExists($tempTable, 'attributes') !== false) {
    $connection->changeColumn(
        $tempTable, 'attributes', 'attributes',
        'LONGTEXT DEFAULT NULL AFTER `path`'
    );
}

if ($connection->tableColumnExists($tempTable, 'is_leaf') === false &&
    $connection->tableColumnExists($tempTable, 'is_listable') !== false) {
    $connection->changeColumn(
        $tempTable, 'is_listable', 'is_leaf',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `attributes`'
    );
}

if ($connection->tableColumnExists($tempTable, 'path') !== false) {
    $connection->changeColumn($tempTable, 'path', 'path', 'VARCHAR(500) DEFAULT NULL');
}

if ($connection->tableColumnExists($tempTable, 'sorder') !== false) {
    $connection->dropColumn($tempTable, 'sorder');
}

if (isset($tempTableIndexList[strtoupper('sorder')])) {
    $connection->dropKey($tempTable, 'sorder');
}

if (isset($tempTableIndexList[strtoupper('is_listable')])) {
    $connection->dropKey($tempTable, 'is_listable');
}

if (!isset($tempTableIndexList[strtoupper('is_leaf')])) {
    $connection->addKey($tempTable, 'is_leaf', 'is_leaf');
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_dictionary_motor_specific');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'is_custom') === false) {
    $connection->addColumn(
        $tempTable, 'is_custom',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `submodel`'
    );
}

if (!isset($tempTableIndexList[strtoupper('is_custom')])) {
    $connection->addKey($tempTable, 'is_custom', 'is_custom');
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_dictionary_motor_ktype');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'is_custom') === false) {
    $connection->addColumn(
        $tempTable, 'is_custom',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `engine`'
    );
}

if ($connection->tableColumnExists($tempTable, 'marketplace_id') !== false) {
    $connection->dropColumn($tempTable, 'marketplace_id');
}

if (!isset($tempTableIndexList[strtoupper('is_custom')])) {
    $connection->addKey($tempTable, 'is_custom', 'is_custom');
}

if (isset($tempTableIndexList[strtoupper('marketplace_id')])) {
    $connection->dropKey($tempTable, 'marketplace_id');
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_listing_product');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'online_current_price') === false) {
    $connection->addColumn(
        $tempTable, 'online_current_price',
        'DECIMAL(12, 4) UNSIGNED DEFAULT NULL AFTER `online_title`'
    );
}

if (!isset($tempTableIndexList[strtoupper('online_current_price')])) {
    $connection->addKey($tempTable, 'online_current_price', 'online_current_price');
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_selling_format');

if ($connection->tableColumnExists($tempTable, 'fixed_price_mode') === false) {
    $connection->addColumn(
        $tempTable, 'fixed_price_mode',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `price_variation_mode`'
    );
}

if ($connection->tableColumnExists($tempTable, 'fixed_price_coefficient') === false) {
    $connection->addColumn(
        $tempTable, 'fixed_price_coefficient',
        'VARCHAR(255) NOT NULL AFTER `fixed_price_mode`'
    );
}

if ($connection->tableColumnExists($tempTable, 'fixed_price_custom_attribute') === false) {
    $connection->addColumn(
        $tempTable, 'fixed_price_custom_attribute',
        'VARCHAR(255) NOT NULL AFTER `fixed_price_coefficient`'
    );
}

// ---------------------------------------

$installer->run(<<<SQL

    UPDATE `m2epro_ebay_template_selling_format`
    SET `fixed_price_mode` = `buyitnow_price_mode`,
        `fixed_price_coefficient` = `buyitnow_price_coefficient`,
        `fixed_price_custom_attribute` = `buyitnow_price_custom_attribute`,
        `buyitnow_price_mode` = NULL,
        `buyitnow_price_coefficient` = NULL,
        `buyitnow_price_custom_attribute` = NULL
    WHERE
        listing_type = 2 AND buyitnow_price_mode IS NOT NULL AND buyitnow_price_mode <> 0;

    UPDATE `m2epro_ebay_template_selling_format`
    SET `fixed_price_mode` = `buyitnow_price_mode`,
        `fixed_price_coefficient` = `buyitnow_price_coefficient`,
        `fixed_price_custom_attribute` = `buyitnow_price_custom_attribute`
    WHERE
        listing_type = 3 AND buyitnow_price_mode IS NOT NULL AND buyitnow_price_mode <> 0;

    UPDATE `m2epro_ebay_listing_product`
        SET `online_current_price` = `online_buyitnow_price`,
            `online_buyitnow_price` = NULL
        WHERE `online_start_price` IS NULL AND `online_reserve_price` IS NULL AND online_buyitnow_price IS NOT NULL;

    UPDATE `m2epro_ebay_listing_product`
        SET `online_current_price` = `online_start_price`
        WHERE `online_start_price` IS NOT NULL AND `online_reserve_price` IS NOT NULL;

    UPDATE `m2epro_ebay_template_description`
        SET `description_template` = REPLACE(description_template, '#value[buy_it_now_price]#', '#value[fixed_price]#')
        WHERE INSTR(`description_template`, '#value[buy_it_now_price]#') > 0;

SQL
);

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_listing');

if ($connection->tableColumnExists($tempTable, 'sku_modification_mode') === false) {
    $connection->addColumn(
        $tempTable, 'sku_modification_mode',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `sku_custom_attribute`'
    );
}

if ($connection->tableColumnExists($tempTable, 'sku_modification_custom_value') === false) {
    $connection->addColumn(
        $tempTable, 'sku_modification_custom_value',
        'VARCHAR(255) NOT NULL AFTER `sku_modification_mode`'
    );
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_buy_listing');

if ($connection->tableColumnExists($tempTable, 'sku_modification_mode') === false) {
    $connection->addColumn(
        $tempTable, 'sku_modification_mode',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `sku_custom_attribute`'
    );
}

if ($connection->tableColumnExists($tempTable, 'sku_modification_custom_value') === false) {
    $connection->addColumn(
        $tempTable, 'sku_modification_custom_value',
        'VARCHAR(255) NOT NULL AFTER `sku_modification_mode`'
    );
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_listing_other');

if ($connection->tableColumnExists($tempTable, 'title') !== false) {
    $connection->changeColumn(
        $tempTable, 'title', 'title',
        'VARCHAR(255) DEFAULT NULL AFTER `sku`'
    );
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_listing');

if ($connection->tableColumnExists($tempTable, 'additional_data') !== false) {
    $connection->modifyColumn(
        $tempTable, 'additional_data',
        'LONGTEXT DEFAULT NULL AFTER `source_products`'
    );
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_listing_log');

if ($connection->tableColumnExists($tempTable, 'additional_data') !== false) {
    $connection->modifyColumn(
        $tempTable, 'additional_data',
        'LONGTEXT DEFAULT NULL AFTER `product_title`'
    );
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_listing_other');

if ($connection->tableColumnExists($tempTable, 'additional_data') !== false) {
    $connection->modifyColumn(
        $tempTable, 'additional_data',
        'LONGTEXT DEFAULT NULL AFTER `component_mode`'
    );
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_listing_product');

if ($connection->tableColumnExists($tempTable, 'additional_data') !== false) {
    $connection->modifyColumn(
        $tempTable, 'additional_data',
        'LONGTEXT DEFAULT NULL AFTER `component_mode`'
    );
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_listing_product_variation');

if ($connection->tableColumnExists($tempTable, 'additional_data') === false) {
    $connection->addColumn(
        $tempTable, 'additional_data',
        'LONGTEXT DEFAULT NULL AFTER `component_mode`'
    );
}

//########################################

$tempTable = $installer->getTable('m2epro_config');

$tempRow = $connection->query("
    SELECT *
    FROM `{$tempTable}`
    WHERE `group` = '/support/'
    AND `key` = 'documentation_url'
")->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

        DELETE FROM `m2epro_config`
        WHERE `group` = '/view/ebay/support/'
        OR    `group` = '/view/common/support/';

        INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
            ('/support/', 'documentation_url', 'http://docs.m2epro.com', NULL, '2015-07-03 00:00:00',
             '2015-07-03 00:00:00');
SQL
    );
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_wizard');

$tempRow = $connection->query("
    SELECT *
    FROM `{$tempTable}`
    WHERE `nick` = 'ebayProductDetails'
")->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

        INSERT INTO `m2epro_wizard` (`nick`, `view`, `status`, `step`, `type`, `priority`)
        VALUES ('ebayProductDetails', 'ebay', 0, NULL, 1, 7);
SQL
    );
}

//########################################

$tempTable = $installer->getTable('m2epro_synchronization_config');

$tempRow = $connection->query("
    SELECT *
    FROM `{$tempTable}`
    WHERE `group` = '/ebay/other_listings/sku/'
    AND   `key` = 'mode'
")->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

        UPDATE `m2epro_synchronization_config`
        SET `group` = '/amazon/other_listings/update/'
        WHERE `group` = '/amazon/other_listings/' AND `key` = 'mode';

        UPDATE `m2epro_synchronization_config`
        SET `group` = '/amazon/other_listings/update/'
        WHERE `group` = '/amazon/other_listings/' AND `key` = 'interval';

        UPDATE `m2epro_synchronization_config`
        SET `group` = '/amazon/other_listings/update/'
        WHERE `group` = '/amazon/other_listings/' AND `key` = 'last_time';

        UPDATE `m2epro_synchronization_config`
        SET `group` = '/buy/other_listings/update/'
        WHERE `group` = '/buy/other_listings/' AND `key` = 'mode';

        UPDATE `m2epro_synchronization_config`
        SET `group` = '/buy/other_listings/update/'
        WHERE `group` = '/buy/other_listings/' AND `key` = 'interval';

        UPDATE `m2epro_synchronization_config`
        SET `group` = '/buy/other_listings/update/'
        WHERE `group` = '/buy/other_listings/' AND `key` = 'last_time';

        INSERT INTO `m2epro_synchronization_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
          ('/amazon/other_listings/', 'mode', '1', '0 - disable, \r\n1 - enable',
           '2013-05-08 00:00:00', '2013-05-08 00:00:00');

        INSERT INTO `m2epro_synchronization_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
          ('/buy/other_listings/', 'mode', '1', '0 - disable, \r\n1 - enable',
           '2013-05-08 00:00:00', '2013-05-08 00:00:00');

        INSERT INTO `m2epro_synchronization_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
          ('/ebay/other_listings/sku/', 'mode', '1', '0 - disable, \r\n1 - enable',
           '2013-05-08 00:00:00', '2013-05-08 00:00:00');

        INSERT INTO `m2epro_synchronization_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
          ('/amazon/other_listings/title/', 'mode', '1', '0 - disable, \r\n1 - enable',
           '2013-05-08 00:00:00', '2013-05-08 00:00:00');

        INSERT INTO `m2epro_synchronization_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
          ('/buy/other_listings/title/', 'mode', '1', '0 - disable, \r\n1 - enable',
           '2013-05-08 00:00:00', '2013-05-08 00:00:00');

SQL
    );
}

// ---------------------------------------

$installer->run(<<<SQl

    UPDATE `m2epro_lock_item`
    SET `nick` = REPLACE(
        `nick`,
        'synchronization_ebay_other_listings',
        'synchronization_ebay_other_listings_update'
    );

    UPDATE `m2epro_lock_item`
    SET `nick` = REPLACE(
        `nick`,
        'synchronization_amazon_other_listings',
        'synchronization_amazon_other_listings_update'
    );

    UPDATE `m2epro_lock_item`
    SET `nick` = REPLACE(
        `nick`,
        'synchronization_buy_other_listings',
        'synchronization_buy_other_listings_update'
    );

    UPDATE `m2epro_processing_request`
    SET `responser_model` = 'M2ePro/Amazon_Synchronization_OtherListings_Update_Responser'
    WHERE `responser_model` = 'M2ePro/Amazon_Synchronization_OtherListings_Responser';

    UPDATE `m2epro_processing_request`
    SET `responser_model` = 'M2ePro/Buy_Synchronization_OtherListings_Update_Responser'
    WHERE `responser_model` = 'M2ePro/Buy_Synchronization_OtherListings_Responser';

    UPDATE `m2epro_synchronization_config`
    SET `value` = 1
    WHERE (`group` = '/ebay/orders/' AND `key` = 'mode')
    OR    (`group` = '/ebay/feedbacks/' AND `key` = 'mode')
    OR    (`group` = '/ebay/other_listings/' AND `key` = 'mode');

    UPDATE `m2epro_synchronization_config`
    SET `value` = 1
    WHERE (`group` = '/amazon/orders/' AND `key` = 'mode')
    OR    (`group` = '/amazon/other_listings/' AND `key` = 'mode');

    UPDATE `m2epro_synchronization_config`
    SET `value` = 1
    WHERE (`group` = '/buy/orders/' AND `key` = 'mode')
    OR    (`group` = '/buy/other_listings/' AND `key` = 'mode');

    UPDATE `m2epro_synchronization_config`
    SET `value` = '86400'
    WHERE `group` = '/amazon/other_listings/update/' AND `key` = 'interval';

    UPDATE `m2epro_synchronization_config`
    SET `value` = '86400'
    WHERE `group` = '/buy/other_listings/update/' AND `key` = 'interval';

    UPDATE `m2epro_synchronization_config`
    SET `group` = '/defaults/added_products/'
    WHERE `group` = '/defaults/inspector/auto_actions/';

    UPDATE `m2epro_wizard`
    SET `step` = 'account'
    WHERE `step` = 'synchronization'
    AND (`nick` = 'amazon' OR `nick` = 'buy');

    UPDATE `m2epro_wizard` as `mw`
    SET `mw`.`status` = 3
    WHERE `mw`.`nick` = 'ebayProductDetails'
    AND (
        (SELECT `mc`.`value`
         FROM `m2epro_config` as `mc`
         WHERE `mc`.`value` IS NOT NULL
         AND `mc`.`group` = '/component/ebay/'
         AND `mc`.`key` = 'mode'
         LIMIT 1) < 1

         OR

        (SELECT `mc`.`value`
         FROM `m2epro_config` as `mc`
         WHERE `mc`.`value` IS NOT NULL
         AND `mc`.`group` = '/component/ebay/'
         AND `mc`.`key` = 'allowed'
         LIMIT 1) < 1

         OR

         (SELECT COUNT(`mm`.`id`) FROM `m2epro_marketplace` as `mm`
          WHERE `mm`.`component_mode` = 'ebay'
          AND `mm`.`status` = 1) = 0
    );

    UPDATE `m2epro_wizard` as `mw`
    SET `mw`.`status` = 3
    WHERE `mw`.`nick` = 'migrationNewAmazon'
    AND (
        (SELECT `mc`.`value`
         FROM `m2epro_config` as `mc`
         WHERE `mc`.`value` IS NOT NULL
         AND `mc`.`group` = '/component/amazon/'
         AND `mc`.`key` = 'mode'
         LIMIT 1) < 1

         OR

        (SELECT `mc`.`value`
         FROM `m2epro_config` as `mc`
         WHERE `mc`.`value` IS NOT NULL
         AND `mc`.`group` = '/component/amazon/'
         AND `mc`.`key` = 'allowed'
         LIMIT 1) < 1

         OR

         (SELECT COUNT(`mm`.`id`) FROM `m2epro_marketplace` as `mm`
          WHERE `mm`.`component_mode` = 'amazon'
          AND `mm`.`status` = 1) = 0
    );

    UPDATE `m2epro_marketplace`
    SET `status` = 0
    WHERE id = 33
    AND (
        (SELECT `mc`.`value`
         FROM `m2epro_config` as `mc`
         WHERE `mc`.`value` IS NOT NULL
         AND `mc`.`group` = '/component/buy/'
         AND `mc`.`key` = 'mode'
         LIMIT 1) < 1

         OR

        (SELECT `mc`.`value`
         FROM `m2epro_config` as `mc`
         WHERE `mc`.`value` IS NOT NULL
         AND `mc`.`group` = '/component/buy/'
         AND `mc`.`key` = 'allowed'
         LIMIT 1) < 1
    );

    UPDATE `m2epro_marketplace`
    SET `sorder` = 5
    WHERE `code` = 'IT';

    UPDATE `m2epro_marketplace`
    SET `sorder` = 7
    WHERE `code` = 'FR';

    UPDATE `m2epro_marketplace`
    SET `sorder` = 8
    WHERE `code` = 'ES';

SQl
);

//########################################

$tempTable = $installer->getTable('m2epro_synchronization_log');

$logRows = $connection->query("
    SELECT *
    FROM `{$tempTable}`
    WHERE description REGEXP '%(sl|el)%'
");

$newLogsData = array();
while ($row = $logRows->fetch()) {

    if ($row['description']{0} != '{') {
        continue;
    }

    $descriptionData = json_decode($row['description'], true);

    $descriptionData['string'] = str_replace(array('%sl%', '%el%'), '', $descriptionData['string']);

    if (isset($descriptionData['params']['!sl'])) {
        unset($descriptionData['params']['!sl']);
    }

    if (isset($descriptionData['params']['!el'])) {
        unset($descriptionData['params']['!el']);
    }

    $newLogsData[json_encode($descriptionData)][] = $row['id'];
}

if (count($newLogsData) != 0) {

    foreach ($newLogsData as $description => $ids) {

        $mysqlIds = implode(',', $ids);
        $connection->update(
            $tempTable,
            array('description' => $description),
            "`id` IN ({$mysqlIds})"
        );
    }
}

//########################################

$tempTable = $installer->getTable('m2epro_ebay_template_description');

$result = $connection->query("
  SELECT `template_description_id`, `product_details`
  FROM {$tempTable}")
    ->fetchAll(PDO::FETCH_ASSOC);

if ($result !== false) {

    foreach ($result as $row) {

        if (empty($row['product_details'])) {
            continue;
        }

        $productDetails = json_decode($row['product_details'], true);

        if (isset($productDetails['gtin'])) {
            unset($productDetails['gtin']);
        }

        if (isset($productDetails['list_if_no_product'])) {
            unset($productDetails['list_if_no_product']);
        }

        $productDetails = $connection->quote(json_encode($productDetails));

        $installer->run("UPDATE `m2epro_ebay_template_description`
                         SET `product_details` = {$productDetails}
                         WHERE `template_description_id` = {$row['template_description_id']}");
    }
}

//########################################

$tempTable = $installer->getTable('m2epro_processing_request');

$processingRequests = $connection->query("
    SELECT *
    FROM `{$tempTable}`
    WHERE responser_model REGEXP '^M2ePro\/Connector_(Amazon|Buy){1}_Product_*'
")->fetchAll();

foreach ($processingRequests as &$processingRequest) {

    if (empty($processingRequest['responser_params'])) {
        continue;
    }

    $responserParams = json_decode($processingRequest['responser_params'], true);

    if (empty($responserParams['products'])) {
        continue;
    }

    $products = array();

    foreach ($responserParams['products'] as $id => $product) {

        if (!empty($responserParams['params']['only_data'])) {
            $configurator = array(
                'mode'               => 'partial',
                'allowed_data_types' => array_keys($responserParams['params']['only_data']),
            );
        } else {
            $configurator = array(
                'mode' => 'full',
            );
        }

        $products[$id] = array(
            'request'      => $product,
            'configurator' => $configurator,
        );
    }

    $responserParams['products'] = $products;

    unset($responserParams['params']['all_data'], $responserParams['params']['only_data']);
    $processingRequest['responser_params'] = json_encode($responserParams);
}

if (!empty($processingRequests)) {
    $connection->insertOnDuplicate($tempTable, $processingRequests, array('responser_params'));
}

//########################################

$installer->endSetup();

//########################################