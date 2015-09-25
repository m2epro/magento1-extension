<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Info_Mysql_Module extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentDatabaseModule');
        //------------------------------

        $this->setTemplate('M2ePro/development/info/mysql/module.phtml');
    }

    // ########################################

    public function getInfoTables()
    {
        $tablesData = array_merge($this->getConfigTables(),
                                  $this->getLocksAndChangeTables(),
                                  $this->getAdditionalTables());

        $helper = Mage::helper('M2ePro/Module_Database_Structure');

        $tablesInfo = array();
        foreach ($tablesData as $category => $tables) {
            foreach ($tables as $tableName) {

                $tablesInfo[$category][$tableName] = array(
                    'count' => 0, 'url'   => '#'
                );

                if (!$helper->isTableReady($tableName)) {
                    continue;
                }

                $tablesInfo[$category][$tableName]['count'] = $helper->getCountOfRecords($tableName);
                $tablesInfo[$category][$tableName]['url'] = $this->getUrl(
                    '*/adminhtml_development_database/manageTable', array('table' => $tableName)
                );
            }
        }

        return $tablesInfo;
    }

    // ########################################

    private function getConfigTables()
    {
        return array(
            'Config' => array(
                'm2epro_primary_config',
                'm2epro_config',
                'm2epro_synchronization_config',
                'm2epro_cache_config'
            )
        );
    }

    private function getLocksAndChangeTables()
    {
        return array(
            'Locks / Changes' => array(
                'm2epro_lock_item',
                'm2epro_locked_object',
                'm2epro_product_change',
                'm2epro_order_change'
            )
        );
    }

    private function getAdditionalTables()
    {
        return array(
            'Additional' => array(
                'm2epro_processing_request',
                'm2epro_operation_history'
            )
        );
    }

    // ########################################
}