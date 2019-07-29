<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//########################################

/*
    ALTER TABLE `m2epro_ebay_template_shipping`
    CHANGE COLUMN `click_and_collect_mode` `click_and_collect_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1
            AFTER `local_shipping_discount_profile_id`;
*/

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_shipping');

if ($connection->tableColumnExists($tempTable, 'click_and_collect_mode')) {
    $connection->changeColumn(
        $tempTable,
        'click_and_collect_mode',
        'click_and_collect_mode',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 1 AFTER `local_shipping_discount_profile_id`'
    );
}

//########################################

$installer->run(<<<SQL

UPDATE `m2epro_ebay_marketplace`
SET `is_holiday_return` = 1
WHERE `marketplace_id` = 9;   -- Motor --

SQL
);

//########################################

$installer->endSetup();

//########################################