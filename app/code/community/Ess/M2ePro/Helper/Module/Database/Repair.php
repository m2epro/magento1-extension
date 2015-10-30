<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Module_Database_Repair extends Mage_Core_Helper_Abstract
{
    //########################################

    public function getBrokenTablesInfo()
    {
        $horizontalTables = Mage::helper('M2ePro/Module_Database_Structure')->getHorizontalTables();

        $brokenParentTables   = array();
        $brokenChildrenTables = array();
        $totalBrokenTables = 0;

        foreach ($horizontalTables as $parentTable => $childrenTables) {

            if ($brokenItemsCount = $this->getBrokenRecordsInfo($parentTable, true)) {
                $brokenParentTables[$parentTable] = $brokenItemsCount;
                $totalBrokenTables++;
            }

            foreach ($childrenTables as $childrenTable) {

                if ($brokenItemsCount = $this->getBrokenRecordsInfo($childrenTable, true)) {
                    $brokenChildrenTables[$childrenTable] = $brokenItemsCount;
                    $totalBrokenTables++;
                }
            }
        }

        return array(
            'parent'      => $brokenParentTables,
            'children'    => $brokenChildrenTables,
            'total_count' => $totalBrokenTables
        );
    }

    public function getBrokenRecordsInfo($table, $returnOnlyCount = false)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $allTables = Mage::helper('M2ePro/Module_Database_Structure')->getHorizontalTables();

        $result = $returnOnlyCount ? 0 : array();

        foreach ($allTables as $parentTable => $childTables) {
            foreach ($childTables as $component => $childTable) {

                if (!in_array($table, array($parentTable, $childTable))) {
                    continue;
                }

                $parentTablePrefix = Mage::getSingleton('core/resource')->getTableName($parentTable);
                $childTablePrefix = Mage::getSingleton('core/resource')->getTableName($childTable);

                $parentIdColumn = Mage::helper('M2ePro/Module_Database_Structure')->getIdColumn($parentTable);
                $childIdColumn  = Mage::helper('M2ePro/Module_Database_Structure')->getIdColumn($childTable);

                if ($table == $parentTable) {

                    $stmtQuery = $connRead->select()
                        ->from(array('parent' => $parentTablePrefix),
                               $returnOnlyCount ? new Zend_Db_Expr('count(*) as `count_total`')
                                                : array('id' => $parentIdColumn))
                        ->joinLeft(array('child' => $childTablePrefix),
                                   '`parent`.`'.$parentIdColumn.'` = `child`.`'.$childIdColumn.'`',
                                   array())
                        ->where('`parent`.`component_mode` = ?', $component)
                        ->where('`child`.`'.$childIdColumn.'` IS NULL')
                        ->query();

                } else if ($table == $childTable) {

                    $stmtQuery = $connRead->select()
                        ->from(array('child' => $childTablePrefix),
                               $returnOnlyCount ? new Zend_Db_Expr('count(*) as `count_total`')
                                                : array('id' => $childIdColumn))
                        ->joinLeft(array('parent' => $parentTablePrefix),
                                   '`child`.`'.$childIdColumn.'` = `parent`.`'.$parentIdColumn.'`',
                                   array())
                        ->where('`parent`.`'.$parentIdColumn.'` IS NULL')
                        ->query();
                }

                if ($returnOnlyCount) {
                    $row = $stmtQuery->fetch();
                    $result += (int)$row['count_total'];
                } else {
                    while ($row = $stmtQuery->fetch()) {
                        $result[] = (int)$row['id'];
                    }
                }
            }
        }

        return $result;
    }

    public function repairBrokenTables(array $tables)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        foreach ($tables as $table) {

            $brokenIds = $this->getBrokenRecordsInfo($table);
            if (count($brokenIds) <= 0) {
                continue;
            }
            $brokenIds = array_slice($brokenIds,0,50000);

            $tableWithPrefix = Mage::getSingleton('core/resource')->getTableName($table);
            $idColumnName = Mage::helper('M2ePro/Module_Database_Structure')->getIdColumn($table);

            foreach (array_chunk($brokenIds,1000) as $brokenIdsPart) {

                if (count($brokenIdsPart) <= 0) {
                    continue;
                }

                $connWrite->delete(
                    $tableWithPrefix,
                    '`'.$idColumnName.'` IN ('.implode (',',$brokenIdsPart).')'
                );
            }

            $logTemp = "Table: {$table} ## Amount: ".count($brokenIds);
            Mage::log($logTemp, null, 'm2epro_repair_tables.log',true);
        }
    }

    // ---------------------------------------

    /**
     * @param $tableName
     * @return string <p> OK if repair was successfully or Error Message if not. </p>
     */
    public function repairCrashedTable($tableName)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $tableName = Mage::getSingleton('core/resource')->getTableName($tableName);

        $result = $connWrite->query("REPAIR TABLE `{$tableName}`")->fetch();
        return $result['Msg_text'];
    }

    // ---------------------------------------

    public function fixColumnIndex($tableName, array $columnInfo)
    {
        if (!isset($columnInfo['name'], $columnInfo['key'])) {
            return;
        }

        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableName = Mage::getSingleton('core/resource')->getTableName($tableName);

        if (empty($columnInfo['key'])) {
            $writeConnection->dropIndex($tableName, $columnInfo['name']);
            return;
        }

        $indexType = Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY;
        $columnInfo['key'] == 'mul' && $indexType = Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX;
        $columnInfo['key'] == 'uni' && $indexType = Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE;

        $writeConnection->addIndex($tableName, $columnInfo['name'], $columnInfo['name'], $indexType);
    }

    public function fixColumnProperties($tableName, array $columnInfo)
    {
        if (!isset($columnInfo['name'])) {
            return;
        }

        $definition = "{$columnInfo['type']} ";
        $columnInfo['null'] == 'no' && $definition .= 'NOT NULL ';
        $columnInfo['default'] != '' && $definition .= "DEFAULT '{$columnInfo['default']}' ";
        ($columnInfo['null'] == 'yes' && $columnInfo['default'] == '') && $definition .= 'DEFAULT NULL ';
        !empty($columnInfo['after']) && $definition .= "AFTER `{$columnInfo['after']}`";

        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableName = Mage::getSingleton('core/resource')->getTableName($tableName);

        if ($writeConnection->tableColumnExists($tableName, $columnInfo['name']) === false) {
            $writeConnection->addColumn($tableName, $columnInfo['name'], $definition);
            return;
        }

        $writeConnection->changeColumn($tableName, $columnInfo['name'], $columnInfo['name'], $definition);
    }

    public function dropColumn($tableName, array $columnInfo)
    {
        if (!isset($columnInfo['name'])) {
            return;
        }

        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableName = Mage::getSingleton('core/resource')->getTableName($tableName);

        $writeConnection->dropColumn($tableName, $columnInfo['name']);
    }

    //########################################
}