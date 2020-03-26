<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Lock_Transactional_Manager extends Varien_Object
{
    protected $_nick = 'undefined';

    protected $_isTableLocked        = false;
    protected $_isTransactionStarted = false;

    //########################################

    public function __construct()
    {
        $args = func_get_args();

        !empty($args[0]['nick']) && $this->_nick = $args[0]['nick'];

        parent::__construct();
    }

    public function __destruct()
    {
        $this->unlock();
    }

    //########################################

    public function lock()
    {
        if ($this->getExclusiveLock()) {
            return;
        }

        $this->createExclusiveLock();
        $this->getExclusiveLock();
    }

    public function unlock()
    {
        $this->_isTableLocked && $this->unlockTable();
        $this->_isTransactionStarted && $this->commitTransaction();
    }

    //########################################

    protected function getExclusiveLock()
    {
        $this->startTransaction();

        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $lockId = (int)$connection->select()
            ->from($this->getTableName(), array('id'))
            ->where('nick = ?', $this->_nick)
            ->forUpdate()
            ->query()->fetchColumn();

        if ($lockId) {
            return true;
        }

        $this->commitTransaction();
        return false;
    }

    protected function createExclusiveLock()
    {
        $this->lockTable();

        $lock = Mage::getModel('M2ePro/Lock_Transactional')->load($this->_nick, 'nick');

        if ($lock->getId() === null) {
            $lock = Mage::getModel('M2ePro/Lock_Transactional');
            $lock->setData(
                array(
                    'nick' => $this->_nick,
                )
            );
            $lock->save();
        }

        $this->unlockTable();
    }

    //########################################

    protected function startTransaction()
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->beginTransaction();

        $this->_isTransactionStarted = true;
    }

    protected function commitTransaction()
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->commit();

        $this->_isTransactionStarted = false;
    }

    // ----------------------------------------

    protected function lockTable()
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->query("LOCK TABLES `{$this->getTableName()}` WRITE");

        $this->_isTableLocked = true;
    }

    protected function unlockTable()
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->query('UNLOCK TABLES');

        $this->_isTableLocked = false;
    }

    protected function getTableName()
    {
        return Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_lock_transactional');
    }

    //########################################

    public function getNick()
    {
        return $this->_nick;
    }

    //########################################
}
