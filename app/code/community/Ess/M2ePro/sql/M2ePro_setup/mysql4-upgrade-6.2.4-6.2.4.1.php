<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE `m2epro_ebay_template_selling_format`
    CHANGE COLUMN `qty_max_posted_value_mode` `qty_modification_mode` tinyint(2) UNSIGNED NOT NULL
        AFTER `qty_percentage`,
    ADD COLUMN `qty_min_posted_value` int(11) UNSIGNED DEFAULT NULL
        AFTER `qty_modification_mode`;

    ALTER TABLE `m2epro_amazon_template_selling_format`
    CHANGE COLUMN `qty_max_posted_value_mode` `qty_modification_mode` tinyint(2) UNSIGNED NOT NULL
        AFTER `qty_percentage`,
    ADD COLUMN `qty_min_posted_value` int(11) UNSIGNED DEFAULT NULL
        AFTER `qty_modification_mode`;

    ALTER TABLE `m2epro_buy_template_selling_format`
    CHANGE COLUMN `qty_max_posted_value_mode` `qty_modification_mode` tinyint(2) UNSIGNED NOT NULL
        AFTER `qty_percentage`,
    ADD COLUMN `qty_min_posted_value` int(11) UNSIGNED DEFAULT NULL
        AFTER `qty_modification_mode`;

    ALTER TABLE `m2epro_play_template_selling_format`
    CHANGE COLUMN `qty_max_posted_value_mode` `qty_modification_mode` tinyint(2) UNSIGNED NOT NULL
        AFTER `qty_percentage`,
    ADD COLUMN `qty_min_posted_value` int(11) UNSIGNED DEFAULT NULL
        AFTER `qty_modification_mode`;

    // --------------------------------------------

    ALTER TABLE `m2epro_amazon_template_synchronization`
    CHANGE COLUMN `stop_out_off_stock` `stop_out_off_stock` TINYINT(2) UNSIGNED NOT NULL;

    ALTER TABLE `m2epro_buy_template_synchronization`
    CHANGE COLUMN `stop_out_off_stock` `stop_out_off_stock` TINYINT(2) UNSIGNED NOT NULL;
*/

//---------------------------------------------

$tempTables = array(
    'm2epro_ebay_template_selling_format',
    'm2epro_amazon_template_selling_format',
    'm2epro_buy_template_selling_format',
    'm2epro_play_template_selling_format'
);

foreach ($tempTables as $tableName) {

    $tempTable = $installer->getTable($tableName);

    if ($connection->tableColumnExists($tempTable, 'qty_max_posted_value_mode') !== false &&
        $connection->tableColumnExists($tempTable, 'qty_modification_mode') === false) {
        $connection->changeColumn(
            $tempTable,
            'qty_max_posted_value_mode',
            'qty_modification_mode',
            'tinyint(2) UNSIGNED NOT NULL AFTER `qty_percentage`'
        );
    }

    if ($connection->tableColumnExists($tempTable, 'qty_min_posted_value') === false) {
        $connection->addColumn(
            $tempTable,
            'qty_min_posted_value',
            'int(11) UNSIGNED DEFAULT NULL AFTER `qty_modification_mode`'
        );
    }
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_template_synchronization');
$columnName = 'stop_out_off_stock';

if ($connection->tableColumnExists($tempTable, $columnName) !== false) {
    $connection->changeColumn(
        $tempTable, $columnName, $columnName,
        'tinyint(2) UNSIGNED NOT NULL'
    );
}

$tempTable = $installer->getTable('m2epro_buy_template_synchronization');
$columnName = 'stop_out_off_stock';

if ($connection->tableColumnExists($tempTable, $columnName) !== false) {
    $connection->changeColumn(
        $tempTable, $columnName, $columnName,
        'tinyint(2) UNSIGNED NOT NULL'
    );
}

//#############################################

$installer->run(<<<SQL

UPDATE `m2epro_ebay_template_selling_format`
SET `qty_min_posted_value` = 1;

UPDATE `m2epro_amazon_template_selling_format`
SET `qty_min_posted_value` = 1;

UPDATE `m2epro_buy_template_selling_format`
SET `qty_min_posted_value` = 1;

UPDATE `m2epro_play_template_selling_format`
SET `qty_min_posted_value` = 1;

SQL
);

//#############################################

$installer->run(<<<SQL

UPDATE `m2epro_cache_config`
SET `value` = null
WHERE `group` = '/installation/version/history/' AND `create_date` = `value`;

SQL
);

//#############################################

$installer->endSetup();

//#############################################