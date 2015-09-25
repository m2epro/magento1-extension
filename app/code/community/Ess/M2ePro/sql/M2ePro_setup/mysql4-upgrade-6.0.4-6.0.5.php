<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE `m2epro_ebay_template_shipping`
    ADD COLUMN `excluded_locations` TEXT DEFAULT NULL AFTER `international_shipping_combined_discount_profile_id`;

    ALTER TABLE `m2epro_ebay_listing_auto_category`
    DROP COLUMN `adding_duplicate`;
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_shipping');

if ($connection->tableColumnExists($tempTable, 'excluded_locations') === false) {
    $connection->addColumn(
        $tempTable,
        'excluded_locations',
        'TEXT DEFAULT NULL AFTER `international_shipping_combined_discount_profile_id`'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_listing_auto_category');

if ($connection->tableColumnExists($tempTable, 'adding_duplicate') !== false) {
    $connection->dropColumn(
        $tempTable,
        'adding_duplicate'
    );
}

//#############################################

$installer->endSetup();

//#############################################