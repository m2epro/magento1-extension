<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Lock_Transactional_Manager extends Varien_Object
{
    private $nick = 'undefined';

    private $isTableLocked = false;
    private $isTransactionStarted = false;

    //########################################

    public function __construct()
    {
        $args = func_get_args();

        !empty($args[0]['nick']) && $this->nick = $args[0]['nick'];

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
        $this->isTableLocked        && $this->unlockTable();
        $this->isTransactionStarted && $this->commitTransaction();
    }

    //########################################

    private function getExclusiveLock()
    {
        $this->startTransaction();

        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $lockId = (int)$connection->select()
            ->from($this->getTableName(), array('id'))
            ->where('nick = ?', $this->nick)
            ->forUpdate()
            ->query()->fetchColumn();

        if ($lockId) {
            return true;
        }

        $this->commitTransaction();
        return false;
    }

    private function createExclusiveLock()
    {
        $this->lockTable();

        $lock = Mage::getModel('M2ePro/Lock_Transactional')->load($this->nick, 'nick');

        if (is_null($lock->getId())) {

            $lock = Mage::getModel('M2ePro/Lock_Transactional');
            $lock->setData(array(
                'nick' => $this->nick,
            ));
            $lock->save();
        }

        $this->unlockTable();
    }

    // ########################################

    private function startTransaction()
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->beginTransaction();

        $this->isTransactionStarted = true;
    }

    private function commitTransaction()
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->commit();

        $this->isTransactionStarted = false;
    }

    // ----------------------------------------

    private function lockTable()
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->query("LOCK TABLES `{$this->getTableName()}` WRITE");

        $this->isTableLocked = true;
    }

    private function unlockTable()
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->query('UNLOCK TABLES');

        $this->isTableLocked = false;
    }

    private function getTableName()
    {
        return Mage::getSingleton('core/resource')->getTableName('m2epro_lock_transactional');
    }

    //########################################

    public function setNick($value)
    {
        $this->nick = $value;
    }

    public function getNick()
    {
        return $this->nick;
    }

    //########################################
}