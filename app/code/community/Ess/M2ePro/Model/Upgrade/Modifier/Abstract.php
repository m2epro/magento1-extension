<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Upgrade_Modifier_Abstract
{
    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    protected $installer = NULL;

    /** @var Varien_Db_Adapter_Pdo_Mysql */
    protected $connection = NULL;

    protected $tableName = NULL;
    protected $queryLog = array();

    //####################################

    public function setInstaller(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer)
    {
        $this->installer = $installer;
        return $this;
    }

    public function getInstaller()
    {
        if (is_null($this->installer)) {
            throw new Zend_Db_Exception("Installer is not exists.");
        }

        return $this->installer;
    }

    // ----------------------------------

    public function setConnection(Varien_Db_Adapter_Pdo_Mysql $connection)
    {
        $this->connection = $connection;
        return $this;
    }

    public function getConnection()
    {
        if (is_null($this->connection)) {
            throw new Zend_Db_Exception("Connection is not exists.");
        }

        return $this->connection;
    }

    // ----------------------------------

    public function setTableName($tableName)
    {
        $result = $this->getConnection()->showTableStatus($tableName);

        if ($result !== false) {
            $this->tableName = $this->getInstaller()->getTable($tableName);
        }

        return $this;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function isTableExists()
    {
        $tableName = $this->getTableName();
        return !empty($tableName);
    }

    //####################################

    protected function runQuery($query)
    {
        $this->setQueryLog($query);
        $this->getConnection()->query($query);
        $this->getConnection()->resetDdlCache();
        return $this;
    }

    //####################################

    public function setQueryLog($query)
    {
        $this->queryLog[] = $query;
        return $this;
    }

    public function getQueryLog()
    {
        return $this->queryLog;
    }

    //####################################
}