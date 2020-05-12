<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Lock_Item_Manager as LockManager;

class Ess_M2ePro_Model_Cron_Strategy_Parallel extends Ess_M2ePro_Model_Cron_Strategy_Abstract
{
    const GENERAL_LOCK_ITEM_PREFIX  = 'cron_strategy_parallel_';
    const FAST_TASKS_LOCK_ITEM_NICK = 'cron_strategy_parallel_fast_tasks';

    const MAX_PARALLEL_EXECUTED_CRONS_COUNT = 10;
    const MAX_SLOW_TASK_EXECUTION_TIME_FOR_CONTINUE = 60;

    /**
     * @var Ess_M2ePro_Model_Lock_Item_Manager
     */
    protected $_lockItemManager;

    /**
     * @var Ess_M2ePro_Model_Lock_Item_Manager
     */
    protected $_fastTasksLockItemManager;

    //########################################

    protected function getNick()
    {
        return Ess_M2ePro_Helper_Module_Cron::STRATEGY_PARALLEL;
    }

    //########################################

    protected function processTasks()
    {
        $this->getInitializationLockManager()->lock();

        if ($this->isSerialStrategyInProgress()) {
            $this->getInitializationLockManager()->unlock();
            return;
        }

        if ($this->getLockItemManager() === false) {
            return;
        }

        try {
            $this->getLockItemManager()->create();
            $this->makeLockItemShutdownFunction($this->getLockItemManager());

            $this->processFastTasks();
            $this->getInitializationLockManager()->unlock();
            $this->processSlowTasks();
        } catch (Exception $exception) {
            $this->processException($exception);
        }

        $this->getLockItemManager()->remove();
    }

    // ---------------------------------------

    protected function processFastTasks()
    {
        if ($this->getFastTasksLockItemManager() === false) {
            return;
        }

        try {
            $this->getFastTasksLockItemManager()->create($this->getLockItemManager()->getNick());
            $this->makeLockItemShutdownFunction($this->getFastTasksLockItemManager());

            $this->getInitializationLockManager()->unlock();

            $this->keepAliveStart($this->getFastTasksLockItemManager());
            $this->startListenProgressEvents($this->getFastTasksLockItemManager());

            $taskGroup = $this->getNextTaskGroup();
            Mage::helper('M2ePro/Module_Cron')->setLastExecutedTaskGroup($taskGroup);

            foreach ($this->getAllowedFastTasks($taskGroup) as $taskNick) {
                try {
                    $taskObject = $this->getTaskObject($taskNick);
                    $taskObject->setLockItemManager($this->getFastTasksLockItemManager());

                    $taskObject->process();
                } catch (Exception $exception) {
                    $this->processException($exception);
                }
            }

            $this->keepAliveStop();
            $this->stopListenProgressEvents();
        } catch (Exception $exception) {
            $this->processException($exception);
        }

        $this->getFastTasksLockItemManager()->remove();
    }

    protected function processSlowTasks()
    {
        $startTime = time();

        $countOfAllowedTasks = count($this->getAllowedSlowTasks());
        for ($i = 0; $i < $countOfAllowedTasks; $i++) {

            /** @var Ess_M2ePro_Model_Lock_Transactional_Manager $transactionalManager */
            $transactionalManager = Mage::getModel(
                'M2ePro/Lock_Transactional_Manager', array(
                    'nick' => self::GENERAL_LOCK_ITEM_PREFIX.'slow_task_switch'
                )
            );

            $transactionalManager->lock();

            $taskNick = $this->getNextSlowTask();
            Mage::helper('M2ePro/Module_Cron')->setLastExecutedSlowTask($taskNick);

            $transactionalManager->unlock();

            $taskLockItemManager = Mage::getModel(
                'M2ePro/Lock_Item_Manager', array(
                    'nick' => 'cron_task_'.str_replace("/", "_", $taskNick)
                )
            );

            if ($taskLockItemManager->isExist()) {
                if (!$taskLockItemManager->isInactiveMoreThanSeconds(LockManager::DEFAULT_MAX_INACTIVE_TIME)) {
                    continue;
                }

                $taskLockItemManager->remove();
            }

            try {
                $taskLockItemManager->create($this->getLockItemManager()->getNick());
                $this->makeLockItemShutdownFunction($taskLockItemManager);

                $taskObject = $this->getTaskObject($taskNick);
                $taskObject->setLockItemManager($taskLockItemManager);

                $this->keepAliveStart($taskLockItemManager);
                $this->startListenProgressEvents($taskLockItemManager);

                $taskObject->process();

                $this->keepAliveStop();
                $this->stopListenProgressEvents();
            } catch (Exception $exception) {
                $this->processException($exception);
            }

            $taskLockItemManager->remove();

            $processTime = time() - $startTime;
            if ($processTime > self::MAX_SLOW_TASK_EXECUTION_TIME_FOR_CONTINUE) {
                break;
            }
        }
    }

    //########################################

    protected function getAllowedFastTasks($group)
    {
        $tasks = Mage::getSingleton('M2ePro/Cron_Task_Repository')->getGroupTasks($group);
        return array_values(array_diff($tasks, $this->getAllowedSlowTasks()));
    }

    /**
     * These tasks will work in parallel to each one and to fast tasks process also. Up to 10 processes!
     */
    protected function getAllowedSlowTasks()
    {
        return Mage::getSingleton('M2ePro/Cron_Task_Repository')->getParallelTasks();
    }

    //########################################

    protected function getNextSlowTask()
    {
        $lastExecuted = Mage::helper('M2ePro/Module_Cron')->getLastExecutedSlowTask();
        $allowed = $this->getAllowedSlowTasks();
        $lastExecutedIndex = array_search($lastExecuted, $allowed, true);

        if (empty($lastExecuted) || $lastExecutedIndex === false || end($allowed) === $lastExecuted) {
            return reset($allowed);
        }

        return $allowed[$lastExecutedIndex + 1];
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

        for ($index = 1; $index <= self::MAX_PARALLEL_EXECUTED_CRONS_COUNT; $index++) {
            $lockItemManager = Mage::getModel(
                'M2ePro/Lock_Item_Manager', array('nick' => self::GENERAL_LOCK_ITEM_PREFIX.$index)
            );

            if (!$lockItemManager->isExist()) {
                return $this->_lockItemManager = $lockItemManager;
            }

            if ($lockItemManager->isInactiveMoreThanSeconds(LockManager::DEFAULT_MAX_INACTIVE_TIME)) {
                $lockItemManager->remove();
                return $this->_lockItemManager = $lockItemManager;
            }
        }

        return false;
    }

    /**
     * @return Ess_M2ePro_Model_Lock_Item_Manager|bool
     */
    protected function getFastTasksLockItemManager()
    {
        if ($this->_fastTasksLockItemManager !== null) {
            return $this->_fastTasksLockItemManager;
        }

        $lockItemManager = Mage::getModel(
            'M2ePro/Lock_Item_Manager', array('nick' => self::FAST_TASKS_LOCK_ITEM_NICK)
        );

        if (!$lockItemManager->isExist()) {
            return $this->_fastTasksLockItemManager = $lockItemManager;
        }

        if ($lockItemManager->isInactiveMoreThanSeconds(LockManager::DEFAULT_MAX_INACTIVE_TIME)) {
            $lockItemManager->remove();
            return $this->_fastTasksLockItemManager = $lockItemManager;
        }

        return false;
    }

    //########################################
}
