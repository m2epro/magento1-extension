<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//########################################

/*
    ### -------------------------------

    ALTER TABLE `m2epro_ebay_processing_action_item`
      MODIFY COLUMN `input_data` LONGTEXT NOT NULL AFTER `related_id`,
      MODIFY COLUMN `is_skipped` TINYINT(2) NOT NULL DEFAULT 0 AFTER `input_data`;

    ALTER TABLE `m2epro_amazon_processing_action_item`
      MODIFY COLUMN `input_data` LONGTEXT NOT NULL AFTER `related_id`,
      MODIFY COLUMN `is_skipped` TINYINT(2) NOT NULL DEFAULT 0 AFTER `is_completed`;

    ALTER TABLE `m2epro_processing`
      ADD COLUMN `expiration_date` DATETIME NOT NULL AFTER `is_completed`,
      ADD INDEX `expiration_date` (`expiration_date`);

    ### -------------------------------

    ALTER TABLE m2epro_ebay_account
    ADD COLUMN user_preferences TEXT DEFAULT NULL AFTER info;

    ### -------------------------------

    ALTER TABLE `m2epro_amazon_listing_other`
        ADD COLUMN `is_repricing_disabled` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_repricing`,
        ADD INDEX `is_repricing_disabled` (`is_repricing_disabled`);

    ALTER TABLE `m2epro_amazon_listing_product`
        DROP COLUMN `is_repricing`,
        DROP INDEX `is_repricing`;

    ALTER TABLE `m2epro_amazon_account`
        DROP COLUMN `repricing`;

    ### -------------------------------

    ALTER TABLE `m2epro_connector_pending_requester_partial`
        DROP COLUMN `next_data_part_number`;

    ALTER TABLE `m2epro_request_pending_partial`
        ADD COLUMN `result_messages` LONGTEXT DEFAULT NULL AFTER `next_part`;

    ### -------------------------------
*/

// ---------------------------------------

$installer->getTableModifier('ebay_processing_action_item')
    ->changeColumn('input_data', 'LONGTEXT NOT NULL', NULL, 'related_id', false)
    ->changeColumn('is_skipped', 'TINYINT(2) NOT NULL', 0, 'input_data', false)
    ->commit();

$installer->getTableModifier('amazon_processing_action_item')
    ->changeColumn('input_data', 'LONGTEXT NOT NULL', NULL, 'related_id', false)
    ->changeColumn('is_skipped', 'TINYINT(2) NOT NULL', 0, 'is_completed', false)
    ->commit();

$installer->getTableModifier('processing')
    ->addColumn('expiration_date', 'DATETIME NOT NULL', NULL, 'is_completed', true, true);

if ($installer->getTableModifier('processing')->isColumnExists('expiration_date')) {
    $installer->run(<<<SQL

    UPDATE `m2epro_processing`
    SET `expiration_date` = DATE_ADD(`create_date`, INTERVAL 24 HOUR)
    WHERE `expiration_date` = '0000-00-00 00:00:00' AND `model` NOT LIKE '%Translation%';

    UPDATE `m2epro_processing`
    SET `expiration_date` = DATE_ADD(`create_date`, INTERVAL 240 HOUR)
    WHERE `expiration_date` = '0000-00-00 00:00:00' AND `model` LIKE '%Translation%';

SQL
    );
}

//########################################

$installer->getTableModifier('ebay_account')
    ->addColumn('user_preferences', 'TEXT', 'NULL', 'info');

//########################################

/*
    INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
    ('/cron/task/update_ebay_accounts_preferences/', 'mode', '1', '0 - disable, \r\n1 - enable',
    '2016-01-01 00:00:00', '2016-01-01 00:00:00'),
    ('/cron/task/update_ebay_accounts_preferences/', 'interval', '86400', 'in seconds',
    '2016-01-01 00:00:00', '2016-01-01 00:00:00'),
    ('/cron/task/update_ebay_accounts_preferences/', 'last_run', NULL, 'date of last run',
    '2016-01-01 00:00:00', '2016-01-01 00:00:00');

    INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
    ('/view/products_grid/', 'use_alternative_mysql_select', '0', '0 - disable, \r\n1 - enable',
    '2016-01-01 00:00:00', '2016-01-01 00:00:00');
*/

$installer->getMainConfigModifier()
    ->insert("/cron/task/update_ebay_accounts_preferences/", "mode", 1, "0 - disable,\r\n1 - enable");
$installer->getMainConfigModifier()
    ->insert("/cron/task/update_ebay_accounts_preferences/", "interval", 86400, "in seconds");
$installer->getMainConfigModifier()
    ->insert("/cron/task/update_ebay_accounts_preferences/", "last_run", NULL, "date of last access");

$installer->getMainConfigModifier()
    ->insert('/view/products_grid/', 'use_alternative_mysql_select', 0, "0 - disable, \r\n1 - enable");

//########################################

$configTable = $installer->getTable('m2epro_config');
$cacheConfigTable = $installer->getTable('m2epro_cache_config');

$oldData = $connection->query("

SELECT * FROM `{$configTable}` WHERE
    `group` = '/view/ebay/advanced/autoaction_popup/' AND `key` = 'shown' OR
    `group` = '/view/ebay/motors_epids_attribute/' AND `key` = 'listing_notification_shown' OR
    `group` = '/view/ebay/multi_currency_marketplace_2/' AND `key` = 'notification_shown' OR
    `group` = '/view/ebay/multi_currency_marketplace_19/' AND `key` = 'notification_shown' OR
    `group` = '/view/requirements/popup/' AND `key` = 'closed'

")->fetchAll();

$insertParts = array();
$ids = array();
foreach ($oldData as $tempRow) {

    $insertParts[] = "(
        '{$tempRow['group']}',
        '{$tempRow['key']}',
        '{$tempRow['value']}',
        '{$tempRow['notice']}',
        '{$tempRow['update_date']}',
        '{$tempRow['create_date']}'
    )";

    $ids[] = $tempRow['id'];
}

if (!empty($insertParts)) {

    $insertString = implode(',', $insertParts);
    $insertSql = 'INSERT INTO `'.$cacheConfigTable.'` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`)
                  VALUES' . $insertString;

    $connection->query($insertSql);

    $idsString = implode(',', $ids);

    $connection->query(<<<SQL

        DELETE FROM `{$configTable}` WHERE `id` IN ({$idsString});

SQL
    );
}

$installer->run(<<<SQL

    UPDATE `m2epro_cache_config`
    SET `group` = '/view/ebay/listing/advanced/autoaction_popup/',
        `key`   = 'shown'
    WHERE `group` = '/view/ebay/advanced/autoaction_popup/'
      AND `key`   = 'shown';

    UPDATE `m2epro_cache_config`
    SET `group` = '/view/ebay/listing/motors_epids_attribute/',
        `key`   = 'notification_shown'
    WHERE `group` = '/view/ebay/motors_epids_attribute/'
      AND `key`   = 'listing_notification_shown';

    UPDATE `m2epro_cache_config`
    SET `group` = '/view/ebay/template/selling_format/multi_currency_marketplace_2/',
        `key`   = 'notification_shown'
    WHERE `group` = '/view/ebay/multi_currency_marketplace_2/'
      AND `key`   = 'notification_shown';

    UPDATE `m2epro_cache_config`
    SET `group` = '/view/ebay/template/selling_format/multi_currency_marketplace_19/',
        `key`   = 'notification_shown'
    WHERE `group` = '/view/ebay/multi_currency_marketplace_19/'
      AND `key`   = 'notification_shown';

SQL
);

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

    $installer->getTableModifier('amazon_listing_product')->dropColumn('is_repricing');
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

//########################################

/*
    INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
    ('/cron/task/repricing_synchronization/', 'mode', '1', '0 - disable, \r\n1 - enable',
    '2016-01-01 00:00:00', '2016-01-01 00:00:00'),
    ('/cron/task/repricing_synchronization/', 'interval', '86400', 'in seconds',
    '2016-01-01 00:00:00', '2016-01-01 00:00:00'),
    ('/cron/task/repricing_synchronization/', 'last_run', NULL, 'date of last run',
    '2016-01-01 00:00:00', '2016-01-01 00:00:00');
*/

$installer->getMainConfigModifier()
    ->insert("/cron/task/repricing_synchronization/", "mode", 1, "0 - disable,\r\n1 - enable");
$installer->getMainConfigModifier()
    ->insert("/cron/task/repricing_synchronization/", "interval", 86400, "in seconds");
$installer->getMainConfigModifier()
    ->insert("/cron/task/repricing_synchronization/", "last_run", NULL, "date of last access");

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

//########################################

/*
    INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
        ('/cron/checker/task/repair_crashed_tables/', 'interval', '3600', 'in seconds',
        '2016-02-18 00:00:00', '2016-02-18 00:00:00');
*/

$installer->getMainConfigModifier()
    ->insert('/cron/checker/task/repair_crashed_tables/', 'interval', '3600', 'in seconds');

//########################################

/*
    UPDATE `m2epro_config`
    SET `value` = 'https://repricer.m2epro.com/connector/m2epro/'
    WHERE `group` = '/amazon/repricing/' AND
          `key` = 'base_url';
*/

$installer->getMainConfigModifier()
    ->getEntity('/amazon/repricing/', 'base_url')->updateValue('https://repricer.m2epro.com/connector/m2epro/');

//########################################

$tempTable = $installer->getTable('m2epro_wizard');
$tempQuery = <<<SQL
    SELECT * FROM `{$tempTable}`
    WHERE `nick` = 'removedEbay3rdParty';
SQL;
$tempRow = $connection->query($tempQuery)->fetch();

if ($tempRow === false) {

    $tempTable = $installer->getTable('m2epro_synchronization_config');
    $queryStmt = $connection->query(<<<SQL

SELECT `value` FROM `{$tempTable}` WHERE
    (`group` = '/ebay/other_listing/synchronization/' AND `key` = 'mode')
OR
    (`group` = '/ebay/other_listing/source/');

SQL
    );

    $wizardStatus = 3;
    while ($mode = $queryStmt->fetchColumn()) {

        if ($mode == 1) {
            $wizardStatus = 0;
            break;
        }
    }

    $installer->run(<<<SQL

INSERT INTO `m2epro_wizard` (`nick`, `view`, `status`, `step`, `type`, `priority`)
SELECT 'removedEbay3rdParty', 'ebay', {$wizardStatus}, NULL, 0, MAX( `priority` )+1 FROM `m2epro_wizard`;

SQL
    );
}

// ---------------------------------------

$installer->run(<<<SQL

DELETE FROM `m2epro_synchronization_config`
WHERE `group` LIKE '%/ebay/other_listings/synchronization/%' OR
      `group` LIKE '%/ebay/other_listing/%';

SQL
);

//########################################

$installer->getTableModifier('connector_pending_requester_partial')
    ->dropColumn('next_data_part_number');

$installer->getTableModifier('request_pending_partial')
    ->addColumn('result_messages', 'LONGTEXT', 'NULL', 'next_part');

//########################################

/*
   ALTER TABLE `m2epro_ebay_listing_product`
       ADD COLUMN `online_duration` INT(11) UNSIGNED DEFAULT NULL AFTER `online_title`;

   ALTER TABLE `m2epro_ebay_listing_other`
       ADD COLUMN `online_duration` INT(11) UNSIGNED DEFAULT NULL AFTER `currency`;
*/

$installer->getTableModifier('ebay_listing_product')
          ->addColumn('online_duration', 'INT(11) UNSIGNED', 'NULL', 'online_title');

$installer->getTableModifier('ebay_listing_other')
          ->addColumn('online_duration', 'INT(11) UNSIGNED', 'NULL', 'currency');

//########################################

$installer->run(<<<SQL

    UPDATE `m2epro_listing_other`
    SET `status` = 3
    WHERE `component_mode` = 'ebay' AND `status` = 6;

SQL
);

//########################################

/*
    DELETE FROM `m2epro_config` WHERE `group` = '/support/uservoice/';
*/

$installer->getMainConfigModifier()
          ->getEntity('/support/uservoice/', 'api_url')->delete();
$installer->getMainConfigModifier()
          ->getEntity('/support/uservoice/', 'api_client_key')->delete();

//########################################

$accountTable = $installer->getTablesObject()->getFullName('account');
$listingOtherTable = $installer->getTablesObject()->getFullName('listing_other');

$accounts = $installer->getConnection()->query("
  SELECT * FROM {$accountTable} WHERE `component_mode` = 'amazon';
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($accounts as $account) {
    $accountId = $account['id'];

    $rowsCount = $installer->getConnection()->query("
        SELECT COUNT(*) FROM {$listingOtherTable} WHERE `component_mode` = 'amazon' AND `account_id` = {$accountId}
    ")->fetchColumn();

    if ((int)$rowsCount <= 0) {
        continue;
    }

    $additionalData = (array)@json_decode($account['additional_data'], true);
    $additionalData['is_amazon_other_listings_full_items_data_already_received'] = true;

    $connection->update(
        $accountTable,
        array('additional_data' => json_encode($additionalData)),
        array('id = ?' => $accountId)
    );
}

// ---------------------------------------

$installer->run(<<<SQL

UPDATE `m2epro_amazon_listing_other`
SET `title` = 'Unknown (can\'t be received)'
WHERE `title` IS NULL

SQL
);

//########################################

/*
    INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
    (NULL, 'is_disabled', '0', '0 - disable, \r\n1 - enable', '2016-01-01 00:00:00', '2016-01-01 00:00:00');
*/

$installer->getMainConfigModifier()->insert(NULL, 'is_disabled', '0', '0 - disable, \r\n1 - enable');

//########################################

$installer->endSetup();

//########################################