<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
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

    //####################################

    public function addColumn($nick, $type, $default = NULL, $after = NULL, $addIndex = false, $autoCommit = true)
    {
        if (!$this->isTableExists()) {
            return $this;
        }

        $definition = $this->buildColumnDefinition($type, $default, $after, $autoCommit);

        if ($this->isColumnExists($nick) === false && !empty($definition)) {
            if (!$autoCommit) {
                $this->addQueryToCommit(self::COMMIT_KEY_ADD_COLUMN,
                                        'ADD COLUMN %s %s', array($nick), $definition);
            } else {
                $this->getConnection()->addColumn($this->getTableName(), $nick, $definition);
            }

            $addIndex && $this->addIndex($nick, $autoCommit);
        }

        return $this;
    }

    public function changeColumn($nick, $type, $default = NULL, $after = NULL, $autoCommit = true)
    {
        if (!$this->isTableExists()) {
            return $this;
        }

        $definition = $this->buildColumnDefinition($type, $default, $after, $autoCommit);

        if ($this->isColumnExists($nick) !== false && !empty($definition)) {
            if (!$autoCommit) {
                $this->addQueryToCommit(self::COMMIT_KEY_CHANGE_COLUMN,
                                        'MODIFY COLUMN %s %s', array($nick), $definition);
            } else {
                $this->getConnection()->modifyColumn($this->getTableName(), $nick ,$definition);
            }
        }

        return $this;
    }

    // ----------------------------------

    public function renameColumn($from, $to, $renameIndex = true, $autoCommit = true)
    {
        if (!$this->isTableExists()) {
            return $this;
        }

        $definition = $this->getColumnDefinitionFromDescribe($from);

        if ($this->isColumnExists($from) !== false && $this->isColumnExists($to) === false && !empty($definition)) {
            if (!$autoCommit) {
                $this->addQueryToCommit(self::COMMIT_KEY_CHANGE_COLUMN,
                                        'CHANGE COLUMN %s %s %s', array($from, $to), $definition);
            } else {
                $this->getConnection()->changeColumn($this->getTableName(), $from, $to, $definition);
            }

            if ($renameIndex) {
                $this->dropIndex($from, $autoCommit)
                     ->addIndex($to, $autoCommit);
            }
        }

        return $this;
    }

    public function dropColumn($nick, $dropIndex = true, $autoCommit = true)
    {
        if (!$this->isTableExists()) {
            return $this;
        }

        if ($this->isColumnExists($nick) !== false) {
            if (!$autoCommit) {
                $this->addQueryToCommit(self::COMMIT_KEY_DROP_COLUMN, 'DROP COLUMN %s', array($nick));
            } else {
                $this->getConnection()->dropColumn($this->getTableName(), $nick);
            }

            $dropIndex && $this->dropIndex($nick, $autoCommit);
        }

        return $this;
    }

    //####################################

    public function addIndex($nick, $autoCommit = true)
    {
        if (!$this->isTableExists()) {
            return $this;
        }

        if (!$this->isIndexExists($nick)) {
            if (!$autoCommit) {
                $this->addQueryToCommit(self::COMMIT_KEY_ADD_INDEX, 'ADD INDEX %s (%s)', array($nick, $nick));
            } else {
                $this->getConnection()->addKey($this->getTableName(), $nick, $nick);
            }
        }

        return $this;
    }

    public function dropIndex($nick, $autoCommit = true)
    {
        if (!$this->isTableExists()) {
            return $this;
        }

        if ($this->isIndexExists($nick)) {
            if (!$autoCommit) {
                $this->addQueryToCommit(self::COMMIT_KEY_DROP_INDEX, 'DROP KEY %s', array($nick));
            } else {
                $this->getConnection()->dropKey($this->getTableName(), $nick);
            }
        }

        return $this;
    }

    //####################################

    public function truncate()
    {
        if (!$this->isTableExists()) {
            return $this;
        }

        $this->getConnection()->truncate($this->getTableName());
        return $this;
    }

    //####################################

    public function commit()
    {
        if (empty($this->sqlForCommit) || !$this->isTableExists()) {
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

        if ($this->checkColumnsBeforeCommit()) {
            $this->runQuery($resultSql);
        }

        $this->sqlForCommit = array();
        return $this;
    }

    //####################################

    private function buildColumnDefinition($type, $default = NULL, $after = NULL, $autoCommit = true)
    {
        $definition = $type;

        if (!is_null($default)) {
            if ($default == 'NULL') {
                $definition .= ' DEFAULT NULL';
            } else {
                $definition .= ' DEFAULT ' . $this->getConnection()->quote($default);
            }
        }

        if (!empty($after) && !$autoCommit) {
            $this->columnsForCheckBeforeCommit[] = $after;
            $definition .= ' AFTER ' . $this->getConnection()->quoteIdentifier($after);
        } elseif (!empty($after) && $this->isColumnExists($after)) {
            $definition .= ' AFTER ' . $this->getConnection()->quoteIdentifier($after);
        }

        return $definition;
    }

    // ----------------------------------

    private function getColumnDefinitionFromDescribe($nick)
    {
        $columnDefinition = '';

        if ($this->isColumnExists($nick) !== false) {
            $tableColumns = $this->getConnection()->describeTable($this->getTableName());

            if (isset($tableColumns[$nick])) {
                $columnInfo = $tableColumns[$nick];
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

                $columnDefinition = sprintf('%s %s %s %s %s',
                    $type,
                    $columnInfo['UNSIGNED'] ? 'UNSIGNED' : '',
                    $columnInfo['NULLABLE'] ? 'NULL' : 'NOT NULL',
                    $default,
                    $columnInfo['IDENTITY'] ? 'AUTO_INCREMENT' : ''
                );
            }
        }

        return $columnDefinition;
    }

    //####################################

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

    // ----------------------------------

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

    //####################################

    private function isColumnExists($nick)
    {
        return $this->getConnection()->tableColumnExists($this->getTableName(), $nick);
    }

    private function isIndexExists($nick)
    {
        $indexList = $this->getConnection()->getIndexList($this->getTableName());
        return isset($indexList[strtoupper($nick)]);
    }

    //####################################
}