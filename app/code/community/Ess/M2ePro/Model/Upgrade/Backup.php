<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_Backup
{
    //########################################

    const TABLE_PREFIX = '__b';

    // max MySQL lenth (64) - backup prefix (m2epro__b_65016_)
    const TABLE_IDENTIFIER_MAX_LEN = 46;

    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    private $installer;

    /** @var array */
    private $tablesList;

    //########################################

    public function __construct(array $arguments = array())
    {
        list($this->installer, $this->tablesList) = $arguments;
    }

    //########################################

    public function isExists()
    {
        foreach ($this->tablesList as $table) {
            if (!$this->getConnection()->isTableExists($this->getBackupTableName($table))) {
                return false;
            }
        }

        return true;
    }

    //########################################

    public function create()
    {
        foreach ($this->tablesList as $table) {

            $this->prepareColumns($table);

            if ($this->getConnection()->isTableExists($this->getBackupTableName($table))) {
                $this->getConnection()->dropTable($this->getBackupTableName($table));
            }

            $backupTable = $this->getConnection()->createTableByDdl(
                $this->getOriginalTableName($table), $this->getBackupTableName($table)
            );
            $backupTable->setComment(sprintf(
                'Based on %s. From [%s] to [%s].',
                $this->getOriginalTableName($table), $this->installer->versionFrom, $this->installer->versionTo
            ));
            $this->getConnection()->createTable($backupTable);

            $select = $this->getConnection()->select()->from($this->getOriginalTableName($table));
            $this->getConnection()->query(
                $this->getConnection()->insertFromSelect($select, $this->getBackupTableName($table))
            );
        }
    }

    public function remove()
    {
        foreach ($this->tablesList as $table) {
            if ($this->getConnection()->isTableExists($this->getBackupTableName($table))) {
                $this->getConnection()->dropTable($this->getBackupTableName($table));
            }
        }
    }

    public function rollback()
    {
        if (!$this->isExists()) {
            throw new Ess_M2ePro_Model_Exception_Setup('Unable to rollback. Backup is not exists.');
        }

        foreach ($this->tablesList as $table) {

            if ($this->getConnection()->isTableExists($this->getOriginalTableName($table))) {
                $this->getConnection()->dropTable($this->getOriginalTableName($table));
            }

            $originalTable = $this->getConnection()->createTableByDdl(
                $this->getBackupTableName($table), $this->getOriginalTableName($table)
            );
            $this->getConnection()->createTable($originalTable);

            $select = $this->getConnection()->select()->from($this->getBackupTableName($table));
            $this->getConnection()->query(
                $this->getConnection()->insertFromSelect($select, $this->getOriginalTableName($table))
            );
        }
    }

    //########################################

    private function prepareColumns($table)
    {
        $tableInfo = $this->getConnection()->describeTable(
            $this->installer->getTablesObject()->getFullName($table)
        );

        $tableModifier = $this->installer->getTableModifier($table);

        foreach ($tableInfo as $columnTitle => $columnInfo) {

            $this->prepareFloatUnsignedColumns($tableModifier, $columnTitle, $columnInfo);
            $this->prepareVarcharColumns($tableModifier, $columnTitle, $columnInfo);
        }

        $tableModifier->commit();
    }

    /**
     * @param $tableModifier Ess_M2ePro_Model_Upgrade_Modifier_Table
     * @param $columnTitle string
     * @param $columnInfo array
     *
     * convert FLOAT UNSIGNED columns to FLOAT because of zend framework bug in ->createTableByDdl method,
     * that does not support 'FLOAT UNSIGNED' column type
     */
    private function prepareFloatUnsignedColumns(Ess_M2ePro_Model_Upgrade_Modifier_Table $tableModifier,
                                                 $columnTitle, array $columnInfo)
    {
        if (strtolower($columnInfo['DATA_TYPE']) !== 'float unsigned') {
            return;
        }

        $columnType = 'FLOAT';
        if (isset($columnInfo['NULLABLE']) && !$columnInfo['NULLABLE']) {
            $columnType .= ' NOT NULL';
        }

        $tableModifier->changeColumn($columnTitle, $columnType, $columnInfo['DEFAULT'], NULL, false);
    }

    /**
     * @param $tableModifier Ess_M2ePro_Model_Upgrade_Modifier_Table
     * @param $columnTitle string
     * @param $columnInfo array
     *
     * convert VARCHAR(256-500) to VARCHAR(255) because ->createTableByDdl method will handle this column
     * as TEXT. Due to the incorrect length > 255
     */
    private function prepareVarcharColumns(Ess_M2ePro_Model_Upgrade_Modifier_Table $tableModifier,
                                           $columnTitle, array $columnInfo)
    {
        if (strtolower($columnInfo['DATA_TYPE']) !== 'varchar') {
            return;
        }

        if ($columnInfo['LENGTH'] > 255 && $columnInfo['LENGTH'] <= 500) {

            $columnType = 'varchar(255)';
            if (isset($columnInfo['NULLABLE']) && !$columnInfo['NULLABLE']) {
                $columnType .= ' NOT NULL';
            }

            $tableModifier->changeColumn($columnTitle, $columnType, $columnInfo['DEFAULT'], NULL, false);
        }
    }

    //########################################

    private function getConnection()
    {
        return $this->installer->getConnection();
    }

    // ---------------------------------------

    public function getOriginalTableName($table)
    {
        return $this->installer->getTablesObject()->getFullName($table);
    }

    public function getBackupTableName($table)
    {
        $prefix = 'm2epro' . self::TABLE_PREFIX. '_' . str_replace('.', '', $this->installer->versionTo) . '_';

        if (strlen($table) > self::TABLE_IDENTIFIER_MAX_LEN) {
            $table = sha1($table);
        }

        return $prefix . $table;
    }

    //########################################
}