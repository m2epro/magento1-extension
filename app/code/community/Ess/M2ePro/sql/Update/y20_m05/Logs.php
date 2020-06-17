<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m05_Logs extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getConnection()->truncateTable(
            $this->_installer->getFullTableName('system_log')
        );

        $this->_installer->getConnection()->truncateTable(
            $this->_installer->getFullTableName('synchronization_log')
        );

        //----------------------------------------

        // exception in renaming on repeat upgrade because of "description" column will be added again
        $systemLogMod = $this->_installer->getTableModifier('system_log');
        if ($systemLogMod->isColumnExists('description') && !$systemLogMod->isColumnExists('detailed_description')) {
            $systemLogMod->renameColumn('description', 'detailed_description');
        }

        $systemLogMod
            ->addColumn('class', 'VARCHAR(255)', 'NULL', 'type', true)
            ->addColumn('description', 'TEXT', 'NULL', 'class')
            ->dropColumn('update_date', true);

        $this->_installer->getTableModifier('synchronization_log')
            ->addColumn('detailed_description', 'LONGTEXT', 'NULL', 'description', false, false)
            ->dropColumn('priority', true, false)
            ->dropColumn('update_date', true, false)
            ->commit();

        $this->_installer->getTableModifier('listing_log')
            ->dropColumn('priority', true, false)
            ->dropColumn('update_date', true, false)
            ->commit();

        $this->_installer->getTableModifier('order_log')
            ->dropColumn('update_date', true, false)
            ->commit();

        $this->_installer->getTableModifier('ebay_account_pickup_store_log')
            ->dropColumn('priority', true, false)
            ->dropColumn('update_date', true, false)
            ->commit();
    }

    //########################################
}
