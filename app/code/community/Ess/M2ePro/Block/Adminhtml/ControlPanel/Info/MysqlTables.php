<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method getTablesList()
 */
class Ess_M2ePro_Block_Adminhtml_ControlPanel_Info_MysqlTables extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('controlPanelDatabaseModule');
        $this->setTemplate('M2ePro/controlPanel/info/mysqlTables.phtml');
    }

    //########################################

    public function getTablesInfo()
    {
        $tablesInfo = array();
        $helper = Mage::helper('M2ePro/Module_Database_Structure');

        foreach ($this->getTablesList() as $category => $tables) {
            foreach ($tables as $tableName) {
                $tablesInfo[$category][$tableName] = array(
                    'count' => 0,
                    'url'   => '#'
                );

                if (!$helper->isTableReady($tableName)) {
                    continue;
                }

                $tablesInfo[$category][$tableName]['count'] = $helper->getCountOfRecords($tableName);
                $tablesInfo[$category][$tableName]['url'] = $this->getUrl(
                    '*/adminhtml_controlPanel_database/manageTable', array('table' => $tableName)
                );
            }
        }

        return $tablesInfo;
    }

    //########################################
}
