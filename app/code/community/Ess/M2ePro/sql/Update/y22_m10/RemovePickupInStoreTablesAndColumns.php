<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m10_RemovePickupInStoreTablesAndColumns extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->_installer->getTableModifier('ebay_marketplace')->dropColumn('is_in_store_pickup');

        $this->_installer->getConnection()->dropTable(
            $this->_installer->getFullTableName('ebay_account_pickup_store')
        );
        $this->_installer->getConnection()->dropTable(
            $this->_installer->getFullTableName('ebay_account_pickup_store_state')
        );
        $this->_installer->getConnection()->dropTable(
            $this->_installer->getFullTableName('ebay_account_pickup_store_log')
        );
        $this->_installer->getConnection()->dropTable(
            $this->_installer->getFullTableName('ebay_listing_product_pickup_store')
        );

        $this->_installer->getMainConfigModifier()->delete('/logs/clearing/ebay_pickup_store/');
        $this->_installer->getMainConfigModifier()->delete('/logs/ebay_pickup_store/');
    }
}
