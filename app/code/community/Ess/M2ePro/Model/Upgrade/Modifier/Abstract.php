<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_Modifier_Abstract
{
    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    private $installer = NULL;

    /** @var Varien_Db_Adapter_Pdo_Mysql */
    private $connection = NULL;

    /** @var Ess_M2ePro_Model_Upgrade_Tables */
    private $tablesObject = NULL;

    protected $tableName = NULL;
    protected $queriesLog = array();

    //########################################

    /**
     * @param Ess_M2ePro_Model_Upgrade_MySqlSetup $installer
     * @return $this
     */
    public function setInstaller(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer)
    {
        $this->installer = $installer;
        $this->connection = $installer->getConnection();
        $this->tablesObject = $installer->getTablesObject();
        return $this;
    }

    /**
     * @param string $tableName
     * @return $this
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function setTableName($tableName)
    {
        if (!$this->getTablesObject()->isExists($tableName)) {
            throw new Ess_M2ePro_Model_Exception_Setup("Table Name does not exist.");
        }

        $this->tableName = $this->getTablesObject()->getFullName($tableName);
        return $this;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Upgrade_MySqlSetup
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function getInstaller()
    {
        if (is_null($this->installer)) {
            throw new Ess_M2ePro_Model_Exception_Setup("Installer does not exist.");
        }

        return $this->installer;
    }

    /**
     * @return Varien_Db_Adapter_Pdo_Mysql
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function getConnection()
    {
        if (is_null($this->connection)) {
            throw new Ess_M2ePro_Model_Exception_Setup("Connection does not exist.");
        }

        return $this->connection;
    }

    /**
     * @return Ess_M2ePro_Model_Upgrade_Tables
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function getTablesObject()
    {
        if (is_null($this->tablesObject)) {
            throw new Ess_M2ePro_Model_Exception_Setup("Tables Object does not exist.");
        }

        return $this->tablesObject;
    }

    /**
     * @return string
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function getTableName()
    {
        if (is_null($this->tableName)) {
            throw new Ess_M2ePro_Model_Exception_Setup("Table Name does not exist.");
        }

        return $this->tableName;
    }

    //########################################

    public function runQuery($query)
    {
        $this->addQueryToLog($query);

        $this->getConnection()->query($query);
        $this->getConnection()->resetDdlCache();

        return $this;
    }

    public function addQueryToLog($query)
    {
        $this->queriesLog[] = $query;
        return $this;
    }

    // ---------------------------------------

    public function setQueriesLog(array $queriesLog = array())
    {
        $this->queriesLog = $queriesLog;
        return $this;
    }

    public function getQueriesLog()
    {
        return $this->queriesLog;
    }

    //########################################
}