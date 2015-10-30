<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//########################################

$installer->run(<<<SQL

ALTER TABLE `m2epro_amazon_template_general`
MODIFY COLUMN `restock_date_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1;

SQL
);

//########################################

/*
    ALTER TABLE `m2epro_amazon_template_general`
    ADD COLUMN `search_by_magento_title_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1
                AFTER `worldwide_id_custom_attribute`,
    ADD INDEX `search_by_magento_title_mode` (`search_by_magento_title_mode`);
*/

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_template_general');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'search_by_magento_title_mode') === false) {
    $connection->addColumn($tempTable, 'search_by_magento_title_mode',
                           'TINYINT(2) UNSIGNED NOT NULL DEFAULT 1 AFTER `worldwide_id_custom_attribute`');
}

if (!isset($tempTableIndexList[strtoupper('search_by_magento_title_mode')])) {
    $connection->addKey($tempTable, 'search_by_magento_title_mode', 'search_by_magento_title_mode');
}

//########################################

$installer->run(<<<SQL

UPDATE `m2epro_amazon_template_general`
SET `restock_date_mode` = 1
WHERE `restock_date_mode` = 0;

UPDATE `m2epro_ebay_template_general`
SET `sku_mode` = 1
WHERE `variation_ignore` = 0
AND   `variation_enabled` = 1;

UPDATE `m2epro_ebay_template_general`
SET `condition_mode` = 0
WHERE `condition_attribute` = '';

UPDATE `m2epro_ebay_template_general`
SET `condition_mode` = 1
WHERE `condition_value` = '';

UPDATE `m2epro_ebay_template_general`
SET `condition_mode` = 1
WHERE `categories_mode` = 1;

SQL
);

//########################################

$installer->endSetup();

//########################################