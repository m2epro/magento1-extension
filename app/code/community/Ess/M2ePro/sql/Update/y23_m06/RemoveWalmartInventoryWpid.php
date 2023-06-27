<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y23_m06_RemoveWalmartInventoryWpid extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->_installer->getConnection()->dropTable(
            $this->_installer->getFullTableName('walmart_inventory_wpid')
        );
    }
}
