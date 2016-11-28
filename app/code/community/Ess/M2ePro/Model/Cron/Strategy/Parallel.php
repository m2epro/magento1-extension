<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2EPro_Model_Cron_Strategy_Parallel extends Ess_M2ePro_Model_Cron_Strategy_Abstract
{
    /**
     * @var Ess_M2ePro_Model_LockItem
     */
    private $fastTasksLockItem = null;

    //########################################

    protected function getNick()
    {
        return Ess_M2ePro_Helper_Module_Cron::STRATEGY_PARALLEL;
    }

    //########################################

    protected function processTasks()
    {
        $result = true;

        if (!$this->getFastTasksLockItem()->isExist()) {

            $this->getFastTasksLockItem()->create();
            $this->getFastTasksLockItem()->makeShutdownFunction();
            sleep(2);

            $result = !$this->processFastTasks() ? false : $result;

            $this->getFastTasksLockItem()->remove();
            sleep(2);
        }

        return !$this->processSlowTasks() ? false : $result;
    }

    // ---------------------------------------

    private function processFastTasks()
    {
        $result = true;

        foreach ($this->getAllowedFastTasks() as $taskNick) {

            try {

                $tempResult = $this->getTaskObject($taskNick)->process();

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

            $taskNick = $this->getNextSlowTask();
            $helper->setLastExecutedSlowTask($taskNick);

            $taskObject = $this->getTaskObject($taskNick);

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

    //########################################
}