<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Strategy_Parallel extends Ess_M2ePro_Model_Cron_Strategy_Abstract
{
    const GENERAL_LOCK_ITEM_PREFIX = 'cron_strategy_parallel_';

    const MAX_PARALLEL_EXECUTED_CRONS_COUNT = 10;

    /**
     * @var Ess_M2ePro_Model_LockItem
     */
    private $generalLockItem = NULL;

    /**
     * @var Ess_M2ePro_Model_LockItem
     */
    private $fastTasksLockItem = NULL;

    //########################################

    protected function getNick()
    {
        return Ess_M2ePro_Helper_Module_Cron::STRATEGY_PARALLEL;
    }

    //########################################

    protected function processTasks()
    {
        $result = true;

        /** @var Ess_M2ePro_Model_Lock_Transactional_Manager $transactionalManager */
        $transactionalManager = Mage::getModel('M2ePro/Lock_Transactional_Manager', array(
            'nick' => self::INITIALIZATION_TRANSACTIONAL_LOCK_NICK
        ));

        $transactionalManager->lock();

        if ($this->isSerialStrategyInProgress()) {
            $transactionalManager->unlock();
            return $result;
        }

        $this->getGeneralLockItem()->create();
        $this->getGeneralLockItem()->makeShutdownFunction();

        if (!$this->getFastTasksLockItem()->isExist()) {
            $this->getFastTasksLockItem()->create($this->getGeneralLockItem()->getRealId());
            $this->getFastTasksLockItem()->makeShutdownFunction();

            $transactionalManager->unlock();

            $result = !$this->processFastTasks() ? false : $result;

            $this->getFastTasksLockItem()->remove();
        }

        $transactionalManager->unlock();

        $result = !$this->processSlowTasks() ? false : $result;

        $this->getGeneralLockItem()->remove();

        return $result;
    }

    // ---------------------------------------

    private function processFastTasks()
    {
        $result = true;

        foreach ($this->getAllowedFastTasks() as $taskNick) {

            try {

                $taskObject = $this->getTaskObject($taskNick);
                $taskObject->setParentLockItem($this->getFastTasksLockItem());

                $tempResult = $taskObject->process();

                if (!is_null($tempResult) && !$tempResult) {
                    $result = false;
                }

                $this->getFastTasksLockItem()->activate();

            } catch (Exception $exception) {

                $result = false;

                $this->getOperationHistory()->setContentData('exception', array(
                    'message' => $exception->getMessage(),
                    'file'    => $exception->getFile(),
                    'line'    => $exception->getLine(),
                    'trace'   => $exception->getTraceAsString(),
                ));

                Mage::helper('M2ePro/Module_Exception')->process($exception);
            }
        }

        return $result;
    }

    private function processSlowTasks()
    {
        $helper = Mage::helper('M2ePro/Module_Cron');

        $result = true;

        for ($i = 0; $i < count($this->getAllowedSlowTasks()); $i++) {

            /** @var Ess_M2ePro_Model_Lock_Transactional_Manager $transactionalManager */
            $transactionalManager = Mage::getModel('M2ePro/Lock_Transactional_Manager', array(
                'nick' => self::GENERAL_LOCK_ITEM_PREFIX.'slow_task_switch'
            ));

            $transactionalManager->lock();

            $taskNick = $this->getNextSlowTask();
            $helper->setLastExecutedSlowTask($taskNick);

            $transactionalManager->unlock();

            $taskObject = $this->getTaskObject($taskNick);
            $taskObject->setParentLockItem($this->getGeneralLockItem());

            if (!$taskObject->isPossibleToRun()) {
                continue;
            }

            try {
                $result = $taskObject->process();
            } catch (Exception $exception) {

                $result = false;

                $this->getOperationHistory()->setContentData('exception', array(
                    'message' => $exception->getMessage(),
                    'file'    => $exception->getFile(),
                    'line'    => $exception->getLine(),
                    'trace'   => $exception->getTraceAsString(),
                ));

                Mage::helper('M2ePro/Module_Exception')->process($exception);
            }

            break;
        }

        return $result;
    }

    //########################################

    private function getAllowedFastTasks()
    {
        return array_intersect($this->getAllowedTasks(), array(
            Ess_M2ePro_Model_Cron_Task_RepricingInspectProducts::NICK,
            Ess_M2ePro_Model_Cron_Task_RepricingUpdateSettings::NICK,
            Ess_M2ePro_Model_Cron_Task_RepricingSynchronizationGeneral::NICK,
            Ess_M2ePro_Model_Cron_Task_RepricingSynchronizationActualPrice::NICK,
            Ess_M2ePro_Model_Cron_Task_LogsClearing::NICK,
            Ess_M2ePro_Model_Cron_Task_Servicing::NICK,
            Ess_M2ePro_Model_Cron_Task_Synchronization::NICK,
        ));
    }

    private function getAllowedSlowTasks()
    {
        return array_intersect($this->getAllowedTasks(), array());
    }

    // ---------------------------------------

    private function getNextSlowTask()
    {
        $helper = Mage::helper('M2ePro/Module_Cron');
        $lastExecutedTask = $helper->getLastExecutedSlowTask();

        $allowedSlowTasks = $this->getAllowedSlowTasks();

        if (empty($lastExecutedTask) || end($allowedSlowTasks) == $lastExecutedTask) {
            return reset($allowedSlowTasks);
        }

        $lastExecutedTaskIndex = array_search($lastExecutedTask, $this->getAllowedSlowTasks());
        return $allowedSlowTasks[$lastExecutedTaskIndex + 1];
    }

    /**
     * @return Ess_M2ePro_Model_LockItem
     * @throws Ess_M2ePro_Model_Exception
     */
    private function getGeneralLockItem()
    {
        if (!is_null($this->generalLockItem)) {
            return $this->generalLockItem;
        }

        for ($index = 1; $index <= self::MAX_PARALLEL_EXECUTED_CRONS_COUNT; $index++) {
            $lockItem = Mage::getModel('M2ePro/LockItem');
            $lockItem->setNick(self::GENERAL_LOCK_ITEM_PREFIX.$index);

            if (!$lockItem->isExist()) {
                return $this->generalLockItem = $lockItem;
            }
        }

        throw new Ess_M2ePro_Model_Exception('Too many parallel lock items.');
    }

    /**
     * @return Ess_M2ePro_Model_LockItem
     */
    private function getFastTasksLockItem()
    {
        if (!is_null($this->fastTasksLockItem)) {
            return $this->fastTasksLockItem;
        }

        $this->fastTasksLockItem = Mage::getModel('M2ePro/LockItem');
        $this->fastTasksLockItem->setNick('cron_strategy_parallel_fast_tasks');

        return $this->fastTasksLockItem;
    }

    /**
     * @return bool
     */
    private function isSerialStrategyInProgress()
    {
        $serialLockItem = Mage::getModel('M2ePro/LockItem');
        $serialLockItem->setNick(Ess_M2ePro_Model_Cron_Strategy_Serial::LOCK_ITEM_NICK);

        return $serialLockItem->isExist();
    }

    //########################################
}