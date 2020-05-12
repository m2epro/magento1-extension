<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Lock_Item_Manager as LockManager;
use Ess_M2ePro_Model_Cron_Strategy_Serial as Serial;
use Ess_M2ePro_Model_Cron_Strategy_Parallel as Parallel;

abstract class Ess_M2ePro_Model_Cron_Strategy_Abstract
{
    const INITIALIZATION_TRANSACTIONAL_LOCK_NICK = 'cron_strategy_initialization';

    const PROGRESS_START_EVENT_NAME           = 'm2epro_cron_progress_start';
    const PROGRESS_SET_PERCENTAGE_EVENT_NAME  = 'm2epro_cron_progress_set_percentage';
    const PROGRESS_SET_DETAILS_EVENT_NAME     = 'm2epro_cron_progress_set_details';
    const PROGRESS_STOP_EVENT_NAME            = 'm2epro_cron_progress_stop';

    protected $_initiator = null;

    /**
     * @var Ess_M2ePro_Model_Lock_Transactional_Manager
     */
    protected $_initializationLockManager;

    /**
     * @var Ess_M2ePro_Model_Cron_OperationHistory
     */
    protected $_operationHistory = null;

    /**
     * @var Ess_M2ePro_Model_Cron_OperationHistory
     */
    protected $_parentOperationHistory = null;

    //########################################

    public function setInitiator($initiator)
    {
        $this->_initiator = $initiator;
        return $this;
    }

    public function getInitiator()
    {
        return $this->_initiator;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Cron_OperationHistory $operationHistory
     * @return $this
     */
    public function setParentOperationHistory(Ess_M2ePro_Model_Cron_OperationHistory $operationHistory)
    {
        $this->_parentOperationHistory = $operationHistory;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Cron_OperationHistory
     */
    public function getParentOperationHistory()
    {
        return $this->_parentOperationHistory;
    }

    //########################################

    abstract protected function getNick();

    //########################################

    public function process()
    {
        $this->beforeStart();

        try {
            $this->processTasks();
        } catch (Exception $exception) {
            $this->processException($exception);
        }

        $this->afterEnd();
    }

    // ---------------------------------------

    /**
     * @param $taskNick
     * @return Ess_M2ePro_Model_Cron_Task_Abstract
     */
    protected function getTaskObject($taskNick)
    {
        $taskNick = preg_replace_callback(
            '/_([a-z])/i', function($matches) {
            return ucfirst($matches[1]);
            }, $taskNick
        );

        $taskNick = preg_replace_callback(
            '/\/([a-z])/i', function($matches) {
            return '_' . ucfirst($matches[1]);
            }, $taskNick
        );

        $taskNick = ucfirst($taskNick);

        /** @var $task Ess_M2ePro_Model_Cron_Task_Abstract **/
        $task = Mage::getModel('M2ePro/Cron_Task_'.trim($taskNick));

        $task->setInitiator($this->getInitiator());
        $task->setParentOperationHistory($this->getOperationHistory());

        return $task;
    }

    protected function getNextTaskGroup()
    {
        $lastExecuted = Mage::helper('M2ePro/Module_Cron')->getLastExecutedTaskGroup();
        $allowed = Mage::getSingleton('M2ePro/Cron_Task_Repository')->getRegisteredGroups();
        $lastExecutedIndex = array_search($lastExecuted, $allowed, true);

        if (empty($lastExecuted) || $lastExecutedIndex === false || end($allowed) === $lastExecuted) {
            return reset($allowed);
        }

        return $allowed[$lastExecutedIndex + 1];
    }

    abstract protected function processTasks();

    //########################################

    protected function beforeStart()
    {
        $parentId = $this->getParentOperationHistory()
            ? $this->getParentOperationHistory()->getObject()->getId() : null;
        $this->getOperationHistory()->start('cron_strategy_'.$this->getNick(), $parentId, $this->getInitiator());
        $this->getOperationHistory()->makeShutdownFunction();
    }

    protected function afterEnd()
    {
        $this->getOperationHistory()->stop();
    }

    //########################################

    protected function keepAliveStart(Ess_M2ePro_Model_Lock_Item_Manager $lockItemManager)
    {
        Mage::getSingleton('M2ePro/Cron_Strategy_Observer_KeepAlive')->enable();
        Mage::getSingleton('M2ePro/Cron_Strategy_Observer_KeepAlive')->setLockItemManager($lockItemManager);
    }

    protected function keepAliveStop()
    {
        Mage::getSingleton('M2ePro/Cron_Strategy_Observer_KeepAlive')->disable();
    }

    //########################################

    protected function startListenProgressEvents(Ess_M2ePro_Model_Lock_Item_Manager $lockItemManager)
    {
        Mage::getSingleton('M2ePro/Cron_Strategy_Observer_Progress')->enable();
        Mage::getSingleton('M2ePro/Cron_Strategy_Observer_Progress')->setLockItemManager($lockItemManager);
    }

    protected function stopListenProgressEvents()
    {
        Mage::getSingleton('M2ePro/Cron_Strategy_Observer_Progress')->disable();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Cron_OperationHistory
     */
    protected function getOperationHistory()
    {
        if ($this->_operationHistory !== null) {
            return $this->_operationHistory;
        }

        return $this->_operationHistory = Mage::getModel('M2ePro/Cron_OperationHistory');
    }

    protected function makeLockItemShutdownFunction(Ess_M2ePro_Model_Lock_Item_Manager $lockItemManager)
    {
        $lockItem = Mage::getModel('M2ePro/Lock_Item')->load($lockItemManager->getNick(), 'nick');
        if (!$lockItem->getId()) {
            return;
        }

        $id = $lockItem->getId();

        register_shutdown_function(
            function() use ($id)
            {
                $error = error_get_last();
                if ($error === null || !in_array((int)$error['type'], array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR))) {
                    return;
                }

                $lockItem = Mage::getModel('M2ePro/Lock_Item')->load($id);
                if ($lockItem->getId()) {
                    $lockItem->delete();
                }
            }
        );
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Lock_Transactional_Manager
     */
    protected function getInitializationLockManager()
    {
        if ($this->_initializationLockManager !== null) {
            return $this->_initializationLockManager;
        }

        /** @var Ess_M2ePro_Model_Lock_Transactional_Manager $transactionalManager */
        $this->_initializationLockManager = Mage::getModel(
            'M2ePro/Lock_Transactional_Manager', array(
                'nick' => self::INITIALIZATION_TRANSACTIONAL_LOCK_NICK
            )
        );

        return $this->_initializationLockManager;
    }

    /**
     * @return bool
     */
    protected function isParallelStrategyInProgress()
    {
        for ($i = 1; $i <= Parallel::MAX_PARALLEL_EXECUTED_CRONS_COUNT; $i++) {
            $lockManager = Mage::getModel(
                'M2ePro/Lock_Item_Manager',
                array('nick' => Parallel::GENERAL_LOCK_ITEM_PREFIX.$i)
            );

            if ($lockManager->isExist()) {
                if ($lockManager->isInactiveMoreThanSeconds(LockManager::DEFAULT_MAX_INACTIVE_TIME)) {
                    $lockManager->remove();
                    continue;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function isSerialStrategyInProgress()
    {
        $lockManager = Mage::getModel('M2ePro/Lock_Item_Manager', array('nick' => Serial::LOCK_ITEM_NICK));
        if (!$lockManager->isExist()) {
            return false;
        }

        if ($lockManager->isInactiveMoreThanSeconds(LockManager::DEFAULT_MAX_INACTIVE_TIME)) {
            $lockManager->remove();
            return false;
        }

        return true;
    }

    //########################################

    protected function processException(Exception $exception)
    {
        $this->getOperationHistory()->addContentData(
            'exceptions', array(
                'message' => $exception->getMessage(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'trace'   => $exception->getTraceAsString(),
            )
        );

        Mage::helper('M2ePro/Module_Exception')->process($exception);
    }

    //########################################
}
