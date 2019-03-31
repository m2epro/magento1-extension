<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//########################################

// MarketplacesFeatures
//########################################

/*
    ALTER TABLE `m2epro_ebay_marketplace`
        ADD COLUMN `is_epid` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_holiday_return`;
    ALTER TABLE `m2epro_ebay_marketplace`
        ADD COLUMN `is_ktype` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_epid`;
*/

// ---------------------------------------

$installer->getTableModifier('ebay_marketplace')->addColumn(
    'is_epid', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'is_holiday_return', true
);

$installer->getTableModifier('ebay_marketplace')->addColumn(
    'is_ktype', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'is_epid', true
);

// ---------------------------------------

$installer->run(<<<SQL
    UPDATE `{$installer->getTable('m2epro_ebay_marketplace')}`
    SET `is_epid` = 1
    WHERE `marketplace_id` = 9;
SQL
);

$installer->run(<<<SQL
    UPDATE `{$installer->getTable('m2epro_ebay_marketplace')}`
    SET `is_ktype` = 1
    WHERE `marketplace_id` IN (3, 4, 7, 8, 10);
SQL
);

// SearchSettingsDataCapacity
//########################################

/*
    ALTER TABLE `m2epro_amazon_listing_product`
        CHANGE COLUMN `search_settings_data` `search_settings_data` LONGTEXT DEFAULT NULL;
*/

// ---------------------------------------

$installer->getTableModifier('amazon_listing_product')
          ->changeColumn('search_settings_data', 'LONGTEXT', 'NULL');

// AfnAndRepricingFiltersImprovements
//########################################

/*
    ALTER TABLE `m2epro_amazon_listing_product`
    ADD COLUMN `variation_parent_afn_state` SMALLINT(4) UNSIGNED DEFAULT NULL AFTER `is_general_id_owner`,
    ADD COLUMN `variation_parent_repricing_state` SMALLINT(4) UNSIGNED DEFAULT NULL AFTER `variation_parent_afn_state`,
    ADD INDEX `variation_parent_afn_state` (`variation_parent_afn_state`),
    ADD INDEX `variation_parent_repricing_state` (`variation_parent_repricing_state`);
*/

// ---------------------------------------

$installer->getTableModifier('amazon_listing_product')
    ->addColumn(
        'variation_parent_afn_state', 'SMALLINT(4) UNSIGNED', 'NULL', 'is_general_id_owner', true, false
    )
    ->addColumn(
        'variation_parent_repricing_state', 'SMALLINT(4) UNSIGNED', 'NULL', 'variation_parent_afn_state', true, false
    )
    ->commit();

//########################################

$installer->endSetup();

//########################################