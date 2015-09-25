<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Upgrade_Modifier_Config extends Ess_M2ePro_Model_Upgrade_Modifier_Abstract
{
    //####################################

    public function insert($group, $key, $value = NULL, $notice = NULL)
    {
        if(!$this->isTableExists()) {
            throw new Zend_Db_Exception("Table is not exists");
        }

        $preparedData = array(
            'group' => $group,
            'key' => $key,
        );

        !is_null($value) && $preparedData['value'] = $value;
        !is_null($notice) && $preparedData['notice'] = $notice;
        $preparedData['update_date'] = $this->getCurrentDateTime();
        $preparedData['create_date'] = $this->getCurrentDateTime();

        return $this->getConnection()->insert($this->getTableName(), $preparedData);
    }

    // ----------------------------------

    public function updateGroup($value, $where)
    {
        return $this->update('group', $value, $where);
    }

    public function updateKey($value, $where)
    {
        return $this->update('key', $value, $where);
    }

    public function updateValue($value, $where)
    {
        return $this->update('value', $value, $where);
    }

    private function update($field, $value, $where)
    {
        if(!$this->isTableExists()) {
            throw new Zend_Db_Exception("Table is not exists");
        }

        $preparedData = array(
            $field => $value,
            'update_date' => $this->getCurrentDateTime()
        );

        return $this->getConnection()->update($this->getTableName(), $preparedData, $where);
    }

    // ----------------------------------

    public function delete($group, $key = NULL)
    {
        if(!$this->isTableExists()) {
            throw new Zend_Db_Exception("Table is not exists");
        }

        $where = array(
            '`group` = ?' => $group
        );

        if (!is_null($key)) {
            $where['`key` = ?'] = $key;
        }

        return $this->getConnection()->delete($this->getTableName(), $where);
    }

    //####################################

    public function isTableExists()
    {
        $tableName = $this->getTableName();

        if (!empty($tableName) &&
            in_array($tableName, $this->getInstaller()->getTablesObject()->getAllHistoryConfigEntities())) {
            return true;
        }

        return false;
    }

    public function isExists($group, $key = NULL)
    {
        if(!$this->isTableExists()) {
            throw new Zend_Db_Exception("Table is not exists");
        }

        $query = $this->getConnection()->select()
                      ->from($this->getTableName())
                      ->where($this->getConnection()->quoteInto('`group` = ?', $group));

        if (!is_null($key)) {
            $query->where($this->getConnection()->quoteInto('`key` = ?', $key));
        }

        $result = $this->getConnection()->fetchOne($query);
        return (bool) $result;
    }

    //####################################

    private function getCurrentDateTime()
    {
        return date('Y-m-d H:i:s', gmdate('U'));
    }

    //####################################
}