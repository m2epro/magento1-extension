<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_Tables
{
    const PREFIX = 'm2epro_';

    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    protected $_installer = null;

    /** @var Varien_Db_Adapter_Pdo_Mysql */
    protected $_connection = null;

    /**
     * @var string[]
     */
    protected $_entities = array();

    //########################################

    public function __construct(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer)
    {
        $this->_installer  = $installer;
        $this->_connection = $installer->getConnection();

        $this->init();
    }

    public function init()
    {
        $magentoTablesPrefix = Mage::helper('M2ePro/Magento')->getDatabaseTablesPrefix();
        foreach (Mage::helper('M2ePro/Module_Database_Structure')->getMysqlTables() as $tableName) {
            $this->_entities[str_replace($magentoTablesPrefix . self::PREFIX, '', $tableName)] = $tableName;
        }
    }

    //########################################

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

    //########################################

    public function getAllEntities()
    {
        return $this->_entities;
    }

    public function getCurrentEntities()
    {
        $result = array();

        foreach (Mage::helper('M2ePro/Module_Database_Structure')->getModuleTables() as $table) {
            $result[$table] = $this->_entities[$table];
        }

        return $result;
    }

    //########################################

    public function isExists($tableName)
    {
        return $this->getInstaller()->tableExists($this->getFullName($tableName));
    }

    public function getFullName($tableName)
    {
        if (strpos(self::PREFIX, $tableName) === false) {
            $tableName = self::PREFIX . $tableName;
        }

        return $this->getInstaller()->getTable($tableName);
    }

    //########################################

    public function renameTable($oldTable, $newTable)
    {
        $oldTable = $this->getFullName($oldTable);
        $newTable = $this->getFullName($newTable);

        if ($this->_installer->tableExists($oldTable) && !$this->_installer->tableExists($newTable)) {
            $this->getConnection()->query(<<<SQL
    RENAME TABLE `{$oldTable}` TO `{$newTable}`
SQL
            );
            return true;
        }

        return false;
    }

    //########################################
}
