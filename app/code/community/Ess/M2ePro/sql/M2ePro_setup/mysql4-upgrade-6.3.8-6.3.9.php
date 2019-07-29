<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//########################################

/*
    ### do not send empty value if field is not required
    ### -------------------------------

    ALTER TABLE `m2epro_amazon_template_description_specific`
        ADD COLUMN `is_required` TINYINT(2) UNSIGNED DEFAULT 0 AFTER `mode`;

    ### -------------------------------

    ### amazon repricing tool
    ### -------------------------------

    ALTER TABLE `m2epro_amazon_account`
        ADD COLUMN `repricing` TEXT NULL DEFAULT NULL AFTER `magento_orders_settings`;

    ALTER TABLE `m2epro_amazon_listing_product`
        ADD COLUMN `is_repricing` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_general_id_owner`,
        ADD INDEX `is_repricing` (`is_repricing`);

    ALTER TABLE `m2epro_amazon_listing_other`
        ADD COLUMN `is_repricing` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_isbn_general_id`,
        ADD INDEX `is_repricing` (`is_repricing`);

    ### -------------------------------

    ### amazon merchant fulfillment
    ### -------------------------------

    ALTER TABLE `m2epro_amazon_marketplace`
        ADD COLUMN `is_merchant_fulfillment_available` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_asin_available`,
        ADD INDEX `is_merchant_fulfillment_available` (`is_merchant_fulfillment_available`);

    ALTER TABLE `m2epro_order`
        ADD COLUMN `additional_data` TEXT NULL DEFAULT NULL AFTER `component_mode`;

    ALTER TABLE `m2epro_amazon_order`
        ADD COLUMN `merchant_fulfillment_data` TEXT NULL DEFAULT NULL AFTER `purchase_create_date`,
        ADD COLUMN `merchant_fulfillment_label` BLOB NULL DEFAULT NULL AFTER `merchant_fulfillment_data`,
        ADD COLUMN `is_prime` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_afn_channel`;

    ### -------------------------------
*/

// ---------------------------------------

$modifier = $installer->getTableModifier('amazon_template_description_specific');

if (!$modifier->isColumnExists('is_required')) {

    $modifier->addColumn('is_required', 'TINYINT(2) UNSIGNED', 0, 'mode');

    $installer->run(<<<SQL
        UPDATE `m2epro_amazon_template_description_specific`
        SET `is_required` = 1;
SQL
    );
}

// ---------------------------------------

$installer->getTableModifier('amazon_account')
          ->addColumn('repricing', 'TEXT NULL', 'NULL', 'magento_orders_settings');

// ---------------------------------------

$installer->getTableModifier('amazon_listing_product')
          ->addColumn('is_repricing', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'is_general_id_owner', true);

// ---------------------------------------

$installer->getTableModifier('amazon_listing_other')
          ->addColumn('is_repricing', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'is_isbn_general_id', true);

// ---------------------------------------

$installer->getTableModifier('amazon_marketplace')
          ->addColumn('is_merchant_fulfillment_available', 'TINYINT(2) UNSIGNED NOT NULL',
                       0, 'is_asin_available', true);

// ---------------------------------------

$installer->getTableModifier('order')
          ->addColumn('additional_data', 'TEXT NULL', 'NULL', 'component_mode');

// ---------------------------------------

$installer->getTableModifier('amazon_order')
          ->addColumn('merchant_fulfillment_data', 'TEXT NULL', 'NULL', 'purchase_create_date', false, false)
          ->addColumn('merchant_fulfillment_label', 'BLOB NULL', 'NULL', 'merchant_fulfillment_data', false, false)
          ->addColumn('is_prime', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'is_afn_channel', true, false)
          ->commit();

//########################################

/*
    INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`)
    VALUES ('/amazon/repricing/', 'mode', '0', '0 - disable, \r\n1 - enable',
             '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
           ('/amazon/repricing/', 'base_url', 'http://repricer.m2epro.com/', NULL,
             '2013-05-08 00:00:00', '2013-05-08 00:00:00');

    INSERT INTO `m2epro_synchronization_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`)
    VALUES ('/amazon/defaults/update_repricing/', 'interval', '86400', 'in seconds',
            '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
           ('/amazon/defaults/update_repricing/', 'mode', '1', '0 - disable, \r\n1 - enable',
            '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
           ('/amazon/defaults/update_repricing/', 'last_time', NULL, 'Last check time',
            '2013-05-08 00:00:00', '2013-05-08 00:00:00');
*/

$installer->getMainConfigModifier()
    ->insert('/amazon/repricing/', 'mode', '0', '0 - disable, \r\n1 - enable');

$installer->getMainConfigModifier()
    ->insert('/amazon/repricing/', 'base_url', 'http://repricer.m2epro.com/', 'Repricing Tool base url');

$installer->getSynchConfigModifier()
    ->insert('/amazon/defaults/update_repricing/', 'interval', '86400', 'in seconds');

$installer->getSynchConfigModifier()
    ->insert('/amazon/defaults/update_repricing/', 'mode', '1', '0 - disable, \r\n1 - enable');

$installer->getSynchConfigModifier()
    ->insert('/amazon/defaults/update_repricing/', 'last_time', NULL, 'Last check time');

//########################################

$installer->run(<<<SQL

    UPDATE `m2epro_amazon_marketplace`
    SET `is_merchant_fulfillment_available` = 1
    WHERE `marketplace_id` IN (25, 28, 29);

SQL
);

//########################################

$installer->endSetup();

//########################################