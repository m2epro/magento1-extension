<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m05_ConvertIntoInnoDB extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $connection = $this->_installer->getConnection();

        $wizard = $connection->select()
            ->from($this->_installer->getFullTableName('m2epro_wizard'))
            ->where('nick = ?', 'migrationToInnodb')
            ->query()
            ->fetch();

        if ($wizard === false) {
            $connection->insert($this->_installer->getTable('m2epro_wizard'), array(
                'nick'     => 'migrationToInnodb',
                'view'     => '*',
                'status'   => 0,
                'step'     => null,
                'type'     => 1,
                'priority' => 11
            ));
        }

        foreach ($this->truncateTables() as $table) {
            $table = $this->_installer->getFullTableName($table);
            if (!$this->_installer->tableExists($table)) {
                continue;
            }

            $connection->truncateTable($table);
        }

        foreach ($this->getDictionaryCategoryTables() as $dictionaryCategoryTable) {
            $this->_installer
                ->getTableModifier($dictionaryCategoryTable)
                ->dropIndex('product_data_nicks', false)
                ->dropIndex('path', false)
                ->commit();

            $tableName = $this->_installer->getTable($dictionaryCategoryTable);

            $this->_installer->run(<<<SQL
ALTER TABLE `{$tableName}` ADD INDEX `path` (`path`(255))
SQL
            );
        }

        $tables = array_merge(
            $this->getGeneralTables(),
            $this->getEbayTables(),
            $this->getAmazonTables(),
            $this->getWalmartTables()
        );

        foreach ($tables as $table) {
            $table = $this->_installer->getFullTableName($table);
            if (!$this->_installer->tableExists($table)) {
                continue;
            }

            $this->_installer->run(
                <<<SQL
ALTER TABLE `{$table}` ENGINE=InnoDB
SQL
            );
        }
    }

    //########################################

    private function getGeneralTables()
    {
        return array(
            'm2epro_listing_log',
            'm2epro_lock_item',
            'm2epro_order_change',
            'm2epro_processing',
            'm2epro_processing_lock',
            'm2epro_request_pending_single',
            'm2epro_request_pending_partial',
            'm2epro_request_pending_partial_data',
            'm2epro_stop_queue',
            'm2epro_synchronization_log',
            'm2epro_system_log',
            'm2epro_operation_history'
        );
    }

    // ---------------------------------------

    private function getEbayTables()
    {
        return array(
            'm2epro_ebay_account_pickup_store_log',
            'm2epro_ebay_dictionary_category',
            'm2epro_ebay_dictionary_motor_epid',
            'm2epro_ebay_dictionary_motor_ktype',
            'm2epro_ebay_motor_filter'
        );
    }

    // ---------------------------------------

    private function getAmazonTables()
    {
        return array(
            'm2epro_amazon_dictionary_category',
            'm2epro_amazon_dictionary_category_product_data',
            'm2epro_amazon_dictionary_marketplace',
            'm2epro_amazon_dictionary_specific'
        );
    }

    // ---------------------------------------

    private function getWalmartTables()
    {
        return array(
            'm2epro_walmart_dictionary_category',
            'm2epro_walmart_dictionary_marketplace',
            'm2epro_walmart_dictionary_specific',
        );
    }

    private function getDictionaryCategoryTables()
    {
        return array(
            'm2epro_ebay_dictionary_category',
            'm2epro_amazon_dictionary_category',
            'm2epro_walmart_dictionary_category'
        );
    }

    //########################################

    private function truncateTables()
    {
        return array(
            'm2epro_system_log',
            'm2epro_synchronization_log',
            'm2epro_operation_history',

            'm2epro_ebay_dictionary_category',
            'm2epro_ebay_dictionary_marketplace',
            'm2epro_ebay_dictionary_motor_epid',
            'm2epro_ebay_dictionary_motor_ktype',
            'm2epro_ebay_dictionary_shipping',

            'm2epro_amazon_dictionary_category',
            'm2epro_amazon_dictionary_category_product_data',
            'm2epro_amazon_dictionary_marketplace',
            'm2epro_amazon_dictionary_specific',

            'm2epro_walmart_dictionary_category',
            'm2epro_walmart_dictionary_marketplace',
            'm2epro_walmart_dictionary_specific'
        );
    }

    //########################################
}
