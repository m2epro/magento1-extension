<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_Modifier_Table extends Ess_M2ePro_Model_Upgrade_Modifier_Abstract
{
    const COMMIT_KEY_ADD_COLUMN    = 'add_column';
    const COMMIT_KEY_DROP_COLUMN   = 'drop_column';
    const COMMIT_KEY_CHANGE_COLUMN = 'change_column';
    const COMMIT_KEY_ADD_INDEX     = 'add_index';
    const COMMIT_KEY_DROP_INDEX    = 'drop_index';

    protected $sqlForCommit = array();
    protected $columnsForCheckBeforeCommit = array();

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
            $this->addQueryToCommit(self::COMMIT_KEY_CHANGE_COLUMN,
                                    'CHANGE COLUMN %s %s %s', array($from, $to), $definition);
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
    public function addColumn($name, $type, $default = NULL, $after = NULL, $addIndex = false, $autoCommit = true)
    {
        if ($this->isColumnExists($name)) {
            return $this;
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
            $this->addQueryToCommit(self::COMMIT_KEY_ADD_COLUMN,
                                   'ADD COLUMN %s %s', array($name), $definition);
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
    public function changeColumn($name, $type, $default = NULL, $after = NULL, $autoCommit = true)
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
            $this->addQueryToCommit(self::COMMIT_KEY_CHANGE_COLUMN,
                                    'MODIFY COLUMN %s %s', array($name), $definition);
        }

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
        return $this->dropIndex($from, $autoCommit)->addIndex($to, $autoCommit);
    }

    // ---------------------------------------

    /**
     * @param string name
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

    private function buildColumnDefinition($type, $default = NULL, $after = NULL, $autoCommit = true)
    {
        $definition = $type;

        if (!is_null($default)) {

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
                $this->columnsForCheckBeforeCommit[] = $after;
            }

            $definition .= ' AFTER ' . $this->getConnection()->quoteIdentifier($after);
        }

        return $definition;
    }

    private function buildColumnDefinitionByName($name)
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

        $type = $columnInfo['DATA_TYPE'];
        if (is_numeric($columnInfo['LENGTH']) && $columnInfo['LENGTH'] > 0) {
            $type .= '('.$columnInfo['LENGTH'].')';
        } elseif (is_numeric($columnInfo['PRECISION']) && is_numeric($columnInfo['SCALE'])) {
            $type .= sprintf('(%d,%d)', $columnInfo['PRECISION'], $columnInfo['SCALE']);
        }

        $default = '';
        if ($columnInfo['DEFAULT'] !== false) {
            $this->getConnection()->quoteInto('DEFAULT ?', $columnInfo['DEFAULT']);
        }

        return sprintf('%s %s %s %s %s',
            $type,
            $columnInfo['UNSIGNED'] ? 'UNSIGNED' : '',
            $columnInfo['NULLABLE'] ? 'NULL' : 'NOT NULL',
            $default,
            $columnInfo['IDENTITY'] ? 'AUTO_INCREMENT' : ''
        );
    }

    //########################################

    private function addQueryToCommit($key, $queryPattern, array $columns, $definition = NULL)
    {
        foreach ($columns as &$column) {
            $column = $this->getConnection()->quoteIdentifier($column);
        }

        $queryArgs = !is_null($definition) ? array_merge($columns, array($definition)) : $columns;
        $tempQuery = vsprintf($queryPattern, $queryArgs);

        if (isset($this->sqlForCommit[$key]) && in_array($tempQuery, $this->sqlForCommit[$key])) {
            return $this;
        }

        $this->sqlForCommit[$key][] = $tempQuery;
        return $this;
    }

    private function checkColumnsBeforeCommit()
    {
        foreach ($this->columnsForCheckBeforeCommit as $index => $columnForCheck) {

            if ($this->isColumnExists($columnForCheck)) {
                unset($this->columnsForCheckBeforeCommit[$index]);
                continue;
            }

            foreach ($this->sqlForCommit as $key => $sqlData) {
                if (!is_array($sqlData) || in_array($key, array(self::COMMIT_KEY_ADD_INDEX,
                                                                self::COMMIT_KEY_DROP_INDEX,
                                                                self::COMMIT_KEY_DROP_COLUMN))
                ) {
                    continue;
                }

                $pattern = '/COLUMN\s(`'.$columnForCheck.'`|`[^`]+`\s`'.$columnForCheck.'`)/';
                $tempSql = implode(', ', $sqlData);

                if (preg_match($pattern, $tempSql)) {
                    unset($this->columnsForCheckBeforeCommit[$index]);
                    break;
                }
            }
        }

        return empty($this->columnsForCheckBeforeCommit);
    }

    /**
     * @return $this
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function commit()
    {
        if (empty($this->sqlForCommit)) {
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

            foreach ($this->sqlForCommit as $key => $sqlData) {

                if ($orderKey != $key || !is_array($sqlData)) {
                    continue;
                }

                $tempSql .= $sep . implode(', ', $sqlData);
                $sep = ', ';
            }
        }

        $resultSql = sprintf('ALTER TABLE %s %s',
            $this->getConnection()->quoteIdentifier($this->getTableName()),
            $tempSql
        );

        if (!$this->checkColumnsBeforeCommit()) {
            $this->sqlForCommit = array();
            $failedColumns = implode("', '", $this->columnsForCheckBeforeCommit);

            throw new Ess_M2ePro_Model_Exception_Setup(
                "Commit for '{$this->getTableName()}' table is failed
                because '{$failedColumns}' columns does not exist."
            );
        }

        $this->runQuery($resultSql);
        $this->sqlForCommit = array();
        return $this;
    }

    //########################################
}