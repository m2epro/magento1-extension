<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//########################################

if (!$installer->getTablesObject()->isExists('amazon_account_repricing')) {
    $installer->run(<<<SQL

    DROP TABLE IF EXISTS `m2epro_amazon_account_repricing`;
    CREATE TABLE `m2epro_amazon_account_repricing` (
        `account_id` INT(11) UNSIGNED NOT NULL,
        `email` VARCHAR(255) DEFAULT NULL,
        `token` VARCHAR(255) DEFAULT NULL,
        `total_products` INT(11) UNSIGNED NOT NULL DEFAULT 0,
        `regular_price_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
        `regular_price_attribute` VARCHAR(255) NOT NULL,
        `regular_price_coefficient` VARCHAR(255) NOT NULL,
        `regular_price_variation_mode` TINYINT(2) UNSIGNED NOT NULL,
        `min_price_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
        `min_price_value` DECIMAL(14, 2) UNSIGNED DEFAULT NULL,
        `min_price_percent` INT(11) UNSIGNED DEFAULT NULL,
        `min_price_attribute` VARCHAR(255) NOT NULL,
        `min_price_coefficient` VARCHAR(255) NOT NULL,
        `min_price_variation_mode` TINYINT(2) UNSIGNED NOT NULL,
        `max_price_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
        `max_price_value` DECIMAL(14, 2) UNSIGNED DEFAULT NULL,
        `max_price_percent` INT(11) UNSIGNED DEFAULT NULL,
        `max_price_attribute` VARCHAR(255) NOT NULL,
        `max_price_coefficient` VARCHAR(255) NOT NULL,
        `max_price_variation_mode` TINYINT(2) UNSIGNED NOT NULL,
        `disable_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
        `disable_mode_attribute` VARCHAR(255) NOT NULL,
        `last_checked_listing_product_update_date` DATETIME DEFAULT NULL,
        `update_date` DATETIME DEFAULT NULL,
        `create_date` DATETIME DEFAULT NULL,
        PRIMARY KEY (`account_id`)
    )
    ENGINE = INNODB
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

SQL
    );
}

if (!$installer->getTablesObject()->isExists('amazon_listing_product_repricing')) {
    $installer->run(<<<SQL

    DROP TABLE IF EXISTS `m2epro_amazon_listing_product_repricing`;
    CREATE TABLE `m2epro_amazon_listing_product_repricing` (
        `listing_product_id` INT(11) UNSIGNED NOT NULL,
        `is_online_disabled` TINYINT(2) UNSIGNED NOT NULL,
        `online_regular_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
        `online_min_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
        `online_max_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
        `is_process_required` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
        `last_synchronization_date` DATETIME DEFAULT NULL,
        `update_date` DATETIME DEFAULT NULL,
        `create_date` DATETIME DEFAULT NULL,
        PRIMARY KEY (`listing_product_id`),
        INDEX `is_online_disabled` (`is_online_disabled`),
        INDEX `is_process_required` (`is_process_required`)
    )
    ENGINE = INNODB
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

SQL
    );
}

// -------------------------------------

$installer->getTableModifier('amazon_listing_other')
    ->addColumn('is_repricing_disabled', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'is_repricing', true, false)
    ->commit();

// -------------------------------------

if ($installer->getTableModifier('amazon_listing_product')->isColumnExists('is_repricing')) {

    $installer->run(<<<SQL

    INSERT INTO `m2epro_amazon_listing_product_repricing`
        (`listing_product_id`,
        `update_date`,
        `create_date`)
        SELECT DISTINCT `malp`.`listing_product_id`, NOW(), NOW()
        FROM `m2epro_amazon_listing_product` malp
            WHERE `is_repricing` = 1;

SQL
    );
}

// -------------------------------------

if ($installer->getTableModifier('amazon_account')->isColumnExists('repricing')) {

    $amazonAccountTable = $installer->getTablesObject()->getFullName('amazon_account');
    $amazonAccountRepricingTable = $installer->getTablesObject()->getFullName('amazon_account_repricing');

    $amazonAccounts = $installer->getConnection()->query("
        SELECT * FROM {$amazonAccountTable}
        WHERE `repricing` IS NOT NULL;
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($amazonAccounts as $amazonAccount) {
        $repricingData = json_decode($amazonAccount['repricing'], true);

        if (empty($repricingData)) {
            continue;
        }

        $amazonAccountRepricingData = array(
            'account_id' => $amazonAccount['account_id']
        );

        if (!empty($repricingData['email'])) {
            $amazonAccountRepricingData['email'] = $repricingData['email'];
        }

        if (!empty($repricingData['token'])) {
            $amazonAccountRepricingData['token'] = $repricingData['token'];
        }

        if (!empty($repricingData['info']['total_products'])) {
            $amazonAccountRepricingData['total_products'] = $repricingData['info']['total_products'];
        }

        $connection->insert($amazonAccountRepricingTable, $amazonAccountRepricingData);
    }

    $installer->getTableModifier('amazon_account')->dropColumn('repricing');
}

// -------------------------------------

/*
    INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
    ('/cron/task/repricing_synchronization_general/', 'mode', '1', '0 - disable, \r\n1 - enable',
    '2016-01-01 00:00:00', '2016-01-01 00:00:00'),
    ('/cron/task/repricing_synchronization_general/', 'interval', '86400', 'in seconds',
    '2016-01-01 00:00:00', '2016-01-01 00:00:00'),
    ('/cron/task/repricing_synchronization_general/', 'last_run', NULL, 'date of last run',
    '2016-01-01 00:00:00', '2016-01-01 00:00:00');
*/

$installer->getMainConfigModifier()
    ->insert("/cron/task/repricing_synchronization_general/", "mode", 1, "0 - disable,\r\n1 - enable");
$installer->getMainConfigModifier()
    ->insert("/cron/task/repricing_synchronization_general/", "interval", 86400, "in seconds");
$installer->getMainConfigModifier()
    ->insert("/cron/task/repricing_synchronization_general/", "last_run", NULL, "date of last access");

/*
    INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
    ('/cron/task/repricing_synchronization_actual_price/', 'mode', '1', '0 - disable, \r\n1 - enable',
    '2016-01-01 00:00:00', '2016-01-01 00:00:00'),
    ('/cron/task/repricing_synchronization_actual_price/', 'interval', '3600', 'in seconds',
    '2016-01-01 00:00:00', '2016-01-01 00:00:00'),
    ('/cron/task/repricing_synchronization_actual_price/', 'last_run', NULL, 'date of last run',
    '2016-01-01 00:00:00', '2016-01-01 00:00:00');
*/

$installer->getMainConfigModifier()
    ->insert("/cron/task/repricing_synchronization_actual_price/", "mode", 1, "0 - disable,\r\n1 - enable");
$installer->getMainConfigModifier()
    ->insert("/cron/task/repricing_synchronization_actual_price/", "interval", 3600, "in seconds");
$installer->getMainConfigModifier()
    ->insert("/cron/task/repricing_synchronization_actual_price/", "last_run", NULL, "date of last access");

// -------------------------------------

/*
    INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
    ('/cron/task/repricing_update_settings/', 'mode', '1', '0 - disable, \r\n1 - enable',
    '2016-01-01 00:00:00', '2016-01-01 00:00:00'),
    ('/cron/task/repricing_update_settings/', 'interval', '3600', 'in seconds',
    '2016-01-01 00:00:00', '2016-01-01 00:00:00'),
    ('/cron/task/repricing_update_settings/', 'last_run', NULL, 'date of last run',
    '2016-01-01 00:00:00', '2016-01-01 00:00:00');
*/

$installer->getMainConfigModifier()
    ->insert("/cron/task/repricing_update_settings/", "mode", 1, "0 - disable,\r\n1 - enable");
$installer->getMainConfigModifier()
    ->insert("/cron/task/repricing_update_settings/", "interval", 3600, "in seconds");
$installer->getMainConfigModifier()
    ->insert("/cron/task/repricing_update_settings/", "last_run", NULL, "date of last access");

// -------------------------------------

/*
   INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
    ('/cron/task/repricing_inspect_products/', 'mode', '1', '0 - disable, \r\n1 - enable',
    '2016-01-01 00:00:00', '2016-01-01 00:00:00'),
    ('/cron/task/repricing_inspect_products/', 'interval', '3600', 'in seconds',
    '2016-01-01 00:00:00', '2016-01-01 00:00:00'),
    ('/cron/task/repricing_inspect_products/', 'last_run', NULL, 'date of last run',
    '2016-01-01 00:00:00', '2016-01-01 00:00:00');
*/

$installer->getMainConfigModifier()
    ->insert("/cron/task/repricing_inspect_products/", "mode", 1, "0 - disable,\r\n1 - enable");
$installer->getMainConfigModifier()
    ->insert("/cron/task/repricing_inspect_products/", "interval", 3600, "in seconds");
$installer->getMainConfigModifier()
    ->insert("/cron/task/repricing_inspect_products/", "last_run", NULL, "date of last run");

// -------------------------------------

/*
    DELETE FROM `m2epro_synchronization_config` WHERE `group` = '/amazon/general/update_repricing/';
*/

$installer->getSynchConfigModifier()
    ->getEntity('/amazon/general/update_repricing/', 'mode')->delete();
$installer->getSynchConfigModifier()
    ->getEntity('/amazon/general/update_repricing/', 'interval')->delete();
$installer->getSynchConfigModifier()
    ->getEntity('/amazon/general/update_repricing/', 'last_time')->delete();

/*
    INSERT INTO `m2epro_synchronization_config`(`group`, `key`, `value`, `notice`) VALUES
        ('/amazon/templates/repricing/', 'mode', '1', '0 - disable, \r\n1 - enable');
*/

$installer->getSynchConfigModifier()
    ->insert('/amazon/templates/repricing/', 'mode', 1, '0 - disable, \r\n1 - enable');

// -------------------------------------

/*
    UPDATE `m2epro_config`
    SET `value` = 'https://repricer.m2epro.com/connector/m2epro/'
    WHERE `group` = '/amazon/repricing/' AND
          `key` = 'base_url';
*/

$installer->getMainConfigModifier()
    ->getEntity('/amazon/repricing/', 'base_url')->updateValue('https://repricer.m2epro.com/connector/m2epro/');

//########################################

$installer->endSetup();

//########################################