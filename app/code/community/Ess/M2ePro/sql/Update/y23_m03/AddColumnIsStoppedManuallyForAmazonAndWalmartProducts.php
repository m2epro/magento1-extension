<?php

// @codingStandardsIgnoreFile

class  Ess_M2ePro_Sql_Update_y23_m03_AddColumnIsStoppedManuallyForAmazonAndWalmartProducts extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    /**
     * @return void
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Exception
     */
    public function execute()
    {
        $this->modifyTable('amazon_listing_product', 'is_general_id_owner');
        $this->modifyTable('walmart_listing_product', 'status_change_reasons');
    }

    /**
     * @param string $tableName
     * @param string $afterColumn
     * @return void
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Exception
     */
    private function modifyTable($tableName, $afterColumn)
    {
        $tableModifier = $this->_installer->getTableModifier($tableName);
        $tableModifier->addColumn('is_stopped_manually', 'TINYINT(2) UNSIGNED NOT NULL', 0, $afterColumn);
    }
}
