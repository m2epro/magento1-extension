<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_Modifier_Table extends Ess_M2ePro_Model_Upgrade_Modifier_Abstract
{
    const COMMIT_KEY_ADD_COLUMN    = 'add_column';
    const COMMIT_KEY_DROP_COLUMN   = 'drop_column';
    const COMMIT_KEY_CHANGE_COLUMN = 'change_column';
    const COMMIT_KEY_ADD_INDEX     = 'add_index';
    const COMMIT_KEY_DROP_INDEX    = 'drop_index';

    protected $_sqlForCommit = array();
    protected $_columnsForCheckBeforeCommit = array();

    protected $_checkedTableRowFormat = false;

    //########################################

    public function truncate()
    {
        $this->getConnection()->truncate($this->getTableName());

        return $this;
    }

    //########################################

    /**
     * @param string $name
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function isColumnExists($name)
    {
        return $this->getConnection()->tableColumnExists($this->getTableName(), $name);
    }

    /**
     * @param string $from
     * @param string $to
     * @param bool $renameIndex
     * @param bool $autoCommit
     * @return $this
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Exception
     */
    public function renameColumn($from, $to, $renameIndex = true, $autoCommit = true)
    {
        if (!$this->isColumnExists($from) && $this->isColumnExists($to)) {
            return $this;
        }

        if ($this->isColumnExists($from) && $this->isColumnExists($to)) {
            throw new Ess_M2ePro_Model_Exception_Setup(
                "Column '{$from}' cannot be changed to '{$to}', because last one
                 already exists in '{$this->getTableName()}' table."
            );
        }

        if (!$this->isColumnExists($from) && !$this->isColumnExists($to)) {
            throw new Ess_M2ePro_Model_Exception_Setup(
                "Column '{$from}' cannot be changed, because
                 does not exist in '{$this->getTableName()}' table."
            );
        }

        $definition = $this->buildColumnDefinitionByName($from);

        if (empty($definition)) {
            throw new Ess_M2ePro_Model_Exception_Setup(
                "Definition for column '{$from}' in '{$this->getTableName()}' table is empty."
            );
        }

        if ($autoCommit) {
            $this->getConnection()->changeColumn($this->getTableName(), $from, $to, $definition);
        } else {
            $this->addQueryToCommit(
                self::COMMIT_KEY_CHANGE_COLUMN,
                'CHANGE COLUMN %s %s %s',
                array($from, $to),
                $definition
            );
        }

        if ($renameIndex) {
            $this->renameIndex($from, $to, $autoCommit);
        }

        return $this;
    }

    // ---------------------------------------

    /**
     * @param string $name
     * @param string $type
     * @param string|null $default
     * @param string|null $after
     * @param bool $addIndex
     * @param bool $autoCommit
     * @return $this
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Exception
     */
    public function addColumn($name, $type, $default = null, $after = null, $addIndex = false, $autoCommit = true)
    {
        if ($this->isColumnExists($name)) {
            return $this;
        }

        if ($this->isNeedChangeRowFormat()) {
            $this->changeRowFormat();
        }

        $definition = $this->buildColumnDefinition($type, $default, $after, $autoCommit);

        if (empty($definition)) {
            throw new Ess_M2ePro_Model_Exception_Setup(
                "Definition for '{$this->getTableName()}'.'{$name}' column is empty."
            );
        }

        if ($autoCommit) {
            $this->getConnection()->addColumn($this->getTableName(), $name, $definition);
        } else {
            $this->addQueryToCommit(
                self::COMMIT_KEY_ADD_COLUMN,
                'ADD COLUMN %s %s',
                array($name),
                $definition
            );
        }

        $addIndex && $this->addIndex($name, $autoCommit);

        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @param string|null $default
     * @param string|null $after
     * @param bool $autoCommit
     * @return $this
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Exception
     */
    public function changeColumn($name, $type, $default = null, $after = null, $autoCommit = true)
    {
        if (!$this->isColumnExists($name)) {
            throw new Ess_M2ePro_Model_Exception_Setup(
                "Column '{$name}' does not exist in '{$this->getTableName()}' table."
            );
        }

        $definition = $this->buildColumnDefinition($type, $default, $after, $autoCommit);

        if (empty($definition)) {
            throw new Ess_M2ePro_Model_Exception_Setup(
                "Definition for '{$this->getTableName()}'.'{$name}' column is empty."
            );
        }

        if ($autoCommit) {
            $this->getConnection()->modifyColumn($this->getTableName(), $name, $definition);
        } else {
            $this->addQueryToCommit(
                self::COMMIT_KEY_CHANGE_COLUMN,
                'MODIFY COLUMN %s %s',
                array($name),
                $definition
            );
        }

        return $this;
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $type
     * @param string|null $default
     * @param string|null $after
     * @param bool $autoCommit
     * @return $this
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Exception
     */
    public function changeAndRenameColumn($from, $to, $type, $default = null, $after = null, $autoCommit = true)
    {
        if (!$this->isColumnExists($from) && $this->isColumnExists($to)) {
            return $this;
        }

        if ($this->isColumnExists($from) && $this->isColumnExists($to)) {
            throw new Ess_M2ePro_Model_Exception_Setup(
                "Column '{$from}' cannot be changed to '{$to}', because last one
                 already exists in '{$this->getTableName()}' table."
            );
        }

        if (!$this->isColumnExists($from) && !$this->isColumnExists($to)) {
            throw new Ess_M2ePro_Model_Exception_Setup(
                "Column '{$from}' cannot be changed, because
                 does not exist in '{$this->getTableName()}' table."
            );
        }

        $definition = $this->buildColumnDefinition($type, $default, $after, $autoCommit);

        if (empty($definition)) {
            throw new Ess_M2ePro_Model_Exception_Setup(
                "Definition for '{$this->getTableName()}'.'{$to}' column is empty."
            );
        }

        if ($autoCommit) {
            $this->getConnection()->changeColumn($this->getTableName(), $from, $to, $definition);
        } else {
            $this->addQueryToCommit(
                self::COMMIT_KEY_CHANGE_COLUMN,
                'CHANGE COLUMN %s %s %s',
                array($from, $to),
                $definition
            );
        }

        $this->renameIndex($from, $to, $autoCommit);

        return $this;
    }

    /**
     * @param string $name
     * @param bool $dropIndex
     * @param bool $autoCommit
     * @return $this
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function dropColumn($name, $dropIndex = true, $autoCommit = true)
    {
        if (!$this->isColumnExists($name)) {
            return $this;
        }

        if ($autoCommit) {
            $this->getConnection()->dropColumn($this->getTableName(), $name);
        } else {
            $this->addQueryToCommit(self::COMMIT_KEY_DROP_COLUMN, 'DROP COLUMN %s', array($name));
        }

        $dropIndex && $this->dropIndex($name, $autoCommit);

        return $this;
    }

    //########################################

    /**
     * @param string $name
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function isIndexExists($name)
    {
        $indexList = $this->getConnection()->getIndexList($this->getTableName());

        return isset($indexList[strtoupper($name)]);
    }

    /**
     * @param string $from
     * @param string $to
     * @param bool $autoCommit
     * @return $this
     */
    public function renameIndex($from, $to, $autoCommit = true)
    {
        if (!$this->isIndexExists($from)) {
            return $this;
        }

        return $this->dropIndex($from, $autoCommit)->addIndex($to, $autoCommit);
    }

    // ---------------------------------------

    /**
     * @param string $name
     * @param bool $autoCommit
     * @return $this
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function addIndex($name, $autoCommit = true)
    {
        if ($this->isIndexExists($name)) {
            return $this;
        }

        if ($autoCommit) {
            $this->getConnection()->addKey($this->getTableName(), $name, $name);
        } else {
            $this->addQueryToCommit(self::COMMIT_KEY_ADD_INDEX, 'ADD INDEX %s (%s)', array($name, $name));
        }

        return $this;
    }

    /**
     * @param string $name
     * @param bool $autoCommit
     * @return $this
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function dropIndex($name, $autoCommit = true)
    {
        if (!$this->isIndexExists($name)) {
            return $this;
        }

        if ($autoCommit) {
            $this->getConnection()->dropKey($this->getTableName(), $name);
        } else {
            $this->addQueryToCommit(self::COMMIT_KEY_DROP_INDEX, 'DROP KEY %s', array($name));
        }

        return $this;
    }

    //########################################

    protected function buildColumnDefinition($type, $default = null, $after = null, $autoCommit = true)
    {
        $definition = $type;

        if ($default !== null) {
            if ($default === 'NULL') {
                $definition .= ' DEFAULT NULL';
            } else {
                $definition .= ' DEFAULT ' . $this->getConnection()->quote($default);
            }
        }

        if (!empty($after)) {
            if ($autoCommit) {
                if (!$this->isColumnExists($after)) {
                    throw new Ess_M2ePro_Model_Exception_Setup(
                        "After column '{$after}' does not exist in '{$this->getTableName()}' table."
                    );
                }
            } else {
                $this->_columnsForCheckBeforeCommit[] = $after;
            }

            $definition .= ' AFTER ' . $this->getConnection()->quoteIdentifier($after);
        }

        return $definition;
    }

    protected function buildColumnDefinitionByName($name)
    {
        if (!$this->isColumnExists($name)) {
            throw new Ess_M2ePro_Model_Exception_Setup(
                "Base column '{$name}' does not exist in '{$this->getTableName()}' table."
            );
        }

        $tableColumns = $this->getConnection()->describeTable($this->getTableName());

        if (!isset($tableColumns[$name])) {
            throw new Ess_M2ePro_Model_Exception_Setup(
                "Describe for column '{$name}' does not exist in '{$this->getTableName()}' table."
            );
        }

        $columnInfo = $tableColumns[$name];

        // In some cases Magento does not cut "UNSIGNED" modifier out of column data type info
        $type = trim(str_replace('UNSIGNED', '', strtoupper($columnInfo['DATA_TYPE'])));
        if (is_numeric($columnInfo['LENGTH']) && $columnInfo['LENGTH'] > 0) {
            $type .= '(' . $columnInfo['LENGTH'] . ')';
        } elseif (is_numeric($columnInfo['PRECISION']) && is_numeric($columnInfo['SCALE'])) {
            $type .= sprintf('(%d,%d)', $columnInfo['PRECISION'], $columnInfo['SCALE']);
        }

        $default = '';
        if ($columnInfo['DEFAULT'] !== null) {
            $default = $this->getConnection()->quoteInto('DEFAULT ?', $columnInfo['DEFAULT']);
        } elseif ($columnInfo['NULLABLE']) {
            $default = 'DEFAULT NULL';
        }

        return sprintf(
            '%s %s %s %s %s',
            $type,
            $columnInfo['UNSIGNED'] ? 'UNSIGNED' : '',
            !$columnInfo['NULLABLE'] ? 'NOT NULL' : '',
            $default,
            $columnInfo['IDENTITY'] ? 'AUTO_INCREMENT' : ''
        );
    }

    //########################################

    protected function addQueryToCommit($key, $queryPattern, array $columns, $definition = null)
    {
        foreach ($columns as &$column) {
            $column = $this->getConnection()->quoteIdentifier($column);
        }

        $queryArgs = $definition !== null ? array_merge($columns, array($definition)) : $columns;
        $tempQuery = vsprintf($queryPattern, $queryArgs);

        if (isset($this->_sqlForCommit[$key]) && in_array($tempQuery, $this->_sqlForCommit[$key])) {
            return $this;
        }

        $this->_sqlForCommit[$key][] = $tempQuery;

        return $this;
    }

    protected function checkColumnsBeforeCommit()
    {
        foreach ($this->_columnsForCheckBeforeCommit as $index => $columnForCheck) {
            if ($this->isColumnExists($columnForCheck)) {
                unset($this->_columnsForCheckBeforeCommit[$index]);
                continue;
            }

            foreach ($this->_sqlForCommit as $key => $sqlData) {
                if (!is_array($sqlData) || in_array(
                        $key,
                        array(
                            self::COMMIT_KEY_ADD_INDEX,
                            self::COMMIT_KEY_DROP_INDEX,
                            self::COMMIT_KEY_DROP_COLUMN
                        )
                    )
                ) {
                    continue;
                }

                $pattern = '/COLUMN\s(`' . $columnForCheck . '`|`[^`]+`\s`' . $columnForCheck . '`)/';
                $tempSql = implode(', ', $sqlData);

                if (preg_match($pattern, $tempSql)) {
                    unset($this->_columnsForCheckBeforeCommit[$index]);
                    break;
                }
            }
        }

        return empty($this->_columnsForCheckBeforeCommit);
    }

    /**
     * @return $this
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function commit()
    {
        if (empty($this->_sqlForCommit)) {
            return $this;
        }

        $order = array(
            self::COMMIT_KEY_ADD_COLUMN,
            self::COMMIT_KEY_CHANGE_COLUMN,
            self::COMMIT_KEY_DROP_COLUMN,
            self::COMMIT_KEY_ADD_INDEX,
            self::COMMIT_KEY_DROP_INDEX
        );

        $tempSql = '';
        $sep = '';

        foreach ($order as $orderKey) {
            foreach ($this->_sqlForCommit as $key => $sqlData) {
                if ($orderKey != $key || !is_array($sqlData)) {
                    continue;
                }

                $tempSql .= $sep . implode(', ', $sqlData);
                $sep = ', ';
            }
        }

        $resultSql = sprintf(
            'ALTER TABLE %s %s',
            $this->getConnection()->quoteIdentifier($this->getTableName()),
            $tempSql
        );

        if (!$this->checkColumnsBeforeCommit()) {
            $this->_sqlForCommit = array();
            $failedColumns = implode("', '", $this->_columnsForCheckBeforeCommit);

            throw new Ess_M2ePro_Model_Exception_Setup(
                "Commit for '{$this->getTableName()}' table is failed
                because '{$failedColumns}' columns does not exist."
            );
        }

        $this->runQuery($resultSql);
        $this->_sqlForCommit = array();

        return $this;
    }

    /**
     * @return bool
     * @throws \Ess\M2ePro\Model\Exception\Logic
     * @throws \Zend_Db_Statement_Exception
     */
    protected function isNeedChangeRowFormat()
    {
        if ($this->_checkedTableRowFormat) {
            return false;
        }

        $databaseName = Mage::helper('M2ePro/Magento')->getDatabaseName();

        $result = array_change_key_case(
            $this->getConnection()->select()
                ->from('tables', array('row_format'), 'information_schema')
                ->where('table_schema =?', $databaseName)
                ->where('table_name =?', $this->_tableName)->query()->fetch()
        );

        $this->_checkedTableRowFormat = true;

        return strtolower($result['row_format']) != 'dynamic';
    }

    /**
     * @throws \Zend_Db_Statement_Exception
     */
    protected function changeRowFormat()
    {
        $sql = sprintf('ALTER TABLE %s ROW_FORMAT = DYNAMIC', $this->_tableName);
        $this->getConnection()->query($sql)->execute();
    }

    //########################################
}
