<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Module_Database_Structure extends Mage_Core_Helper_Abstract
{
    //########################################

    public function getMysqlTables()
    {
        $cacheData = Mage::helper('M2ePro/Data_Cache_Runtime')->getValue(__METHOD__);
        if (null !== $cacheData) {
            return $cacheData;
        }

        $result = array();

        $queryStmt = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from('information_schema.tables', array('table_name'))
            ->where('table_schema = ?', Mage::helper('M2ePro/Magento')->getDatabaseName())
            ->where('table_name LIKE ?', "%m2epro\_%")
            ->query();

        while ($tableName = $queryStmt->fetchColumn()) {
            $result[] = $tableName;
        }

        Mage::helper('M2ePro/Data_Cache_Runtime')->setValue(__METHOD__, $result);
        return $result;
    }

    public function getModuleTables()
    {
        return array_keys($this->getTablesModels());
    }

    //########################################

    public function getHorizontalTables()
    {
        $cacheData = Mage::helper('M2ePro/Data_Cache_Runtime')->getValue(__METHOD__);
        if (null !== $cacheData) {
            return $cacheData;
        }

        $components = Mage::helper('M2ePro/Component')->getComponents();
        $mySqlTables = $this->getModuleTables();

        // minimal amount of child tables to be a horizontal table
        $minimalAmount = 2;

        $result = array();
        foreach ($mySqlTables as $mySqlTable) {
            $tempComponentTables = array();
            $mySqlTableCropped = str_replace('m2epro_', '', $mySqlTable);

            foreach ($components as $component) {
                $needComponentTable = "m2epro_{$component}_{$mySqlTableCropped}";

                if (in_array($needComponentTable, $mySqlTables)) {
                    $tempComponentTables[$component] = $needComponentTable;
                } else {
                    break;
                }
            }

            if (count($tempComponentTables) >= $minimalAmount) {
                $result[$mySqlTable] = $tempComponentTables;
            }
        }

        Mage::helper('M2ePro/Data_Cache_Runtime')->setValue(__METHOD__, $result);
        return $result;
    }

    // ---------------------------------------

    public function getTableComponent($tableName)
    {
        foreach (Mage::helper('M2ePro/Component')->getComponents() as $component) {
            if (strpos(strtolower($tableName), strtolower($component)) !== false) {
                return $component;
            }
        }

        return 'general';
    }

    public function isModuleTable($tableName)
    {
        return strpos($tableName, 'm2epro_') !== false;
    }

    public function isTableHorizontal($tableName)
    {
        return $this->isTableHorizontalChild($tableName) || $this->isTableHorizontalParent($tableName);
    }

    public function isTableHorizontalChild($tableName)
    {
        $horizontalTables = $this->getHorizontalTables();

        $modifiedTableName = str_replace(Mage::helper('M2ePro/Component')->getComponents(), '', $tableName);
        $modifiedTableName = str_replace('__', '_', $modifiedTableName);

        return !array_key_exists($tableName, $horizontalTables) &&
                array_key_exists($modifiedTableName, $horizontalTables);
    }

    public function isTableHorizontalParent($tableName)
    {
        return array_key_exists($tableName, $this->getHorizontalTables());
    }

    // ---------------------------------------

    public function isTableExists($tableName)
    {
        $cacheKey  = __METHOD__ . $tableName;
        $cacheData = Mage::helper('M2ePro/Data_Cache_Runtime')->getValue($cacheKey);

        if (null !== $cacheData) {
            return $cacheData;
        }

        $databaseName = Mage::helper('M2ePro/Magento')->getDatabaseName();
        $tableName = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix($tableName);

        $row = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->query("SHOW TABLE STATUS FROM `{$databaseName}` WHERE `name` = '{$tableName}'")
            ->fetch() ;

        $result = $row !== false;

        Mage::helper('M2ePro/Data_Cache_Runtime')->setValue($cacheKey, $result);
        return $result;
    }

    public function isTableStatusOk($tableName)
    {
        $cacheKey  = __METHOD__ . $tableName;
        $cacheData = Mage::helper('M2ePro/Data_Cache_Runtime')->getValue($cacheKey);

        if (null !== $cacheData) {
            return $cacheData;
        }

        $result = true;

        try {
            Mage::getSingleton('core/resource')->getConnection('core_read')
                ->select()
                ->from(
                    Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix($tableName),
                    new Zend_Db_Expr('1')
                )
                ->limit(1)
                ->query();
        } catch (Exception $e) {
            $result = false;
        }

        Mage::helper('M2ePro/Data_Cache_Runtime')->setValue($cacheKey, $result);
        return $result;
    }

    public function isTableReady($tableName)
    {
        return $this->isTableExists($tableName) && $this->isTableStatusOk($tableName);
    }

    // ---------------------------------------

    public function getCountOfRecords($tableName)
    {
        $cacheKey  = __METHOD__ . $tableName;
        $cacheData = Mage::helper('M2ePro/Data_Cache_Runtime')->getValue($cacheKey);

        if (null !== $cacheData) {
            return $cacheData;
        }

        $result = (int)Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix($tableName),
                new Zend_Db_Expr('COUNT(*)')
            )
            ->query()
            ->fetchColumn();

        Mage::helper('M2ePro/Data_Cache_Runtime')->setValue($cacheKey, $result);
        return $result;
    }

    public function getDataLength($tableName)
    {
        $cacheKey  = __METHOD__ . $tableName;
        $cacheData = Mage::helper('M2ePro/Data_Cache_Runtime')->getValue($cacheKey);

        if (null !== $cacheData) {
            return $cacheData;
        }

        $tableName = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix($tableName);
        $dataLength = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from('information_schema.tables', array(new Zend_Db_Expr('data_length + index_length')))
            ->where('`table_name` = ?', $tableName)
            ->where('`table_schema` = ?', Mage::helper('M2ePro/Magento')->getDatabaseName())
            ->query()
            ->fetchColumn();

        $dataLength = round($dataLength / 1024 / 1024, 2);

        Mage::helper('M2ePro/Data_Cache_Runtime')->setValue($cacheKey, $dataLength);
        return $dataLength;
    }

    // ---------------------------------------

    public function getTablesInfo()
    {
        $tablesInfo = array();
        foreach ($this->getMysqlTables() as $currentTable) {
            $currentTableInfo = $this->getTableInfo($currentTable);
            $currentTableWithoutPrefix = $this->getTableNameWithoutPrefix($currentTable);
            $currentTableInfo && $tablesInfo[$currentTableWithoutPrefix] = $currentTableInfo;
        }

        return $tablesInfo;
    }

    public function getTableInfo($tableName)
    {
        $cacheKey  = __METHOD__ . $tableName;
        $cacheData = Mage::helper('M2ePro/Data_Cache_Runtime')->getValue($cacheKey);

        if (null !== $cacheData) {
            return $cacheData;
        }

        $tableName = $this->getTableNameWithoutPrefix($tableName);
        if (!$this->isTableExists($tableName)) {
            return false;
        }

        $moduleTableName = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix($tableName);

        $stmtQuery = Mage::getResourceModel('core/config')->getReadConnection()->query(
            "SHOW COLUMNS FROM {$moduleTableName}"
        );

        $result = array();

        while ($row = $stmtQuery->fetch()) {
            $result[strtolower($row['Field'])] = array(
                'name'     => strtolower($row['Field']),
                'type'     => strtolower($row['Type']),
                'null'     => strtolower($row['Null']),
                'key'      => strtolower($row['Key']),
                'default'  => strtolower($row['Default']),
                'extra'    => strtolower($row['Extra']),
            );
        }

        Mage::helper('M2ePro/Data_Cache_Runtime')->setValue($cacheKey, $result);
        return $result;
    }

    public function getColumnInfo($table, $columnName)
    {
        $info = $this->getTableInfo($table);
        return isset($info[$columnName]) ? $info[$columnName] : null;
    }

    public function getTableModel($tableName)
    {
        $tablesModels = $this->getTablesModels();
        if (!isset($tablesModels[$tableName])) {
            return null;
        }

        return $tablesModels[$tableName];
    }

    protected function getTablesModels()
    {
        $cacheData = Mage::helper('M2ePro/Data_Cache_Runtime')->getValue(__METHOD__);
        if (null !== $cacheData) {
            return $cacheData;
        }

        $result = array();
        foreach (Mage::getConfig()->getNode('global/models/M2ePro_resource/entities')->asArray() as $model => $info) {
            $result[$info['table']] = $model;
        }

        Mage::helper('M2ePro/Data_Cache_Runtime')->setValue(__METHOD__, $result);
        return $result;
    }

    // ---------------------------------------

    public function getIdColumn($table)
    {
        $tableModel = $this->getTableModel($table);
        $tableModel = Mage::getModel('M2ePro/'.$tableModel);

        return $tableModel->getIdFieldName();
    }

    public function isIdColumnAutoIncrement($table)
    {
        $idColumn = $this->getIdColumn($table);
        $columnInfo = $this->getColumnInfo($table, $idColumn);

        return isset($columnInfo['extra']) && strpos($columnInfo['extra'], 'increment') !== false;
    }

    // ---------------------------------------

    public function getConfigSnapshot($table)
    {
        $tableModel = $this->getTableModel($table);

        $tableModel = Mage::getModel('M2ePro/'.$tableModel);
        $collection = $tableModel->getCollection()->toArray();

        $result = array();
        foreach ($collection['items'] as $item) {
            $codeHash = strtolower($item['group']).'#'.strtolower($item['key']);
            $result[$codeHash] = array(
                'id'     => (int)$item['id'],
                'group'  => $item['group'],
                'key'    => $item['key'],
                'value'  => $item['value'],
            );
        }

        return $result;
    }

    // ---------------------------------------

    public function getStoreRelatedColumns()
    {
        $result = array();

        $simpleColumns = array('store_id', 'related_store_id');
        $jsonColumns   = array('magento_orders_settings', 'marketplaces_data');

        foreach ($this->getTablesInfo() as $tableName => $tableInfo) {
            foreach ($tableInfo as $columnName => $columnInfo) {
                if (in_array($columnName, $simpleColumns)) {
                    $result[$tableName][] = array('name' => $columnName, 'type' => 'int');
                }

                if (in_array($columnName, $jsonColumns)) {
                    $result[$tableName][] = array('name' => $columnName, 'type' => 'json');
                }
            }
        }

        return $result;
    }

    public function getTableNameWithPrefix($tableName)
    {
        return Mage::getSingleton('core/resource')->getTableName($tableName);
    }

    public function getTableNameWithoutPrefix($tableName)
    {
        $replacePattern = '/^' . Mage::helper('M2ePro/Magento')->getDatabaseTablesPrefix() . '/';

        $tableName = preg_match($replacePattern, $tableName) ? $tableName : $this->getTableNameWithPrefix($tableName);
        return preg_replace(
            $replacePattern,
            '',
            $tableName
        );
    }

    //########################################
}
