<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//########################################

/*
    ### less getSellerEvents calls for eBay
    ### -------------------------------

    ALTER TABLE `m2epro_ebay_account`
        ADD COLUMN `defaults_last_synchronization` datetime NULL DEFAULT NULL AFTER `marketplaces_data`;

    ### -------------------------------

    ### fix for amazon other listings online_qty can be NULL
    ### -------------------------------

    ALTER TABLE `m2epro_amazon_listing_other`
        CHANGE COLUMN `online_qty` `online_qty` INT(11) UNSIGNED NULL DEFAULT NULL;

    ### -------------------------------

    ###  ebay: add ability to store several attributes for Configurable Product Images selection
    ### -------------------------------

    ALTER TABLE `m2epro_ebay_template_description`
        CHANGE COLUMN `variation_configurable_images` `variation_configurable_images` TEXT NULL DEFAULT NULL;

    ### -------------------------------

    ### Orders Log Table modification
    ### -------------------------------

    ALTER TABLE `m2epro_order_log`
        ADD COLUMN `update_date` datetime NULL DEFAULT NULL AFTER `additional_data`,
        CHANGE COLUMN `message` `description` TEXT NULL DEFAULT NULL;

    ### -------------------------------

    ### increasing of price by vat value for Amazon, Rakuten.com
    ### -------------------------------

    ALTER TABLE `m2epro_amazon_template_selling_format`
        ADD COLUMN `price_vat_percent` FLOAT UNSIGNED NOT NULL DEFAULT 0 AFTER `sale_price_end_date_custom_attribute`;

    ALTER TABLE `m2epro_buy_template_selling_format`
        ADD COLUMN `price_vat_percent` FLOAT UNSIGNED NOT NULL DEFAULT 0 AFTER `price_variation_mode`;

    ### -------------------------------

    ### ebay / amazon / buy: adding to auto add/remove rules "not visible individually products" option
    ### -------------------------------

    ALTER TABLE `m2epro_listing`
        ADD COLUMN `auto_global_adding_add_not_visible` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1
        AFTER `auto_global_adding_mode`;

    ALTER TABLE `m2epro_listing`
        ADD COLUMN `auto_website_adding_add_not_visible` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1
        AFTER `auto_website_adding_mode`;

    ALTER TABLE `m2epro_listing_auto_category_group`
        ADD COLUMN `adding_add_not_visible` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1
        'AFTER `adding_mode`;

    ### -------------------------------

    ### renaming "specifics" to "epids"
    ### -------------------------------

    DROP TABLE IF EXISTS `m2epro_ebay_dictionary_motor_epid`;
    RENAME TABLE `m2epro_ebay_dictionary_motor_specific` TO `m2epro_ebay_dictionary_motor_epid`;

    ### -------------------------------

    ### ebay motors filters and groups
    ### -------------------------------

    CREATE TABLE `m2epro_ebay_motor_filter` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `type` TINYINT(2) UNSIGNED NOT NULL,
        `conditions` TEXT DEFAULT NULL,
        `note` TEXT DEFAULT NULL,
        `update_date` datetime DEFAULT NULL,
        `create_date` datetime DEFAULT NULL,
        PRIMARY KEY (id),
        INDEX type (`type`)
    )
    ENGINE = MYISAM
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

    CREATE TABLE `m2epro_ebay_motor_group` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `mode` TINYINT(2) UNSIGNED NOT NULL,
        `type` TINYINT(2) UNSIGNED NOT NULL,
        `items_data` TEXT DEFAULT NULL,
        `update_date` datetime DEFAULT NULL,
        `create_date` datetime DEFAULT NULL,
        PRIMARY KEY (id),
        INDEX mode (`mode`),
        INDEX type (`type`)
    )
    ENGINE = INNODB
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

    CREATE TABLE `m2epro_ebay_motor_filter_to_group` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `filter_id` INT(11) UNSIGNED DEFAULT NULL,
        `group_id` INT(11) UNSIGNED DEFAULT NULL,
        PRIMARY KEY (id),
        INDEX filter_id (`filter_id`),
        INDEX group_id (`group_id`)
    )
    ENGINE = INNODB
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

    ### -------------------------------

    ### remove useless rakuten features
    ### -------------------------------

    ALTER TABLE `m2epro_buy_template_new_product_core`
        DROP COLUMN `asin_mode`,
        DROP COLUMN `asin_custom_attribute`;

    ALTER TABLE `m2epro_buy_account`
        DROP COLUMN `other_listings_update_titles_settings`,
        DROP COLUMN `other_listings_first_synchronization`;

    ### -------------------------------

    ### `online_sku` field for `ebay_listing_product_variation`
    ### -------------------------------

    ALTER TABLE `m2epro_ebay_listing_product_variation`
        ADD COLUMN `online_sku` VARCHAR(255) NULL DEFAULT NULL AFTER `delete`,
        ADD INDEX `online_sku` (`online_sku`);

    ### -------------------------------

    ### Accounts generalization
    ### -------------------------------

    ALTER TABLE `m2epro_ebay_account`
        CHANGE COLUMN `ebay_info` `info` text NULL DEFAULT NULL,
        ADD COLUMN `user_id` VARCHAR(255) NOT NULL AFTER `server_hash`;

    ALTER TABLE `m2epro_buy_account`
        ADD COLUMN `seller_id` VARCHAR(255) NULL DEFAULT NULL AFTER `server_hash`;

    ### -------------------------------
*/

// ---------------------------------------

$installer->getTableModifier('ebay_account')
          ->addColumn('defaults_last_synchronization', 'datetime NULL', 'NULL', 'marketplaces_data');

// ---------------------------------------

$installer->getTableModifier('amazon_listing_other')
          ->changeColumn('online_qty', 'INT(11) UNSIGNED NULL', 'NULL');

// ---------------------------------------

$installer->getTableModifier('ebay_template_description')
          ->changeColumn('variation_configurable_images', 'TEXT NULL', 'NULL');

// ---------------------------------------

$installer->getTableModifier('order_log')
          ->addColumn('update_date', 'datetime NULL', 'NULL', 'additional_data', false, false)
          ->renameColumn('message', 'description', false, false)
          ->commit();

$installer->getTableModifier('order_log')
          ->changeColumn('description', 'TEXT NULL', 'NULL');

// ---------------------------------------

$installer->getTableModifier('amazon_template_selling_format')
          ->addColumn('price_vat_percent', 'FLOAT UNSIGNED NOT NULL', 0,
                      'sale_price_end_date_custom_attribute');

// ---------------------------------------

$installer->getTableModifier('buy_template_selling_format')
          ->addColumn('price_vat_percent', 'FLOAT UNSIGNED NOT NULL', 0,
                      'price_variation_mode');

// ---------------------------------------

$installer->getTableModifier('listing')
          ->addColumn('auto_global_adding_add_not_visible', 'TINYINT(2) UNSIGNED NOT NULL', 1,
                      'auto_global_adding_mode');

// ---------------------------------------

$installer->getTableModifier('listing')
          ->addColumn('auto_website_adding_add_not_visible', 'TINYINT(2) UNSIGNED NOT NULL', 1,
                      'auto_website_adding_mode');

// ---------------------------------------

$installer->getTableModifier('listing_auto_category_group')
          ->addColumn('adding_add_not_visible', 'TINYINT(2) UNSIGNED NOT NULL', 1,
                      'adding_mode');

// ---------------------------------------

if ($installer->getTablesObject()->isExists('ebay_dictionary_motor_specific')) {

    $installer->run(<<<SQL

    DROP TABLE IF EXISTS `m2epro_ebay_dictionary_motor_epid`;
    RENAME TABLE `m2epro_ebay_dictionary_motor_specific` TO `m2epro_ebay_dictionary_motor_epid`;

SQL
    );
}

// ---------------------------------------

if (!$installer->getTablesObject()->isExists('ebay_motor_filter')) {

    $installer->run(<<<SQL

    CREATE TABLE `m2epro_ebay_motor_filter` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `type` TINYINT(2) UNSIGNED NOT NULL,
        `conditions` TEXT NOT NULL,
        `note` TEXT DEFAULT NULL,
        `update_date` datetime DEFAULT NULL,
        `create_date` datetime DEFAULT NULL,
        PRIMARY KEY (id),
        INDEX type (`type`)
    )
    ENGINE = MYISAM
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

SQL
    );
}

// ---------------------------------------

if (!$installer->getTablesObject()->isExists('ebay_motor_group')) {

    $installer->run(<<<SQL

    CREATE TABLE `m2epro_ebay_motor_group` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `mode` TINYINT(2) UNSIGNED NOT NULL,
        `type` TINYINT(2) UNSIGNED NOT NULL,
        `items_data` TEXT DEFAULT NULL,
        `update_date` datetime DEFAULT NULL,
        `create_date` datetime DEFAULT NULL,
        PRIMARY KEY (id),
        INDEX mode (`mode`),
        INDEX type (`type`)
    )
    ENGINE = INNODB
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

SQL
    );
}

// ---------------------------------------

if (!$installer->getTablesObject()->isExists('ebay_motor_filter_to_group')) {

    $installer->run(<<<SQL

    CREATE TABLE `m2epro_ebay_motor_filter_to_group` (
        id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `filter_id` INT(11) UNSIGNED NOT NULL,
        `group_id` INT(11) UNSIGNED NOT NULL,
        PRIMARY KEY (id),
        INDEX filter_id (`filter_id`),
        INDEX group_id (`group_id`)
    )
    ENGINE = INNODB
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

SQL
    );
}

// ---------------------------------------

$installer->getTableModifier('buy_template_new_product_core')
          ->dropColumn('asin_mode', false, false)
          ->dropColumn('asin_custom_attribute', false, false)
          ->commit();

// ---------------------------------------

$installer->getTableModifier('buy_account')
          ->dropColumn('other_listings_update_titles_settings', false, false)
          ->dropColumn('other_listings_first_synchronization', false, false)
          ->commit();

// ---------------------------------------

$installer->getTableModifier('ebay_listing_product_variation')
          ->addColumn('online_sku', 'VARCHAR(255) NULL', 'NULL', 'delete', true);

// ---------------------------------------

$installer->getTableModifier('ebay_account')
          ->renameColumn('ebay_info', 'info', false, false)
          ->addColumn('user_id', 'VARCHAR(255) NOT NULL', NULL, 'server_hash', false, false)
          ->commit();

// --------------------------------------------

$installer->getTableModifier('buy_account')
          ->addColumn('seller_id', 'VARCHAR(255) NULL', 'NULL', 'server_hash', false);

//########################################

/*
    DELETE FROM `m2epro_synchronization_config`
    WHERE `group` = '/ebay/defaults/update_listings_products/'
    AND `key` = 'since_time';
*/

$tempConfigEntity = $installer->getSynchConfigModifier()
                               ->getEntity('/ebay/defaults/update_listings_products/',
                                           'since_time');

if ($tempConfigEntity->isExists()) {

    $tempValue = $tempConfigEntity->getValue();
    $sinceTime = !$tempValue ? 'NULL' : $connection->quote($tempValue);

    $tempConfigEntity->delete();

    $installer->run(<<<SQL
        UPDATE `m2epro_ebay_account`
        SET `defaults_last_synchronization` = {$sinceTime}
SQL
    );
}

//########################################

/*
    UPDATE `m2epro_config`
    SET `key` = 'runner'
    WHERE `group` = '/cron/' AND
          `key` = 'type';

    UPDATE `m2epro_config`
    SET `key` = 'last_runner_change'
    WHERE `group` = '/cron/' AND
          `key` = 'last_type_change';

    INSERT INTO `m2epro_config`(`group`, `key`, `value`)
    VALUES ('/cron/', 'last_executed_slow_task', null);
*/

$installer->getMainConfigModifier()->getEntity('/cron/', 'type')->updateKey('runner');
$installer->getMainConfigModifier()->getEntity('/cron/', 'last_type_change')->updateKey('last_runner_change');
$installer->getMainConfigModifier()->getEntity('/cron/', 'last_executed_slow_task')->insert(null);

//########################################

/*
    DELETE FROM `m2epro_config`
    WHERE `group` = '/cron/task/processing/';

    INSERT INTO `m2epro_synchronization_config`(`group`, `key`, `value`)
    VALUES ('/defaults/processing/', 'mode', 1);
*/

$mcModifier = $installer->getMainConfigModifier();
$mcModifier->delete('/cron/task/processing/');

$scModifier = $installer->getSynchConfigModifier();
$scModifier->insert('/defaults/processing/', 'mode', 1);

$installer->run(<<<SQL

DELETE FROM `m2epro_lock_item`
WHERE `nick` = 'cron'
OR    `nick` = 'processing_cron'

SQL
);

//########################################

/*
    UPDATE `m2epro_config`
    SET `group` = '/ebay/motors/', `key` = 'epids_attribute'
    WHERE `group` = '/ebay/motor/' AND `key` = 'motors_specifics_attribute';

    UPDATE `m2epro_config`
    SET `group` = '/ebay/motors/', `key` = 'ktypes_attribute'
    WHERE `group` = '/ebay/motor/' AND `key` = 'motors_ktypes_attribute';

    UPDATE `m2epro_config`
    SET `group` = '/view/ebay/motors_epids_attribute/'
    WHERE `group` = '/view/ebay/motors_specifics_attribute/';

    UPDATE `m2epro_synchronization_config`
    SET `group` = '/ebay/marketplaces/motors_epids/'
    WHERE `group` = '/ebay/marketplaces/motors_specifics/';
*/

$installer->getMainConfigModifier()
    ->updateGroup('/ebay/motors/', array('`group` = ?' => '/ebay/motor/'));
$installer->getMainConfigModifier()
    ->getEntity('/ebay/motors/','motors_specifics_attribute')->updateKey('epids_attribute');
$installer->getMainConfigModifier()
    ->getEntity('/ebay/motors/','motors_ktypes_attribute')->updateKey('ktypes_attribute');

$installer->getMainConfigModifier()->updateGroup(
    '/view/ebay/motors_epids_attribute/',
    array('`group` = ?' => '/view/ebay/motors_specifics_attribute/')
);

$installer->getSynchConfigModifier()->updateGroup(
    '/ebay/marketplaces/motors_epids/',
    array('`group` = ?' => '/ebay/marketplaces/motors_specifics/')
);

//########################################

/*
    DELETE FROM `m2epro_synchronization_config`
    WHERE `group` = '/buy/other_listings/title/'
    AND `key` = 'mode';
*/

$installer->getSynchConfigModifier()
          ->delete('/buy/other_listings/title/', 'mode');

//########################################

$installer->run(<<<SQL

    UPDATE `m2epro_ebay_template_selling_format`
    SET `fixed_price_mode` = 1
    WHERE `listing_type` = 2 AND `fixed_price_mode` = 0;

    UPDATE `m2epro_amazon_listing_other`
    SET `online_qty` = NULL
    WHERE `is_afn_channel` = 1 AND `online_qty` IS NOT NULL;

    UPDATE `m2epro_amazon_listing_other`
    SET `online_qty` = 0
    WHERE `is_afn_channel` = 0 AND `online_qty` IS NULL;

    UPDATE `m2epro_ebay_template_description`
    SET `variation_configurable_images` = '[]'
    WHERE LENGTH(`variation_configurable_images`) = 0;

    UPDATE `m2epro_ebay_template_description`
    SET `variation_configurable_images` = CONCAT('["', `variation_configurable_images`, '"]')
    WHERE `variation_configurable_images` NOT LIKE '[%';

    UPDATE `m2epro_buy_listing`
    SET `general_id_mode` = 0,
        `general_id_custom_attribute` = ''
    WHERE `general_id_mode` NOT IN(0,1);

    UPDATE `m2epro_listing_product`
    SET `additional_data` = CONCAT(
                                SUBSTRING(`additional_data`,
                                          1,
                                          INSTR(`additional_data`, '"ebay_product_images_hash":"') - 1
                                          + LENGTH('"ebay_product_images_hash":"') + 40
                                ),
                                SUBSTRING(`additional_data`,
                                          INSTR(`additional_data`, '"ebay_product_images_hash":"')
                                          + LENGTH('"ebay_product_images_hash":"') + 40 + 9
                                )
                            )
    WHERE `additional_data` REGEXP '"ebay_product_images_hash":[^#]+#[0-9]{8}"';

    UPDATE `m2epro_listing_product`
    SET `additional_data` = CONCAT(
                                SUBSTRING(`additional_data`,
                                          1,
                                          INSTR(`additional_data`, '"ebay_product_variation_images_hash":"') - 1
                                          + LENGTH('"ebay_product_variation_images_hash":"') + 40
                                ),
                                SUBSTRING(`additional_data`,
                                          INSTR(`additional_data`, '"ebay_product_variation_images_hash":"')
                                          + LENGTH('"ebay_product_variation_images_hash":"') + 40 + 9
                                )
                            )
    WHERE `additional_data` REGEXP '"ebay_product_variation_images_hash":[^#]+#[0-9]{8}"';

SQL
);

//########################################

$accountTable = $installer->getTablesObject()->getFullName('account');
$ebayAccountTable = $installer->getTablesObject()->getFullName('ebay_account');

$result = $installer->getConnection()->query("
  SELECT ma.title, mea.info, ma.id
  FROM {$accountTable} ma
  INNER JOIN {$ebayAccountTable} mea ON ma.id = mea.account_id;
")->fetchAll(PDO::FETCH_ASSOC);

if ($result !== false) {

    foreach ($result as $row) {

        $accountInfo = @json_decode($row['info'], true);

        if (empty($accountInfo['UserID'])) {
            $userId = trim(preg_replace('/\(\d+\)$/', '', $row['title']));
        } else {
            $userId = $accountInfo['UserID'];
        }

        $userId = $connection->quote($userId);

        $installer->run(<<<SQL
            UPDATE `m2epro_ebay_account`
            SET `user_id` = {$userId}
            WHERE `account_id` = {$row['id']};
SQL
        );
    }
}

//########################################

$result = $connection->query("
    SELECT `account_id`,
           `other_listings_mapping_settings`
    FROM `{$installer->getTablesObject()->getFullName('buy_account')}`
")->fetchAll(PDO::FETCH_ASSOC);

if ($result !== false) {

    foreach ($result as $row) {

        $settings = @json_decode($row['other_listings_mapping_settings'], true);

        if (isset($settings['title'])) {

            unset($settings['title']);
            $settings = $connection->quote(json_encode($settings));

            $installer->run(<<<SQL
                UPDATE `m2epro_buy_account`
                SET `other_listings_mapping_settings` = {$settings}
                WHERE `account_id` = {$row['account_id']}
SQL
            );
        }
    }
}

//########################################

if ($installer->getTablesObject()->isExists('locked_object') &&
    $installer->getTablesObject()->isExists('processing_request')) {

    $installer->run(<<<SQL

DELETE `mlo`
FROM `m2epro_locked_object` AS `mlo`
LEFT JOIN `m2epro_processing_request` AS `mpr` ON `mlo`.`related_hash` = `mpr`.`hash`
WHERE `mpr`.`id` IS NULL;

SQL
    );
}

//########################################

$installer->endSetup();

//########################################