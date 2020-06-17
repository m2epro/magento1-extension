<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m11_LogsImprovements extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    const TASK_DELETE            = 'delete';
    const TASK_ACTION_ID         = 'action_id';
    const TASK_INDEX             = 'index';
    const TASK_COLUMNS           = 'columns';

    const LOGS_LIMIT_COUNT = 100000;

    //########################################

    public function execute()
    {
        foreach ($this->getProcessSubjects() as $subject) {
            foreach ($subject['tasks'] as $task) {
                $this->processTask($task, $subject['params']);
            }
        }

        $this->removeListingOtherLog();

        $this->_installer->getTableModifier('ebay_account_pickup_store_log')
            ->changeColumn('action_id', 'INT(11) UNSIGNED NOT NULL');

        $this->_installer->getTableModifier('listing_log')
            ->changeColumn('action_id', 'INT(11) UNSIGNED NOT NULL');

        $this->_installer->getTableModifier('synchronization_log')->addIndex('create_date');
        $this->_installer->getTableModifier('ebay_account_pickup_store_log')->addIndex('create_date');

        $this->_installer->getMainConfigModifier()->insert(
            '/logs/grouped/', 'max_records_count', self::LOGS_LIMIT_COUNT
        );
    }

    //----------------------------------------

    protected function getProcessSubjects()
    {
        return array(
            array(
                'params' => array(
                    'table'           => 'listing_log',
                    'config'          => '/logs/listings/',
                    'entity_table'    => 'listing',
                    'entity_id_field' => 'listing_id'
                ),
                'tasks' => array(
                    self::TASK_DELETE,
                    self::TASK_ACTION_ID,
                    self::TASK_COLUMNS,
                    self::TASK_INDEX
                )
            ),
            array(
                'params' => array(
                    'table'           => 'order_log',
                    'entity_table'    => 'order',
                    'entity_id_field' => 'order_id'
                ),
                'tasks' => array(
                    self::TASK_DELETE,
                    self::TASK_COLUMNS,
                    self::TASK_INDEX
                )
            )
        );
    }

    protected function processTask($task, $params)
    {
        switch ($task) {
            case self::TASK_DELETE:
                $this->processDelete($params['table']);
                break;
            case self::TASK_ACTION_ID:
                $this->processActionId($params['table'], $params['config']);
                break;
            case self::TASK_INDEX:
                $this->processIndex($params['table']);
                break;
            case self::TASK_COLUMNS:
                $this->processColumns($params['table'], $params['entity_table'], $params['entity_id_field']);
                break;
        }
    }

    protected function processDelete($tableName)
    {
        $table = $this->_installer->getFullTableName($tableName);
        $tempTable = $this->_installer->getFullTableName($tableName . '_temp');
        if (!$this->_installer->tableExists($table) && $this->_installer->tableExists($tempTable)) {
            return $this->_installer->getTablesObject()->renameTable($tempTable, $table);
        }

        $select = $this->_installer->getConnection()->select()->from(
            $table,
            array(new \Zend_Db_Expr('COUNT(*)'))
        );

        $logsCount = $this->_installer->getConnection()->fetchOne($select);

        if ($logsCount <= self::LOGS_LIMIT_COUNT) {
            return;
        }

        $limit = self::LOGS_LIMIT_COUNT;

        $this->_installer->getConnection()->exec("CREATE TABLE IF NOT EXISTS `{$table}_temp` LIKE `{$table}`");
        $this->_installer->getConnection()->exec("INSERT INTO `{$table}_temp` (
                                        SELECT * FROM `{$table}` ORDER BY `id` DESC LIMIT {$limit}
                                     )");
        $this->_installer->getConnection()->exec("DROP TABLE `{$table}`");

        $this->_installer->getTablesObject()->renameTable($table . '_temp', $table);
    }

    protected function processActionId($tableName, $configName)
    {
        $noActionIdCondition = new \Zend_Db_Expr('(`action_id` IS NULL) OR (`action_id` = 0)');

        $select = $this->_installer->getConnection()->select()
            ->from(
                $this->_installer->getFullTableName($tableName),
                array(new \Zend_Db_Expr('MIN(`id`)'))
            )
            ->where($noActionIdCondition);

        $minLogIdWithNoActionId = $this->_installer->getConnection()->fetchOne($select);

        if ($minLogIdWithNoActionId === null) {
            return;
        }

        $lastActionId = $this->getLastActionId($configName);

        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName($tableName),
            array(
                'action_id' => new \Zend_Db_Expr("`id` - {$minLogIdWithNoActionId} + {$lastActionId}")
            ),
            $noActionIdCondition
        );

        $this->updateLastActionId($tableName, $configName);
    }

    protected function getLastActionId($configName)
    {
        $config = $this->_installer->getMainConfigModifier()->getEntity(
            $configName, 'last_action_id'
        );

        return $config->getValue() + 100;
    }

    protected function updateLastActionId($tableName, $configName)
    {
        $select = $this->_installer->getConnection()->select()->from(
            $this->_installer->getFullTableName($tableName),
            array(new \Zend_Db_Expr('MAX(`action_id`)'))
        );

        $maxActionId = $this->_installer->getConnection()->fetchOne($select);

        $config = $this->_installer->getMainConfigModifier()->getEntity(
            $configName, 'last_action_id'
        );

        $config->updateValue((int)$maxActionId + 100);
    }

    protected function processIndex($tableName)
    {
        $this->_installer->getTableModifier($tableName)
            ->addIndex('create_date', false)
            ->addIndex('account_id', false)
            ->addIndex('marketplace_id', false)
            ->commit();
    }

    protected function processColumns($tableName, $entityTableName, $entityIdField)
    {
        $this->_installer->getTableModifier($tableName)
            ->addColumn('account_id', 'INT(11) UNSIGNED NOT NULL', null, 'id', true, false)
            ->addColumn('marketplace_id', 'INT(11) UNSIGNED NOT NULL', null, 'account_id', true, false)
            ->changeColumn($entityIdField, 'INT(11) UNSIGNED NOT NULL', null, null, false)
            ->commit();

        $table = $this->_installer->getFullTableName($tableName);
        $entityTable = $this->_installer->getFullTableName($entityTableName);

        $this->_installer->getConnection()->exec(<<<SQL
UPDATE `{$table}` `log_table`
  INNER JOIN `{$entityTable}` `entity_table` ON `log_table`.`{$entityIdField}` = `entity_table`.`id`
SET
  `log_table`.`account_id` = `entity_table`.`account_id`,
  `log_table`.`marketplace_id` = `entity_table`.`marketplace_id`;
SQL
        );

        $this->_installer->getConnection()->delete(
            $table, array(
                'account_id = ?'     => 0,
                'marketplace_id = ?' => 0
            )
        );
    }

    protected function removeListingOtherLog()
    {
        $this->_installer->run("DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_listing_other_log')}`");

        $this->_installer->getMainConfigModifier()->delete('/logs/clearing/other_listings/');
        $this->_installer->getMainConfigModifier()->delete('/logs/other_listings/');
    }

    //########################################
}