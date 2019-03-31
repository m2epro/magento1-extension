<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//########################################

/*
    ALTER TABLE `m2epro_amazon_processing_action`
    DROP COLUMN `is_completed`;

    ALTER TABLE `m2epro_amazon_processing_action_item`
    ADD COLUMN `is_skipped` TINYINT(2) NOT NULL DEFAULT 0 AFTER `is_completed`,
    ADD INDEX `is_skipped` (`is_skipped`);

    ALTER TABLE `m2epro_ebay_processing_action`
    DROP COLUMN `is_completed`,
    DROP INDEX `is_completed`,
    ADD COLUMN `request_timeout` INT(11) UNSIGNED DEFAULT NULL AFTER `type`;

    ALTER TABLE `m2epro_ebay_processing_action_item`
    DROP COLUMN `output_data`,
    DROP COLUMN `output_messages`,
    DROP COLUMN `attempts_count`,
    DROP COLUMN `is_completed`,
    ADD COLUMN `is_skipped` TINYINT(2) NOT NULL DEFAULT 0 AFTER `input_data`,
    ADD INDEX `is_skipped` (`is_skipped`);
*/

// ---------------------------------------

$installer->getTableModifier('amazon_processing_action')
          ->dropColumn('is_completed');

// ---------------------------------------

$installer->getTableModifier('amazon_processing_action_item')
          ->addColumn('is_skipped', 'TINYINT(2) NOT NULL', 0, 'is_completed', true);

// ---------------------------------------

$installer->getTableModifier('ebay_processing_action')
          ->dropColumn('is_completed', true, false)
          ->addColumn('request_timeout', 'INT(11) UNSIGNED', NULL, 'type', false, false)
          ->commit();

// ---------------------------------------

$installer->getTableModifier('ebay_processing_action_item')
          ->dropColumn('output_data', false, false)
          ->dropColumn('output_messages', false, false)
          ->dropColumn('attempts_count', false, false)
          ->dropColumn('is_completed', true, false)
          ->addColumn('is_skipped', 'TINYINT(2) NOT NULL', 0, 'input_data', true, false)
          ->commit();

//########################################

/*
    UPDATE `m2epro_config`
    SET `value` = 'http://repricer.m2epro.com/connector/m2epro/'
    WHERE `group` = '/amazon/repricing/'
    AND   `key` = 'base_url';
*/

// ---------------------------------------

$installer->getMainConfigModifier()->getEntity('/amazon/repricing/', 'base_url')
          ->updateValue('http://repricer.m2epro.com/connector/m2epro/');

//########################################

$installer->endSetup();

//########################################