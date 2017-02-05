<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//########################################

// IncreaseCapacityOfSystemLogMessage
//----------------------------------------

/*
    ALTER TABLE `m2epro_system_log`
    CHANGE COLUMN `description` `description` LONGTEXT DEFAULT NULL;
*/

$installer->getTableModifier('system_log')
    ->changeColumn('description', 'LONGTEXT', 'NULL');

// fix for is_repricing default value
// ---------------------------------------

/*
    ALTER TABLE `m2epro_amazon_listing_product`
       CHANGE COLUMN `is_repricing` `is_repricing` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0;
*/

$installer->getTableModifier('amazon_listing_product')
    ->changeColumn('is_repricing', 'TINYINT(2) UNSIGNED NOT NULL', 0);

$installer->run(<<<SQL

TRUNCATE TABLE `m2epro_amazon_listing_product_repricing`;

UPDATE `m2epro_amazon_listing_product`
SET `is_repricing` = 0;

UPDATE `m2epro_amazon_listing_other`
SET `is_repricing` = 0, `is_repricing_disabled` = 0;

UPDATE `m2epro_amazon_account_repricing`
SET `total_products` = 0, `last_checked_listing_product_update_date` = NULL;

SQL
);

/*
    UPDATE `m2epro_config`
    SET `value` = NULL
    WHERE `group` = '/cron/task/repricing_synchronization_general/' AND `key` = 'last_run'
 */

$installer->getMainConfigModifier()
    ->getEntity('/cron/task/repricing_synchronization_general/', 'last_run')
    ->updateValue(NULL);

// ebay orders job_token default null fix
// ---------------------------------------

/*
    ALTER TABLE `m2epro_ebay_account`
       CHANGE COLUMN `job_token` `job_token` VARCHAR(255) DEFAULT NULL;
*/

$installer->getTableModifier('ebay_account')
    ->changeColumn('job_token', 'VARCHAR(255)', 'NULL');

// repricing_update_settings cron task config fix
// ---------------------------------------

/*
    INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
    ('/cron/task/repricing_update_settings/', 'mode', '1', '0 - disable, \r\n1 - enable',
     '2013-05-08 00:00:00', '2013-05-08 00:00:00');
 */
$installer->getMainConfigModifier()
    ->insert('/cron/task/repricing_update_settings/', 'mode', '1');

//########################################

$installer->endSetup();

//########################################