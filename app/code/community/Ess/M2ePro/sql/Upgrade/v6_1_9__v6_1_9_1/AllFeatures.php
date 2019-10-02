<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_1_9__v6_1_9_1_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;
        $connection = $installer->getConnection();

        $tempTables = array(
            'm2epro_amazon_template_synchronization',
            'm2epro_buy_template_synchronization',
            'm2epro_ebay_template_synchronization',
            'm2epro_play_template_synchronization'
        );

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
    }

    //########################################
}