<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_Modifier_Abstract
{
    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    protected $_installer = null;

    /** @var Varien_Db_Adapter_Pdo_Mysql */
    protected $_connection = null;

    /** @var Ess_M2ePro_Model_Upgrade_Tables */
    protected $_tablesObject = null;

    protected $_tableName  = null;
    protected $_queriesLog = array();

    //########################################

    /**
     * @param Ess_M2ePro_Model_Upgrade_MySqlSetup $installer
     * @return $this
     */
    public function setInstaller(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer)
    {
        $this->_installer    = $installer;
        $this->_connection   = $installer->getConnection();
        $this->_tablesObject = $installer->getTablesObject();
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
            throw new Ess_M2ePro_Model_Exception_Setup(
                sprintf(
                    'Table Name [%s] does not exist.', $tableName
                )
            );
        }

        $this->_tableName = $this->getTablesObject()->getFullName($tableName);
        return $this;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Upgrade_MySqlSetup
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function getInstaller()
    {
        if ($this->_installer === null) {
            throw new Ess_M2ePro_Model_Exception_Setup("Installer does not exist.");
        }

        return $this->_installer;
    }

    /**
     * @return Varien_Db_Adapter_Pdo_Mysql
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function getConnection()
    {
        if ($this->_connection === null) {
            throw new Ess_M2ePro_Model_Exception_Setup("Connection does not exist.");
        }

        return $this->_connection;
    }

    /**
     * @return Ess_M2ePro_Model_Upgrade_Tables
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function getTablesObject()
    {
        if ($this->_tablesObject === null) {
            throw new Ess_M2ePro_Model_Exception_Setup("Tables Object does not exist.");
        }

        return $this->_tablesObject;
    }

    /**
     * @return string
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function getTableName()
    {
        if ($this->_tableName === null) {
            throw new Ess_M2ePro_Model_Exception_Setup("Table Name does not exist.");
        }

        return $this->_tableName;
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
        $this->_queriesLog[] = $query;
        return $this;
    }

    // ---------------------------------------

    public function setQueriesLog(array $queriesLog = array())
    {
        $this->_queriesLog = $queriesLog;
        return $this;
    }

    public function getQueriesLog()
    {
        return $this->_queriesLog;
    }

    //########################################
}
