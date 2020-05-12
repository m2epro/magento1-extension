<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Lock_Item_Manager as LockManager;

class Ess_M2ePro_Model_Cron_Strategy_Serial extends Ess_M2ePro_Model_Cron_Strategy_Abstract
{
    const LOCK_ITEM_NICK = 'cron_strategy_serial';

    /**
     * @var Ess_M2ePro_Model_Lock_Item_Manager
     */
    protected $_lockItemManager;

    protected $_allowedTasks;

    //########################################

    protected function getNick()
    {
        return Ess_M2ePro_Helper_Module_Cron::STRATEGY_SERIAL;
    }

    //########################################

    protected function processTasks()
    {
        $this->getInitializationLockManager()->lock();

        if ($this->isParallelStrategyInProgress()) {
            $this->getInitializationLockManager()->unlock();
            return;
        }

        if ($this->getLockItemManager() === false) {
            return;
        }

        try {
            $this->getLockItemManager()->create();
            $this->makeLockItemShutdownFunction($this->getLockItemManager());

            $this->getInitializationLockManager()->unlock();

            $this->keepAliveStart($this->getLockItemManager());
            $this->startListenProgressEvents($this->getLockItemManager());

            $this->processAllTasks();

            $this->keepAliveStop();
            $this->stopListenProgressEvents();
        } catch (Exception $exception) {
            $this->processException($exception);
        }

        $this->getLockItemManager()->remove();
    }

    // ---------------------------------------

    protected function processAllTasks()
    {
        $taskGroup = null;
        /**
         * Developer cron runner
         */
        if ($this->_allowedTasks === null) {
            $taskGroup = $this->getNextTaskGroup();
            Mage::helper('M2ePro/Module_Cron')->setLastExecutedTaskGroup($taskGroup);
        }

        foreach ($this->getAllowedTasks($taskGroup) as $taskNick) {
            try {
                $taskObject = $this->getTaskObject($taskNick);
                $taskObject->setLockItemManager($this->getLockItemManager());

                $taskObject->process();
            } catch (Exception $exception) {
                $this->processException($exception);
            }
        }
    }

    //########################################

    /**
     * @param array $tasks
     * @return $this
     */
    public function setAllowedTasks(array $tasks)
    {
        $this->_allowedTasks = $tasks;
        return $this;
    }

    public function getAllowedTasks($taskGroup)
    {
        if ($this->_allowedTasks !== null) {
            return $this->_allowedTasks;
        }

        return Mage::getSingleton('M2ePro/Cron_Task_Repository')->getGroupTasks($taskGroup);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Lock_Item_Manager|bool
     */
    protected function getLockItemManager()
    {
        if ($this->_lockItemManager !== null) {
            return $this->_lockItemManager;
        }

        $lockItemManager = Mage::getModel(
            'M2ePro/Lock_Item_Manager', array('nick' => self::LOCK_ITEM_NICK)
        );

        if (!$lockItemManager->isExist()) {
            return $this->_lockItemManager = $lockItemManager;
        }

        if ($lockItemManager->isInactiveMoreThanSeconds(LockManager::DEFAULT_MAX_INACTIVE_TIME)) {
            $lockItemManager->remove();
            return $this->_lockItemManager = $lockItemManager;
        }

        return false;
    }

    //########################################
}
