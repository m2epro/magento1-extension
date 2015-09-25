<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE `m2epro_ebay_marketplace`
    DROP COLUMN `categories_version`,
    CHANGE COLUMN `currency` `currency` VARCHAR(70) NOT NULL DEFAULT 'USD',
    ADD COLUMN `is_metric_measurement_system` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0
                AFTER `is_english_measurement_system`,
    ADD INDEX `is_metric_measurement_system` (`is_metric_measurement_system`);

    ALTER TABLE `m2epro_ebay_template_synchronization`
    ADD COLUMN `revise_update_gallery` TINYINT(2) UNSIGNED NOT NULL AFTER `revise_update_description`;

    ALTER TABLE `m2epro_order_change`
    ADD COLUMN `processing_attempt_count` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `creator_type`,
    ADD COLUMN `processing_attempt_date` DATETIME DEFAULT NULL AFTER `processing_attempt_count`,
    ADD INDEX `processing_attempt_count` (`processing_attempt_count`);

    ALTER TABLE `m2epro_ebay_template_shipping`
    CHANGE COLUMN `local_shipping_combined_discount_profile_id`
                    `local_shipping_combined_discount_profile_id` TEXT DEFAULT NULL,
    CHANGE COLUMN `international_shipping_combined_discount_profile_id`
                    `international_shipping_combined_discount_profile_id` TEXT DEFAULT NULL,
    ADD COLUMN `tax_category_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `tax_table_mode`,
    ADD COLUMN `tax_category_value` VARCHAR(255) NOT NULL AFTER `tax_category_mode`,
    ADD COLUMN `tax_category_attribute` VARCHAR(255) NOT NULL AFTER `tax_category_value`;

    ALTER TABLE `m2epro_ebay_dictionary_marketplace`
    ADD COLUMN `client_categories_version` INT(11) UNSIGNED NULL DEFAULT NULL after `marketplace_id`,
    ADD COLUMN `server_categories_version` INT(11) UNSIGNED NULL DEFAULT NULL after `client_categories_version`;
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_marketplace');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'categories_version') !== false) {
    $connection->dropColumn($tempTable,'categories_version');
}

if ($connection->tableColumnExists($tempTable, 'currency') !== false) {
    $connection->changeColumn(
        $tempTable,
        'currency',
        'currency',
        'VARCHAR(70) NOT NULL DEFAULT \'USD\'');
}

if ($connection->tableColumnExists($tempTable, 'is_metric_measurement_system') === false) {
    $connection->addColumn(
        $tempTable,
        'is_metric_measurement_system',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_english_measurement_system`'
    );
}

if (!isset($tempTableIndexList[strtoupper('is_metric_measurement_system')])) {
    $connection->addKey($tempTable, 'is_metric_measurement_system', 'is_metric_measurement_system');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_synchronization');

if ($connection->tableColumnExists($tempTable, 'revise_update_gallery') === false) {
    $connection->addColumn(
        $tempTable,
        'revise_update_gallery',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `revise_update_description`'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_order_change');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'processing_attempt_count') === false) {
    $connection->addColumn(
        $tempTable,
        'processing_attempt_count',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `creator_type`'
    );
}

if ($connection->tableColumnExists($tempTable, 'processing_attempt_date') === false) {
    $connection->addColumn(
        $tempTable,
        'processing_attempt_date',
        'DATETIME DEFAULT NULL AFTER `processing_attempt_count`'
    );
}

if (!isset($tempTableIndexList[strtoupper('processing_attempt_count')])) {
    $connection->addKey($tempTable, 'processing_attempt_count', 'processing_attempt_count');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_dictionary_marketplace');

if ($connection->tableColumnExists($tempTable, 'client_categories_version') === false) {
    $connection->addColumn(
        $tempTable,
        'client_categories_version',
        'INT(11) UNSIGNED NULL DEFAULT NULL AFTER `marketplace_id`'
    );
}

if ($connection->tableColumnExists($tempTable, 'server_categories_version') === false) {
    $connection->addColumn(
        $tempTable,
        'server_categories_version',
        'INT(11) UNSIGNED NULL DEFAULT NULL AFTER `client_categories_version`'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_shipping');

if ($connection->tableColumnExists($tempTable, 'local_shipping_combined_discount_profile_id')  != false) {
    $connection->changeColumn(
        $tempTable,
        'local_shipping_combined_discount_profile_id',
        'local_shipping_combined_discount_profile_id',
        'TEXT DEFAULT NULL');
}

if ($connection->tableColumnExists($tempTable, 'international_shipping_combined_discount_profile_id') != false) {
    $connection->changeColumn(
        $tempTable,
        'international_shipping_combined_discount_profile_id',
        'international_shipping_combined_discount_profile_id',
        'TEXT DEFAULT NULL');
}

if ($connection->tableColumnExists($tempTable, 'tax_category_mode') === false) {
    $connection->addColumn(
        $tempTable,
        'tax_category_mode',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `tax_table_mode`'
    );
}

if ($connection->tableColumnExists($tempTable, 'tax_category_value') === false) {
    $connection->addColumn(
        $tempTable,
        'tax_category_value',
        'VARCHAR(255) NOT NULL AFTER `tax_category_mode`'
    );
}

if ($connection->tableColumnExists($tempTable, 'tax_category_attribute') === false) {
    $connection->addColumn(
        $tempTable,
        'tax_category_attribute',
        'VARCHAR(255) NOT NULL AFTER `tax_category_value`'
    );
}

//#############################################

$shippingTable = $installer->getTable('m2epro_ebay_template_shipping');
$listingMainTable = $installer->getTable('m2epro_listing');
$listingSecondTable = $installer->getTable('m2epro_ebay_listing');
$listingProdMainTable = $installer->getTable('m2epro_listing_product');
$listingProdSecondTable = $installer->getTable('m2epro_ebay_listing_product');

$combinedData = $connection->query("
SELECT *
FROM (
        (SELECT `listing`.`account_id`,
                `shipping`.`id` AS `shipping_id`,
                `shipping`.`local_shipping_combined_discount_profile_id` AS `shipping_local`,
                `shipping`.`international_shipping_combined_discount_profile_id` AS `shipping_international`
        FROM `{$listingSecondTable}` AS `ebayListing`
        INNER JOIN `{$listingMainTable}` AS `listing` ON `listing`.`id` = `ebayListing`.`listing_id`
        RIGHT JOIN `{$shippingTable}` AS `shipping` ON (`shipping`.`id` = `ebayListing`.`template_shipping_id` OR
                                                        `shipping`.`id` = `ebayListing`.`template_shipping_custom_id`)
        )
        UNION
       (SELECT  `listing`.`account_id`,
                `shipping`.`id` AS `shipping_id`,
                `shipping`.`local_shipping_combined_discount_profile_id` AS `shipping_local`,
                `shipping`.`international_shipping_combined_discount_profile_id` AS `shipping_international`
       FROM `{$listingProdSecondTable}` AS `ebayListingP`
       INNER JOIN `{$listingProdMainTable}` AS `listingP` ON `listingP`.`id` = `ebayListingP`.`listing_product_id`
       INNER JOIN `{$listingMainTable}` AS `listing` ON `listing`.`id` = `listingP`.`listing_id`
       RIGHT JOIN `{$shippingTable}` AS `shipping` ON (`shipping`.`id` = `ebayListingP`.`template_shipping_id` OR
                                                        `shipping`.`id` = `ebayListingP`.`template_shipping_custom_id`)
        )
) AS `combined_data`
WHERE `combined_data`.`shipping_local` IS NOT NULL OR `combined_data`.`shipping_international` IS NOT NULL
")->fetchAll();

if (!empty($combinedData)) {

    $combinedChippingProfilerChange = true;
    $tempData = array();

    foreach ($combinedData as $data) {

        if (is_null($data['account_id'])) {
            if (!isset($tempData[$data['shipping_id']])) {
                !is_null($data['shipping_local']) && $tempData[$data['shipping_id']]['local'] = array();
                !is_null($data['shipping_international']) && $tempData[$data['shipping_id']]['international'] = array();
            }
            continue;
        }

        if (!is_null($data['shipping_local'])) {

            if ((int)$data['shipping_local'] == 0) {
                $combinedChippingProfilerChange = false;
                break;
            }

            $tempData[$data['shipping_id']]['local'][$data['account_id']] = $data['shipping_local'];
        }

        if (!is_null($data['shipping_international'])) {

            if ((int)$data['shipping_international'] == 0) {
                $combinedChippingProfilerChange = false;
                break;
            }

            $tempData[$data['shipping_id']]['international'][$data['account_id']] = $data['shipping_international'];
        }
    }

    if ($combinedChippingProfilerChange) {
        foreach ($tempData as $shippingId => $dataType) {
            foreach ($dataType as $type => $data) {
                $connection->query("UPDATE {$shippingTable}
                                    SET `{$type}_shipping_combined_discount_profile_id` = '" . json_encode($data) . "'
                                    WHERE `id` = " . (int)$shippingId);
            }
        }
    }
}

//#############################################

/*
    ALTER TABLE `m2epro_ebay_template_category`
    ADD COLUMN `marketplace_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
    ADD INDEX `marketplace_id` (`marketplace_id`);
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_category');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'marketplace_id') === false) {

    $connection->addColumn(
        $tempTable,
        'marketplace_id',
        'INT(11) UNSIGNED NOT NULL AFTER `id`'
    );

    $installer->run(<<<SQL

        UPDATE `m2epro_ebay_template_category` AS `etc`
        INNER JOIN `m2epro_ebay_listing_product` AS `elp` ON `elp`.`template_category_id` = `etc`.`id`
        INNER JOIN `m2epro_listing_product` AS `lp` ON `lp`.`id` = `elp`.`listing_product_id`
        INNER JOIN `m2epro_listing` AS `l` ON `lp`.`listing_id` = `l`.`id`
        SET `etc`.`marketplace_id` = `l`.`marketplace_id`;

        UPDATE `m2epro_ebay_template_category` AS `etc`
        INNER JOIN `m2epro_ebay_listing` AS `el` ON `el`.`auto_global_adding_template_category_id` = `etc`.`id`
        INNER JOIN `m2epro_listing` AS `l` ON `el`.`listing_id` = `l`.`id`
        SET `etc`.`marketplace_id` = `l`.`marketplace_id`;

        UPDATE `m2epro_ebay_template_category` AS `etc`
        INNER JOIN `m2epro_ebay_listing` AS `el` ON `el`.`auto_website_adding_template_category_id` = `etc`.`id`
        INNER JOIN `m2epro_listing` AS `l` ON `el`.`listing_id` = `l`.`id`
        SET `etc`.`marketplace_id` = `l`.`marketplace_id`;

        UPDATE `m2epro_ebay_template_category` AS `etc`
        INNER JOIN `m2epro_ebay_listing_auto_category` AS `elac` ON `elac`.`adding_template_category_id` = `etc`.`id`
        INNER JOIN `m2epro_listing` AS `l` ON `elac`.`listing_id` = `l`.`id`
        SET `etc`.`marketplace_id` = `l`.`marketplace_id`;

SQL
);
}

if (!isset($tempTableIndexList[strtoupper('marketplace_id')])) {
    $connection->addKey($tempTable, 'marketplace_id', 'marketplace_id');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_other_category');
$otherCategoryTemplateTableExists = $installer->tableExists($tempTable);

if (!$otherCategoryTemplateTableExists) {

    $installer->run(<<<SQL

    CREATE TABLE IF NOT EXISTS `m2epro_ebay_template_other_category` (
      id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      marketplace_id int(11) UNSIGNED NOT NULL,
      account_id int(11) UNSIGNED NOT NULL,
      category_secondary_id int(11) UNSIGNED NOT NULL,
      category_secondary_path varchar(255) DEFAULT NULL,
      category_secondary_mode tinyint(2) UNSIGNED NOT NULL DEFAULT 2,
      category_secondary_attribute varchar(255) NOT NULL,
      store_category_main_id decimal(20, 0) UNSIGNED NOT NULL,
      store_category_main_path varchar(255) DEFAULT NULL,
      store_category_main_mode tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
      store_category_main_attribute varchar(255) NOT NULL,
      store_category_secondary_id decimal(20, 0) UNSIGNED NOT NULL,
      store_category_secondary_path varchar(255) DEFAULT NULL,
      store_category_secondary_mode tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
      store_category_secondary_attribute varchar(255) NOT NULL,
      update_date datetime DEFAULT NULL,
      create_date datetime DEFAULT NULL,
      PRIMARY KEY (id),
      INDEX account_id (account_id),
      INDEX marketplace_id (marketplace_id)
    )
    ENGINE = INNODB
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

    INSERT INTO `m2epro_ebay_template_other_category`
    SELECT `id`,
           `marketplace_id`,
           NULL,

           category_secondary_id,
           category_secondary_path,
           category_secondary_mode,
           category_secondary_attribute,

           store_category_main_id,
           store_category_main_path,
           store_category_main_mode,
           store_category_main_attribute,

           store_category_secondary_id,
           store_category_secondary_path,
           store_category_secondary_mode,
           store_category_secondary_attribute,

           update_date,
           create_date
    FROM `m2epro_ebay_template_category`;

SQL
);
}

//#############################################

/*
    ALTER TABLE `m2epro_ebay_listing_product`
    ADD COLUMN template_other_category_id int(11) UNSIGNED DEFAULT NULL AFTER `template_category_id`,
    ADD INDEX `template_other_category_id` (`template_other_category_id`);

    ALTER TABLE `m2epro_ebay_listing`
    ADD COLUMN auto_global_adding_template_other_category_id INT(11) UNSIGNED DEFAULT NULL
        AFTER `auto_global_adding_template_category_id`,
    ADD COLUMN auto_website_adding_template_other_category_id INT(11) UNSIGNED DEFAULT NULL
        AFTER `auto_website_adding_template_category_id`,
    ADD INDEX `auto_global_adding_template_other_category_id` (`auto_global_adding_template_other_category_id`),
    ADD INDEX `auto_website_adding_template_other_category_id` (`auto_website_adding_template_other_category_id`);

    ALTER TABLE `m2epro_ebay_listing_auto_category`
    ADD COLUMN adding_template_other_category_id INT(11) UNSIGNED DEFAULT NULL AFTER `adding_template_category_id`,
    ADD INDEX `adding_template_other_category_id` (`adding_template_other_category_id`);
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_listing_product');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'template_other_category_id') === false) {
    $connection->addColumn(
        $tempTable,
        'template_other_category_id',
        'int(11) UNSIGNED DEFAULT NULL AFTER `template_category_id`'
    );
}

if (!isset($tempTableIndexList[strtoupper('template_other_category_id')])) {
    $connection->addKey($tempTable, 'template_other_category_id', 'template_other_category_id');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_listing');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'auto_global_adding_template_other_category_id') === false) {
    $connection->addColumn(
        $tempTable,
        'auto_global_adding_template_other_category_id',
        'INT(11) UNSIGNED DEFAULT NULL AFTER `auto_global_adding_template_category_id`'
    );
}

if ($connection->tableColumnExists($tempTable, 'auto_website_adding_template_other_category_id') === false) {
    $connection->addColumn(
        $tempTable,
        'auto_website_adding_template_other_category_id',
        'INT(11) UNSIGNED DEFAULT NULL AFTER `auto_website_adding_template_category_id`'
    );
}

if (!isset($tempTableIndexList[strtoupper('auto_global_adding_template_other_category_id')])) {
    $connection->addKey(
        $tempTable,
        'auto_global_adding_template_other_category_id', 'auto_global_adding_template_other_category_id'
    );
}

if (!isset($tempTableIndexList[strtoupper('auto_website_adding_template_other_category_id')])) {
    $connection->addKey(
        $tempTable,
        'auto_website_adding_template_other_category_id', 'auto_website_adding_template_other_category_id'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_listing_auto_category');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'adding_template_other_category_id') === false) {
    $connection->addColumn(
        $tempTable,
        'adding_template_other_category_id',
        'INT(11) UNSIGNED DEFAULT NULL AFTER `adding_template_category_id`'
    );
}

if (!isset($tempTableIndexList[strtoupper('adding_template_other_category_id')])) {
    $connection->addKey($tempTable, 'adding_template_other_category_id', 'adding_template_other_category_id');
}

//---------------------------------------------

if (!$otherCategoryTemplateTableExists) {

    $installer->run(<<<SQL

        UPDATE `m2epro_ebay_listing_product`
        SET `template_other_category_id` = `template_category_id`;

        UPDATE `m2epro_ebay_listing`
        SET `auto_global_adding_template_other_category_id` = `auto_global_adding_template_category_id`,
            `auto_website_adding_template_other_category_id` = `auto_website_adding_template_category_id`;

        UPDATE `m2epro_ebay_listing_auto_category`
        SET `adding_template_other_category_id` = `adding_template_category_id`;

SQL
);
}

//---------------------------------------------

if (!$otherCategoryTemplateTableExists) {

    $installer->run(<<<SQL

        UPDATE `m2epro_ebay_template_other_category` AS `etoc`
        INNER JOIN `m2epro_ebay_listing_product` AS `elp` ON `elp`.`template_other_category_id` = `etoc`.`id`
        INNER JOIN `m2epro_listing_product` AS `lp` ON `lp`.`id` = `elp`.`listing_product_id`
        INNER JOIN `m2epro_listing` AS `l` ON `lp`.`listing_id` = `l`.`id`
        SET `etoc`.`account_id` = `l`.`account_id`;

        UPDATE `m2epro_ebay_template_other_category` AS `etoc`
        INNER JOIN `m2epro_ebay_listing` AS `el` ON `el`.`auto_global_adding_template_other_category_id` = `etoc`.`id`
        INNER JOIN `m2epro_listing` AS `l` ON `el`.`listing_id` = `l`.`id`
        SET `etoc`.`account_id` = `l`.`account_id`;

        UPDATE `m2epro_ebay_template_other_category` AS `etoc`
        INNER JOIN `m2epro_ebay_listing` AS `el` ON `el`.`auto_website_adding_template_other_category_id` = `etoc`.`id`
        INNER JOIN `m2epro_listing` AS `l` ON `el`.`listing_id` = `l`.`id`
        SET `etoc`.`account_id` = `l`.`account_id`;

        UPDATE `m2epro_ebay_template_other_category` AS `etoc`
        INNER JOIN `m2epro_ebay_listing_auto_category` AS `elac`
            ON `elac`.`adding_template_other_category_id` = `etoc`.`id`
        INNER JOIN `m2epro_listing` AS `l` ON `elac`.`listing_id` = `l`.`id`
        SET `etoc`.`account_id` = `l`.`account_id`;

SQL
);
}

//---------------------------------------------

$configTable = $installer->getTable('m2epro_config');
$tempQuery = <<<SQL
    SELECT * FROM `{$configTable}`
    WHERE `group` = '/ebay/motor/'
    AND   `key` = 'motors_specifics_attribute'
SQL;
$tempRow = $connection->query($tempQuery)->fetch();

if ($tempRow === false && !$otherCategoryTemplateTableExists) {

    $ebayTemplateCategoryTable = $installer->getTable('m2epro_ebay_template_category');

    $query = <<<SQL

        SELECT motors_specifics_attribute, COUNT(*) as `count`
        FROM `{$ebayTemplateCategoryTable}`
        GROUP BY motors_specifics_attribute
        HAVING motors_specifics_attribute IS NOT NULL
        ORDER BY `count` DESC

SQL;

    $existAttributes = $connection->query($query)->fetchAll();

    if (!empty($existAttributes)) {

        $firstRow = reset($existAttributes);
        $mostUsedAttribute = $firstRow['motors_specifics_attribute'];

        $query = <<<SQL

            INSERT INTO `{$configTable}` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
            ('/ebay/motor/', 'motors_specifics_attribute', '{$mostUsedAttribute}', NULL,
             '2013-09-18 00:00:00', '2013-09-18 00:00:00');

SQL;

        $connection->query($query);
    }
}

//---------------------------------------------

/*
    ALTER TABLE `m2epro_ebay_template_category`
    DROP COLUMN `category_secondary_id`,
    DROP COLUMN `category_secondary_path`,
    DROP COLUMN `category_secondary_mode`,
    DROP COLUMN `category_secondary_attribute`,
    DROP COLUMN `store_category_main_id`,
    DROP COLUMN `store_category_main_path`,
    DROP COLUMN `store_category_main_mode`,
    DROP COLUMN `store_category_main_attribute`,
    DROP COLUMN `store_category_secondary_id`,
    DROP COLUMN `store_category_secondary_path`,
    DROP COLUMN `store_category_secondary_mode`,
    DROP COLUMN `store_category_secondary_attribute`,
    DROP COLUMN `tax_category_mode`,
    DROP COLUMN `tax_category_value`,
    DROP COLUMN `tax_category_attribute`,
    DROP COLUMN `motors_specifics_attribute`;
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_category');

if ($connection->tableColumnExists($tempTable, 'category_secondary_id') === true) {
    $connection->dropColumn($tempTable, 'category_secondary_id');
}

if ($connection->tableColumnExists($tempTable, 'category_secondary_path') === true) {
    $connection->dropColumn($tempTable, 'category_secondary_path');
}

if ($connection->tableColumnExists($tempTable, 'category_secondary_mode') === true) {
    $connection->dropColumn($tempTable, 'category_secondary_mode');
}

if ($connection->tableColumnExists($tempTable, 'category_secondary_attribute') === true) {
    $connection->dropColumn($tempTable, 'category_secondary_attribute');
}

if ($connection->tableColumnExists($tempTable, 'store_category_main_id') === true) {
    $connection->dropColumn($tempTable, 'store_category_main_id');
}

if ($connection->tableColumnExists($tempTable, 'store_category_main_path') === true) {
    $connection->dropColumn($tempTable, 'store_category_main_path');
}

if ($connection->tableColumnExists($tempTable, 'store_category_main_mode') === true) {
    $connection->dropColumn($tempTable, 'store_category_main_mode');
}

if ($connection->tableColumnExists($tempTable, 'store_category_main_attribute') === true) {
    $connection->dropColumn($tempTable, 'store_category_main_attribute');
}

if ($connection->tableColumnExists($tempTable, 'store_category_secondary_id') === true) {
    $connection->dropColumn($tempTable, 'store_category_secondary_id');
}

if ($connection->tableColumnExists($tempTable, 'store_category_secondary_path') === true) {
    $connection->dropColumn($tempTable, 'store_category_secondary_path');
}

if ($connection->tableColumnExists($tempTable, 'store_category_secondary_mode') === true) {
    $connection->dropColumn($tempTable, 'store_category_secondary_mode');
}

if ($connection->tableColumnExists($tempTable, 'store_category_secondary_attribute') === true) {
    $connection->dropColumn($tempTable, 'store_category_secondary_attribute');
}

if ($connection->tableColumnExists($tempTable, 'tax_category_mode') === true) {
    $connection->dropColumn($tempTable, 'tax_category_mode');
}

if ($connection->tableColumnExists($tempTable, 'tax_category_value') === true) {
    $connection->dropColumn($tempTable, 'tax_category_value');
}

if ($connection->tableColumnExists($tempTable, 'tax_category_attribute') === true) {
    $connection->dropColumn($tempTable, 'tax_category_attribute');
}

if ($connection->tableColumnExists($tempTable, 'motors_specifics_attribute') === true) {
    $connection->dropColumn($tempTable, 'motors_specifics_attribute');
}

//#############################################

$tempTable = $installer->getTable('m2epro_synchronization_config');
$tempQuery = <<<SQL
    SELECT * FROM `{$tempTable}`
    WHERE `group` = '/ebay/orders/update/'
    AND   `key` = 'mode'
SQL;
$tempRow = $connection->query($tempQuery)->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO `m2epro_synchronization_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/ebay/orders/update/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2013-09-18 00:00:00', '2013-09-18 00:00:00');

SQL
);
}

//#############################################

$tempTable = $installer->getTable('m2epro_config');
$tempQuery = <<<SQL
    SELECT * FROM `{$tempTable}`
    WHERE `group` = '/view/ebay/template/category/'
    AND   `key` = 'use_last_specifics'
SQL;
$tempRow = $connection->query($tempQuery)->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/ebay/connector/listing/', 'check_the_same_product_already_listed', '1', '0 - false, \r\n1 - true',
 '2013-09-18 00:00:00', '2013-09-18 00:00:00'),
('/view/ebay/template/category/', 'use_last_specifics', '0', '0 - false, \r\n1 - true',
 '2013-09-18 00:00:00', '2013-09-18 00:00:00');

SQL
);
}

//#############################################

$installer->run(<<<SQL

UPDATE `m2epro_config`
SET `value` = 'http://docs.m2epro.com/display/eBayMagentoV6/M2E+Pro+-+User+Guide/'
WHERE `group` = '/view/ebay/support/'
AND   `key` = 'documentation_url';

UPDATE `m2epro_config`
SET `value` = 'http://docs.m2epro.com/display/eBayMagentoV6/eBay+Video+Tutorials/'
WHERE `group` = '/view/ebay/support/'
AND   `key` = 'video_tutorials_url';

UPDATE `m2epro_config`
SET `value` = 'http://docs.m2epro.com/display/AmazonRakutenPlayMagentoV6/M2E+Pro/'
WHERE `group` = '/view/common/support/'
AND   `key` = 'documentation_url';

UPDATE `m2epro_config`
SET `value` = 'http://docs.m2epro.com/display/AmazonRakutenPlayMagentoV6/Video+Tutorials/'
WHERE `group` = '/view/common/support/'
AND   `key` = 'video_tutorials_url';

UPDATE `m2epro_config`
SET `group` = '/view/ebay/template/shipping/'
WHERE `group` = '/view/ebay/template/category/'
AND `key` = 'show_tax_category';

UPDATE `m2epro_ebay_marketplace`
SET `currency` = 'CAD,USD'
WHERE `marketplace_id` IN (2,19);

UPDATE `m2epro_ebay_marketplace`
SET `currency` = 'INR'
WHERE `marketplace_id` = 16;

UPDATE `m2epro_ebay_marketplace`
SET `is_metric_measurement_system` = 1
WHERE `marketplace_id` IN (2,3,4,5,6,7,8,10,11,12,13,14,15,16,17,18,19,20,21,22,23);

UPDATE `m2epro_ebay_marketplace`
SET `is_english_measurement_system` = 1
WHERE `marketplace_id` IN (2,19);

UPDATE `m2epro_primary_config`
SET `value` = 'https://s1.m2epro.com/',
    `key` = 'baseurl_1'
WHERE `group` = '/server/'
AND   `key` = 'baseurl';

DROP TABLE IF EXISTS `m2epro_ebay_listing_auto_filter`;

SQL
);

//#############################################

if (Mage::registry('M2EPRO_IS_INSTALLATION') === true) {

    $installer->run(<<<SQL

UPDATE `m2epro_config`
SET `value` = '0'
WHERE `group` = '/component/amazon/'
AND   `key` = 'mode';

UPDATE `m2epro_config`
SET `value` = '0'
WHERE `group` = '/component/buy/'
AND   `key` = 'mode';

UPDATE `m2epro_config`
SET `value` = '0'
WHERE `group` = '/component/play/'
AND   `key` = 'mode';

SQL
);
}

//#############################################

$installer->endSetup();

//#############################################