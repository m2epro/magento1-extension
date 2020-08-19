<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m07_HashLongtextFields extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        if (!$this->isTableMigratedToMd5('amazon_listing_product', 'online_details_data')) {
            $this->_installer->run(
                <<<SQL
UPDATE `{$this->_installer->getFullTableName('amazon_listing_product')}`
SET 
`online_details_data` = md5(online_details_data),
`online_images_data` = md5(online_images_data);
SQL
            );
        }

        if (!$this->isTableSchemaMigrated('amazon_listing_product', 'online_details_data')) {
            $this->_installer->getTableModifier('amazon_listing_product')
                ->changeColumn('online_details_data', 'VARCHAR(40) DEFAULT NULL', null, null, false)
                ->changeColumn('online_images_data', 'VARCHAR(40) DEFAULT NULL', null, null, false)
                ->commit();
        }

        if (!$this->isTableMigratedToMd5('ebay_listing_product', 'online_description')) {
            $this->_installer->run(
                <<<SQL
UPDATE `{$this->_installer->getFullTableName('ebay_listing_product')}`
SET
`online_description` = md5(online_description),
`online_images` = md5(online_images),
`online_shipping_data` = md5(online_shipping_data),
`online_payment_data` = md5(online_payment_data),
`online_return_data` = md5(online_return_data),
`online_other_data` = md5(online_other_data);
SQL
            );
        }

        if (!$this->isTableSchemaMigrated('ebay_listing_product', 'online_description')) {
            $this->_installer->getTableModifier('ebay_listing_product')
                ->changeColumn('online_description', 'VARCHAR(40) DEFAULT NULL', null, null, false)
                ->changeColumn('online_images', 'VARCHAR(40) DEFAULT NULL', null, null, false)
                ->changeColumn('online_shipping_data', 'VARCHAR(40) DEFAULT NULL', null, null, false)
                ->changeColumn('online_payment_data', 'VARCHAR(40) DEFAULT NULL', null, null, false)
                ->changeColumn('online_return_data', 'VARCHAR(40) DEFAULT NULL', null, null, false)
                ->changeColumn('online_other_data', 'VARCHAR(40) DEFAULT NULL', null, null, false)
                ->commit();
        }

        if (!$this->isTableMigratedToMd5('walmart_listing_product', 'online_details_data')) {
            $this->_installer->run(
                <<<SQL
UPDATE `{$this->_installer->getFullTableName('walmart_listing_product')}`
SET
`online_promotions` = md5(online_promotions),
`online_details_data` = md5(online_details_data);
SQL
            );
        }

        if (!$this->isTableSchemaMigrated('walmart_listing_product', 'online_details_data')) {
            $this->_installer->getTableModifier('walmart_listing_product')
                ->changeColumn('online_promotions', 'VARCHAR(40) DEFAULT NULL', null, null, false)
                ->changeColumn('online_details_data', 'VARCHAR(40) DEFAULT NULL', null, null, false)
                ->commit();
        }
    }

    //########################################

    private function isTableMigratedToMd5($tableName, $column)
    {
        $value = $this->_installer->getConnection()
            ->select()
            ->from($this->_installer->getFullTableName($tableName), $column)
            ->where("`{$column}` IS NOT NULL")
            ->query()
            ->fetchColumn();

        if (32 != strlen($value) || !ctype_xdigit($value)) {
            return false;
        }

        return true;
    }

    private function isTableSchemaMigrated($tableName, $column)
    {
        $describe = $this->_installer->getConnection()->describeTable($this->_installer->getFullTableName($tableName));
        $prop = $describe[$column];

        if ($prop['DATA_TYPE'] != Varien_Db_Ddl_Table::TYPE_VARCHAR && (int)$prop['LENGTH'] != 40) {
            return false;
        }

        return true;
    }

    //########################################
}
