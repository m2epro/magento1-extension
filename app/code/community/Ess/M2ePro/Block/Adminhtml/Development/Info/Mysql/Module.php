<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Development_Info_Mysql_Module extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('developmentDatabaseModule');
        // ---------------------------------------

        $this->setTemplate('M2ePro/development/info/mysql/module.phtml');
    }

    //########################################

    public function getInfoTables()
    {
        $tablesData = array_merge(
            $this->getConfigTables(),
            $this->getLocksAndChangeTables(),
            $this->getAdditionalTables()
        );

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

    //########################################

    protected function getConfigTables()
    {
        return array(
            'Config' => array(
                'm2epro_config',
                'm2epro_primary_config',
                'm2epro_cache_config',
                'm2epro_registry'
            )
        );
    }

    protected function getLocksAndChangeTables()
    {
        return array(
            'Additional' => array(
                'm2epro_lock_item',
                'm2epro_lock_transactional',
                'm2epro_listing_product_instruction',
                'm2epro_listing_product_scheduled_action',
                'm2epro_order_change',
                'm2epro_operation_history',
            )
        );
    }

    protected function getAdditionalTables()
    {
        return array(
            'Processing' => array(
                'm2epro_processing',
                'm2epro_processing_lock',
                'm2epro_request_pending_single',
                'm2epro_request_pending_partial',
                'm2epro_connector_pending_requester_single',
                'm2epro_connector_pending_requester_partial',
            )
        );
    }

    //########################################
}
