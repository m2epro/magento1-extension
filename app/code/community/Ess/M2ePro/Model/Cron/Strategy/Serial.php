<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Strategy_Serial extends Ess_M2ePro_Model_Cron_Strategy_Abstract
{
    const LOCK_ITEM_NICK = 'cron_strategy_serial';

    /**
     * @var Ess_M2ePro_Model_Lock_Item_Manager
     */
    private $lockItemManager = NULL;

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
        return $task->setLockItemManager($this->getLockItemManager());
    }

    protected function processTasks()
    {
        $result = true;

        /** @var Ess_M2ePro_Model_Lock_Transactional_Manager $transactionalManager */
        $transactionalManager = Mage::getModel('M2ePro/Lock_Transactional_Manager', array(
            'nick' => self::INITIALIZATION_TRANSACTIONAL_LOCK_NICK
        ));

        $transactionalManager->lock();

        if ($this->isParallelStrategyInProgress()) {
            $transactionalManager->unlock();
            return $result;
        }

        if ($this->getLockItemManager()->isExist()) {
            if (!$this->getLockItemManager()->isInactiveMoreThanSeconds(
                    Ess_M2ePro_Model_Lock_Item_Manager::DEFAULT_MAX_INACTIVE_TIME
            )) {
                $transactionalManager->unlock();
                return $result;
            }

            $this->getLockItemManager()->remove();
        }

        $this->getLockItemManager()->create();
        $this->makeLockItemShutdownFunction($this->getLockItemManager());

        $transactionalManager->unlock();

        $this->keepAliveStart($this->getLockItemManager());
        $this->startListenProgressEvents($this->getLockItemManager());

        $result = $this->processAllTasks();

        $this->keepAliveStop();
        $this->stopListenProgressEvents();

        $this->getLockItemManager()->remove();

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

                $this->getLockItemManager()->activate();

            } catch (Exception $exception) {

                $result = false;

                $this->getOperationHistory()->addContentData('exceptions', array(
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
     * @return Ess_M2ePro_Model_Lock_Item_Manager
     */
    protected function getLockItemManager()
    {
        if (!is_null($this->lockItemManager)) {
            return $this->lockItemManager;
        }

        $this->lockItemManager = Mage::getModel('M2ePro/Lock_Item_Manager', array('nick' => self::LOCK_ITEM_NICK));

        return $this->lockItemManager;
    }

    /**
     * @return bool
     */
    protected function isParallelStrategyInProgress()
    {
        for ($i = 1; $i <= Ess_M2ePro_Model_Cron_Strategy_Parallel::MAX_PARALLEL_EXECUTED_CRONS_COUNT; $i++) {
            $lockItemManager = Mage::getModel(
                'M2ePro/Lock_Item_Manager',
                array('nick' => Ess_M2ePro_Model_Cron_Strategy_Parallel::GENERAL_LOCK_ITEM_PREFIX.$i)
            );

            if ($lockItemManager->isExist()) {

                if ($lockItemManager->isInactiveMoreThanSeconds(
                        Ess_M2ePro_Model_Lock_Item_Manager::DEFAULT_MAX_INACTIVE_TIME
                )) {
                    $lockItemManager->remove();
                    continue;
                }

                return true;
            }
        }

        return false;
    }

    //########################################
}