<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Strategy_Serial extends Ess_M2ePro_Model_Cron_Strategy_Abstract
{
    const LOCK_ITEM_NICK = 'cron_strategy_serial';

    /**
     * @var Ess_M2ePro_Model_LockItem
     */
    private $lockItem = NULL;

    //########################################

    protected function getNick()
    {
        return Ess_M2ePro_Helper_Module_Cron::STRATEGY_SERIAL;
    }

    //########################################

    /**
     * @param $taskNick
     * @return Ess_M2ePro_Model_Cron_Task_Abstract
     */
    protected function getTaskObject($taskNick)
    {
        $task = parent::getTaskObject($taskNick);
        return $task->setParentLockItem($this->getLockItem());
    }

    protected function processTasks()
    {
        $result = true;

        /** @var Ess_M2ePro_Model_Lock_Transactional_Manager $transactionalManager */
        $transactionalManager = Mage::getModel('M2ePro/Lock_Transactional_Manager', array(
            'nick' => self::INITIALIZATION_TRANSACTIONAL_LOCK_NICK
        ));

        $transactionalManager->lock();

        if ($this->getLockItem()->isExist() || $this->isParallelStrategyInProgress()) {
            $transactionalManager->unlock();
            return $result;
        }

        $this->getLockItem()->create();
        $this->getLockItem()->makeShutdownFunction();

        $transactionalManager->unlock();

        $result = $this->processAllTasks();

        $this->getLockItem()->remove();

        return $result;
    }

    // ---------------------------------------

    private function processAllTasks()
    {
        $result = true;

        foreach ($this->getAllowedTasks() as $taskNick) {

            try {

                $tempResult = $this->getTaskObject($taskNick)->process();

                if (!is_null($tempResult) && !$tempResult) {
                    $result = false;
                }

                $this->getLockItem()->activate();

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

    //########################################

    /**
     * @return Ess_M2ePro_Model_LockItem
     */
    protected function getLockItem()
    {
        if (!is_null($this->lockItem)) {
            return $this->lockItem;
        }

        $this->lockItem = Mage::getModel('M2ePro/LockItem');
        $this->lockItem->setNick(self::LOCK_ITEM_NICK);

        return $this->lockItem;
    }

    /**
     * @return bool
     */
    protected function isParallelStrategyInProgress()
    {
        for ($i = 1; $i <= Ess_M2ePro_Model_Cron_Strategy_Parallel::MAX_PARALLEL_EXECUTED_CRONS_COUNT; $i++) {
            $lockItem = Mage::getModel('M2ePro/LockItem');
            $lockItem->setNick(Ess_M2ePro_Model_Cron_Strategy_Parallel::GENERAL_LOCK_ITEM_PREFIX.$i);

            if ($lockItem->isExist()) {
                return true;
            }
        }

        return false;
    }

    //########################################
}