<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE m2epro_amazon_template_synchronization
       CHANGE COLUMN stop_qty stop_qty_magento tinyint(2) UNSIGNED NOT NULL,
       CHANGE COLUMN stop_qty_value stop_qty_magento_value int(11) UNSIGNED NOT NULL,
       CHANGE COLUMN stop_qty_value_max stop_qty_magento_value_max int(11) UNSIGNED NOT NULL,
       ADD COLUMN stop_qty_calculated tinyint(2) UNSIGNED NOT NULL AFTER stop_qty_magento_value_max,
       ADD COLUMN stop_qty_calculated_value int(11) UNSIGNED NOT NULL AFTER stop_qty_calculated,
       ADD COLUMN stop_qty_calculated_value_max int(11) UNSIGNED NOT NULL AFTER stop_qty_calculated_value;

    ALTER TABLE m2epro_buy_template_synchronization
       CHANGE COLUMN stop_qty stop_qty_magento tinyint(2) UNSIGNED NOT NULL,
       CHANGE COLUMN stop_qty_value stop_qty_magento_value int(11) UNSIGNED NOT NULL,
       CHANGE COLUMN stop_qty_value_max stop_qty_magento_value_max int(11) UNSIGNED NOT NULL,
       ADD COLUMN stop_qty_calculated tinyint(2) UNSIGNED NOT NULL AFTER stop_qty_magento_value_max,
       ADD COLUMN stop_qty_calculated_value int(11) UNSIGNED NOT NULL AFTER stop_qty_calculated,
       ADD COLUMN stop_qty_calculated_value_max int(11) UNSIGNED NOT NULL AFTER stop_qty_calculated_value;

    ALTER TABLE m2epro_ebay_template_synchronization
       CHANGE COLUMN stop_qty stop_qty_magento tinyint(2) UNSIGNED NOT NULL,
       CHANGE COLUMN stop_qty_value stop_qty_magento_value int(11) UNSIGNED NOT NULL,
       CHANGE COLUMN stop_qty_value_max stop_qty_magento_value_max int(11) UNSIGNED NOT NULL,
       ADD COLUMN stop_qty_calculated tinyint(2) UNSIGNED NOT NULL AFTER stop_qty_magento_value_max,
       ADD COLUMN stop_qty_calculated_value int(11) UNSIGNED NOT NULL AFTER stop_qty_calculated,
       ADD COLUMN stop_qty_calculated_value_max int(11) UNSIGNED NOT NULL AFTER stop_qty_calculated_value;

    ALTER TABLE m2epro_play_template_synchronization
       CHANGE COLUMN stop_qty stop_qty_magento tinyint(2) UNSIGNED NOT NULL,
       CHANGE COLUMN stop_qty_value stop_qty_magento_value int(11) UNSIGNED NOT NULL,
       CHANGE COLUMN stop_qty_value_max stop_qty_magento_value_max int(11) UNSIGNED NOT NULL,
       ADD COLUMN stop_qty_calculated tinyint(2) UNSIGNED NOT NULL AFTER stop_qty_magento_value_max,
       ADD COLUMN stop_qty_calculated_value int(11) UNSIGNED NOT NULL AFTER stop_qty_calculated,
       ADD COLUMN stop_qty_calculated_value_max int(11) UNSIGNED NOT NULL AFTER stop_qty_calculated_value;

    //---------------------------------------------

    ALTER TABLE m2epro_amazon_template_synchronization
       CHANGE COLUMN list_qty list_qty_magento tinyint(2) UNSIGNED NOT NULL,
       CHANGE COLUMN list_qty_value list_qty_magento_value int(11) UNSIGNED NOT NULL,
       CHANGE COLUMN list_qty_value_max list_qty_magento_value_max int(11) UNSIGNED NOT NULL,
       ADD COLUMN list_qty_calculated tinyint(2) UNSIGNED NOT NULL AFTER list_qty_magento_value_max,
       ADD COLUMN list_qty_calculated_value int(11) UNSIGNED NOT NULL AFTER list_qty_calculated,
       ADD COLUMN list_qty_calculated_value_max int(11) UNSIGNED NOT NULL AFTER list_qty_calculated_value;

    ALTER TABLE m2epro_buy_template_synchronization
       CHANGE COLUMN list_qty list_qty_magento tinyint(2) UNSIGNED NOT NULL,
       CHANGE COLUMN list_qty_value list_qty_magento_value int(11) UNSIGNED NOT NULL,
       CHANGE COLUMN list_qty_value_max list_qty_magento_value_max int(11) UNSIGNED NOT NULL,
       ADD COLUMN list_qty_calculated tinyint(2) UNSIGNED NOT NULL AFTER list_qty_magento_value_max,
       ADD COLUMN list_qty_calculated_value int(11) UNSIGNED NOT NULL AFTER list_qty_calculated,
       ADD COLUMN list_qty_calculated_value_max int(11) UNSIGNED NOT NULL AFTER list_qty_calculated_value;

    ALTER TABLE m2epro_ebay_template_synchronization
       CHANGE COLUMN list_qty list_qty_magento tinyint(2) UNSIGNED NOT NULL,
       CHANGE COLUMN list_qty_value list_qty_magento_value int(11) UNSIGNED NOT NULL,
       CHANGE COLUMN list_qty_value_max list_qty_magento_value_max int(11) UNSIGNED NOT NULL,
       ADD COLUMN list_qty_calculated tinyint(2) UNSIGNED NOT NULL AFTER list_qty_magento_value_max,
       ADD COLUMN list_qty_calculated_value int(11) UNSIGNED NOT NULL AFTER list_qty_calculated,
       ADD COLUMN list_qty_calculated_value_max int(11) UNSIGNED NOT NULL AFTER list_qty_calculated_value;

    ALTER TABLE m2epro_play_template_synchronization
      CHANGE COLUMN list_qty list_qty_magento tinyint(2) UNSIGNED NOT NULL,
      CHANGE COLUMN list_qty_value list_qty_magento_value int(11) UNSIGNED NOT NULL,
      CHANGE COLUMN list_qty_value_max list_qty_magento_value_max int(11) UNSIGNED NOT NULL,
      ADD COLUMN list_qty_calculated tinyint(2) UNSIGNED NOT NULL AFTER list_qty_magento_value_max,
      ADD COLUMN list_qty_calculated_value int(11) UNSIGNED NOT NULL AFTER list_qty_calculated,
      ADD COLUMN list_qty_calculated_value_max int(11) UNSIGNED NOT NULL AFTER list_qty_calculated_value;

    //---------------------------------------------

    ALTER TABLE m2epro_amazon_template_synchronization
       CHANGE COLUMN relist_qty relist_qty_magento tinyint(2) UNSIGNED NOT NULL,
       CHANGE COLUMN relist_qty_value relist_qty_magento_value int(11) UNSIGNED NOT NULL,
       CHANGE COLUMN relist_qty_value_max relist_qty_magento_value_max int(11) UNSIGNED NOT NULL,
       ADD COLUMN relist_qty_calculated tinyint(2) UNSIGNED NOT NULL AFTER relist_qty_magento_value_max,
       ADD COLUMN relist_qty_calculated_value int(11) UNSIGNED NOT NULL AFTER relist_qty_calculated,
       ADD COLUMN relist_qty_calculated_value_max int(11) UNSIGNED NOT NULL AFTER relist_qty_calculated_value;

    ALTER TABLE m2epro_buy_template_synchronization
       CHANGE COLUMN relist_qty relist_qty_magento tinyint(2) UNSIGNED NOT NULL,
       CHANGE COLUMN relist_qty_value relist_qty_magento_value int(11) UNSIGNED NOT NULL,
       CHANGE COLUMN relist_qty_value_max relist_qty_magento_value_max int(11) UNSIGNED NOT NULL,
       ADD COLUMN relist_qty_calculated tinyint(2) UNSIGNED NOT NULL AFTER relist_qty_magento_value_max,
       ADD COLUMN relist_qty_calculated_value int(11) UNSIGNED NOT NULL AFTER relist_qty_calculated,
       ADD COLUMN relist_qty_calculated_value_max int(11) UNSIGNED NOT NULL AFTER relist_qty_calculated_value;

    ALTER TABLE m2epro_ebay_template_synchronization
       CHANGE COLUMN relist_qty relist_qty_magento tinyint(2) UNSIGNED NOT NULL,
       CHANGE COLUMN relist_qty_value relist_qty_magento_value int(11) UNSIGNED NOT NULL,
       CHANGE COLUMN relist_qty_value_max relist_qty_magento_value_max int(11) UNSIGNED NOT NULL,
       ADD COLUMN relist_qty_calculated tinyint(2) UNSIGNED NOT NULL AFTER relist_qty_magento_value_max,
       ADD COLUMN relist_qty_calculated_value int(11) UNSIGNED NOT NULL AFTER relist_qty_calculated,
       ADD COLUMN relist_qty_calculated_value_max int(11) UNSIGNED NOT NULL AFTER relist_qty_calculated_value;

    ALTER TABLE m2epro_play_template_synchronization
       CHANGE COLUMN relist_qty relist_qty_magento tinyint(2) UNSIGNED NOT NULL,
       CHANGE COLUMN relist_qty_value relist_qty_magento_value int(11) UNSIGNED NOT NULL,
       CHANGE COLUMN relist_qty_value_max relist_qty_magento_value_max int(11) UNSIGNED NOT NULL,
       ADD COLUMN relist_qty_calculated tinyint(2) UNSIGNED NOT NULL AFTER relist_qty_magento_value_max,
       ADD COLUMN relist_qty_calculated_value int(11) UNSIGNED NOT NULL AFTER relist_qty_calculated,
       ADD COLUMN relist_qty_calculated_value_max int(11) UNSIGNED NOT NULL AFTER relist_qty_calculated_value;
*/

//---------------------------------------------

$tempTables = array('m2epro_amazon_template_synchronization',
                    'm2epro_buy_template_synchronization',
                    'm2epro_ebay_template_synchronization',
                    'm2epro_play_template_synchronization');

$actions = array('list','relist','stop');

foreach ($tempTables as $tempTable) {

    $tempTable = $installer->getTable($tempTable);
    foreach ($actions as $actionName) {

        $oldColumnName = $actionName.'_qty';
        $newColumnName = $actionName.'_qty_magento';

        if ($connection->tableColumnExists($tempTable, $oldColumnName) !== false &&
            $connection->tableColumnExists($tempTable, $newColumnName) === false) {
            $connection->changeColumn(
                $tempTable, $oldColumnName, $newColumnName, 'tinyint(2) UNSIGNED NOT NULL'
            );
        }

        $oldColumnName = $actionName.'_qty_value';
        $newColumnName = $actionName.'_qty_magento_value';

        if ($connection->tableColumnExists($tempTable, $oldColumnName) !== false &&
            $connection->tableColumnExists($tempTable, $newColumnName) === false) {
            $connection->changeColumn(
                $tempTable, $oldColumnName, $newColumnName, 'int(11) UNSIGNED NOT NULL'
            );
        }

        $oldColumnName = $actionName.'_qty_value_max';
        $newColumnName = $actionName.'_qty_magento_value_max';

        if ($connection->tableColumnExists($tempTable, $oldColumnName) !== false &&
            $connection->tableColumnExists($tempTable, $newColumnName) === false) {
            $connection->changeColumn(
                $tempTable, $oldColumnName, $newColumnName, 'int(11) UNSIGNED NOT NULL'
            );
        }

        $columnName  = $actionName.'_qty_calculated';
        $afterColumn = $actionName.'_qty_magento_value_max';

        if ($connection->tableColumnExists($tempTable, $columnName) === false) {
            $connection->addColumn(
                $tempTable, $columnName, "tinyint(2) UNSIGNED NOT NULL AFTER `{$afterColumn}`"
            );
        }

        $columnName  = $actionName.'_qty_calculated_value';
        $afterColumn = $actionName.'_qty_calculated';

        if ($connection->tableColumnExists($tempTable, $columnName) === false) {
            $connection->addColumn(
                $tempTable, $columnName, "int(11) UNSIGNED NOT NULL AFTER `{$afterColumn}`"
            );
        }

        $columnName  = $actionName.'_qty_calculated_value_max';
        $afterColumn = $actionName.'_qty_calculated_value';

        if ($connection->tableColumnExists($tempTable, $columnName) === false) {
            $connection->addColumn(
                $tempTable, $columnName, "int(11) UNSIGNED NOT NULL AFTER `{$afterColumn}`"
            );
        }
    }
}

//#############################################

$installer->endSetup();

//#############################################