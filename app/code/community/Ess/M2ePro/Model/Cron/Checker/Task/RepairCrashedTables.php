<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Checker_Task_RepairCrashedTables
    extends Ess_M2ePro_Model_Cron_Checker_Task_Abstract
{
    const NICK = 'repair_crashed_tables';

    //########################################

    /**
     * @return array
     */
    private function getTablesForCheck()
    {
        return array(
            'm2epro_operation_history',
            'm2epro_listing_log',
            'm2epro_listing_other_log',
            'm2epro_order_log',
            'm2epro_synchronization_log',
            'm2epro_lock_item',
            'm2epro_processing_lock'
        );
    }

    //########################################

    public function performActions()
    {
        $crashedTables = $this->getCrashedTablesFromLog();

        $workingTables = $this->getAlreadyRepairedTables($crashedTables);
        $this->performActionsToAlreadyWorkingTables($workingTables);

        $tablesForRepair = array_diff($crashedTables, $workingTables);
        $tablesForRepair = $this->filterAlreadyTriedToRepairTables($tablesForRepair);

        if (empty($tablesForRepair)) {
            return;
        }

        $this->processRepairTables($tablesForRepair);
    }

    //########################################

    /**
     * @return array
     */
    private function getCrashedTablesFromLog()
    {
        $crashedTables = array();

        foreach ($this->getCrashedTablesLogRecords() as $row) {
            $table = $this->findTableNameInLog($row['description']);
            !is_null($table) && $crashedTables[] = $table;
        }

        return array_unique($crashedTables);
    }

    private function processRepairTables($tablesForRepair)
    {
        foreach ($tablesForRepair as $table) {

            if (!Mage::helper('M2ePro/Module_Database_Structure')->isTableExists($table)) {
                continue;
            }

            if (!Mage::helper('M2ePro/Module_Database_Repair')->repairCrashedTable($table)) {
                $this->setTryToRepair($table);
            }
        }
    }

    //########################################

    /**
     * @param array $tables
     * @return array
     */
    private function getAlreadyRepairedTables(array $tables)
    {
        $workingTables = array();
        foreach ($tables as $table) {
            Mage::helper('M2ePro/Module_Database_Structure')->isTableStatusOk($table) && $workingTables[] = $table;
        }

        return $workingTables;
    }

    /**
     * @param array $tables
     */
    private function performActionsToAlreadyWorkingTables($tables)
    {
        foreach ($tables as $table) {
            $this->unsetTryToRepair($table);
        }
    }

    //########################################

    /**
     * @param array $tables
     * @return array
     */
    private function filterAlreadyTriedToRepairTables($tables)
    {
        $filteredTables = array();
        foreach ($tables as $table) {
            !$this->wasTryToRepair($table) && $filteredTables[] = $table;
        }
        return $filteredTables;
    }

    //########################################

    /**
     * @return array
     */
    private function getCrashedTablesLogRecords()
    {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');

        $currentDate = Mage::helper('M2ePro')->getCurrentGmtDate();
        $dateTimeTo = new DateTime($currentDate, new DateTimeZone('UTC'));

        $dateTimeFrom = new DateTime($currentDate, new DateTimeZone('UTC'));
        $dateTimeFrom->modify('-1 hour');

        $tableName = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_system_log');
        $select = $readConnection->select()
            ->from($tableName, array('id', 'type', 'description', 'create_date'))
            ->where($readConnection->quoteInto('`type` = ?', 'Zend_Db_Statement_Exception'))
            ->where($readConnection->quoteInto('`create_date` >= ?', $dateTimeFrom->format('Y-m-d H:i:s')))
            ->where($readConnection->quoteInto('`create_date` <= ?', $dateTimeTo->format('Y-m-d H:i:s')))
            ->where('`description` REGEXP "General error\: (126|127|132|134|141|144|145)"');

        return $select->query()->fetchAll();
    }

    /**
     * @param string $description
     * @return null|string
     */
    private function findTableNameInLog($description)
    {
        $tablesForPattern = implode('|', $this->getTablesForCheck());
        $pattern = '/`(\w{1,})?('.$tablesForPattern.')\`/i';
        preg_match($pattern, $description, $matches);

        if (isset($matches[2])) {
            return $matches[2];
        }

        return NULL;
    }

    //########################################

    private function setTryToRepair($table)
    {
        Mage::helper('M2ePro/Module')->getCacheConfig()
            ->setGroupValue('/cron/repair_tables/'.$table.'/', 'tried_to_repair', 1);
    }

    private function unsetTryToRepair($table)
    {
        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();
        if ($cacheConfig->getGroupValue('/cron/repair_tables/'.$table.'/', 'tried_to_repair')) {
            $cacheConfig->deleteGroupValue('/cron/repair_tables/'.$table.'/', 'tried_to_repair');
        }
    }

    /**
     * @param string $table
     * @return bool
     */
    private function wasTryToRepair($table)
    {
        return (bool)Mage::helper('M2ePro/Module')->getCacheConfig()
            ->getGroupValue('/cron/repair_tables/'.$table.'/', 'tried_to_repair');
    }

    //########################################
}