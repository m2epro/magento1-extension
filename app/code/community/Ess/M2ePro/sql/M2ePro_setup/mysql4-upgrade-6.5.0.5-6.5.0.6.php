<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//########################################

/*
    ALTER TABLE `m2epro_amazon_marketplace`
    CHANGE COLUMN `is_asin_available` `is_new_asin_available` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
    DROP INDEX `is_asin_available`,
    ADD INDEX `is_new_asin_available` (`is_new_asin_available`);
*/

$installer->getTableModifier('amazon_marketplace')->renameColumn('is_asin_available', 'is_new_asin_available', true);

$installer->run(<<<SQL
    UPDATE `m2epro_amazon_marketplace`
    SET `is_new_asin_available` = 1
    WHERE `marketplace_id` = 24;

    UPDATE `m2epro_amazon_marketplace`
    SET `is_new_asin_available` = 0
    WHERE `marketplace_id` IN (27, 32);
SQL
);

//########################################

$installer->run(<<<SQL

UPDATE `m2epro_amazon_listing_other`
SET `title` = '--'
WHERE `title` = ''
OR `title` = 'Unknown (can\'t be received)'
OR `title` IS NULL;

SQL
);

//########################################

$installer->endSetup();

//########################################