<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Synchronization_Dispatcher
{
    const MAX_MEMORY_LIMIT = 512;

    private $allowedComponents = array();
    private $allowedTasksTypes = array();

    private $lockItem = NULL;
    private $operationHistory = NULL;

    private $parentLockItem = NULL;
    private $parentOperationHistory = NULL;

    private $log = NULL;
    private $params = array();
    private $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;

    //####################################

    public function process()
    {
        $this->initialize();
        $this->updateLastAccess();

        if (!$this->isPossibleToRun()) {
            return true;
        }

        $this->updateLastRun();
        $this->beforeStart();

        $result = true;

        try {

            // global tasks
            $result = !$this->processTask('Synchronization_Task_Defaults') ? false : $result;

            // components tasks
            $result = !$this->processComponent(Ess_M2ePro_Helper_Component_Ebay::NICK) ? false : $result;
            $result = !$this->processComponent(Ess_M2ePro_Helper_Component_Amazon::NICK) ? false : $result;
            $result = !$this->processComponent(Ess_M2ePro_Helper_Component_Buy::NICK) ? false : $result;

        } catch (Exception $exception) {

            $result = false;

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->getOperationHistory()->setContentData('exception', array(
                'message' => $exception->getMessage(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'trace'   => $exception->getTraceAsString(),
            ));

            $this->getLog()->addMessage(
                Mage::helper('M2ePro')->__($exception->getMessage()),
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }

        $this->afterEnd();

        return $result;
    }

    // ----------------------------------

    protected function processComponent($component)
    {
        if (!in_array($component,$this->getAllowedComponents())) {
            return false;
        }

        return $this->processTask(ucfirst($component).'_Synchronization_Launcher');
    }

    protected function processTask($taskPath)
    {
        $result = $this->makeTask($taskPath)->process();
        return is_null($result) || $result;
    }

    protected function makeTask($taskPath)
    {
        /** @var $task Ess_M2ePro_Model_Synchronization_Task **/
        $task = Mage::getModel('M2ePro/'.$taskPath);

        $task->setParentLockItem($this->getLockItem());
        $task->setParentOperationHistory($this->getOperationHistory());

        $task->setAllowedTasksTypes($this->getAllowedTasksTypes());

        $task->setLog($this->getLog());
        $task->setInitiator($this->getInitiator());
        $task->setParams($this->getParams());

        return $task;
    }

    //####################################

    public function setAllowedComponents(array $components)
    {
        $this->allowedComponents = $components;
    }

    public function getAllowedComponents()
    {
        return $this->allowedComponents;
    }

    // -----------------------------------

    public function setAllowedTasksTypes(array $types)
    {
        $this->allowedTasksTypes = $types;
    }

    public function getAllowedTasksTypes()
    {
        return $this->allowedTasksTypes;
    }

    // -----------------------------------

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    public function getParams()
    {
        return $this->params;
    }

    // -----------------------------------

    public function setInitiator($value)
    {
        $this->initiator = (int)$value;
    }

    public function getInitiator()
    {
        return $this->initiator;
    }

    // -----------------------------------

    public function setParentLockItem(Ess_M2ePro_Model_LockItem $object)
    {
        $this->parentLockItem = $object;
    }

    /**
     * @return Ess_M2ePro_Model_LockItem
     */
    public function getParentLockItem()
    {
        return $this->parentLockItem;
    }

    // -----------------------------------

    public function setParentOperationHistory(Ess_M2ePro_Model_OperationHistory $object)
    {
        $this->parentOperationHistory = $object;
    }

    /**
     * @return Ess_M2ePro_Model_OperationHistory
     */
    public function getParentOperationHistory()
    {
        return $this->parentOperationHistory;
    }

    //####################################

    protected function initialize()
    {
        Mage::helper('M2ePro/Client')->setMemoryLimit(self::MAX_MEMORY_LIMIT);
        Mage::helper('M2ePro/Module_Exception')->setFatalErrorHandler();
    }

    protected function updateLastAccess()
    {
        $currentDateTime = Mage::helper('M2ePro')->getCurrentGmtDate();
        $this->setConfigValue(NULL,'last_access',$currentDateTime);
    }

    protected function isPossibleToRun()
    {
        return (bool)(int)$this->getConfigValue(NULL,'mode') &&
               !$this->getLockItem()->isExist();
    }

    protected function updateLastRun()
    {
        $currentDateTime = Mage::helper('M2ePro')->getCurrentGmtDate();
        $this->setConfigValue(NULL,'last_run',$currentDateTime);
    }

    // -----------------------------------

    protected function beforeStart()
    {
        $lockItemParentId = $this->getParentLockItem() ? $this->getParentLockItem()->getRealId() : NULL;
        $this->getLockItem()->create($lockItemParentId);
        $this->getLockItem()->makeShutdownFunction();

        $this->getOperationHistory()->cleanOldData();

        $operationHistoryParentId = $this->getParentOperationHistory() ?
                $this->getParentOperationHistory()->getObject()->getId() : NULL;
        $this->getOperationHistory()->start('synchronization',
                                            $operationHistoryParentId,
                                            $this->getInitiator());
        $this->getOperationHistory()->makeShutdownFunction();

        $this->getLog()->setOperationHistoryId($this->getOperationHistory()->getObject()->getId());

        $this->checkAndPrepareProductChange();

        if (in_array(Ess_M2ePro_Model_Synchronization_Task::ORDERS, $this->getAllowedTasksTypes())) {
            Mage::dispatchEvent('m2epro_synchronization_before_start', array());
        }
    }

    protected function afterEnd()
    {
        if (in_array(Ess_M2ePro_Model_Synchronization_Task::ORDERS, $this->getAllowedTasksTypes())) {
            Mage::dispatchEvent('m2epro_synchronization_after_end', array());
        }

        Mage::getModel('M2ePro/ProductChange')->clearLastProcessed(
            $this->getOperationHistory()->getObject()->getData('start_date'),
            (int)$this->getConfigValue('/settings/product_change/', 'max_count_per_one_time')
        );

        $this->getOperationHistory()->stop();
        $this->getLockItem()->remove();
    }

    //####################################

    /**
     * @return Ess_M2ePro_Model_Synchronization_LockItem
     */
    protected function getLockItem()
    {
        if (is_null($this->lockItem)) {
            $this->lockItem = Mage::getModel('M2ePro/Synchronization_LockItem');
        }
        return $this->lockItem;
    }

    /**
     * @return Ess_M2ePro_Model_Synchronization_OperationHistory
     */
    public function getOperationHistory()
    {
        if (is_null($this->operationHistory)) {
            $this->operationHistory = Mage::getModel('M2ePro/Synchronization_OperationHistory');
        }
        return $this->operationHistory;
    }

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getLog()
    {
        if (is_null($this->log)) {
            $this->log = Mage::getModel('M2ePro/Synchronization_Log');
            $this->log->setInitiator($this->getInitiator());
            $this->log->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_UNKNOWN);
        }
        return $this->log;
    }

    // -----------------------------------

    protected function checkAndPrepareProductChange()
    {
        Mage::getModel('M2ePro/ProductChange')->clearOutdated(
            $this->getConfigValue('/settings/product_change/', 'max_lifetime')
        );
        Mage::getModel('M2ePro/ProductChange')->clearExcessive(
            (int)$this->getConfigValue('/settings/product_change/', 'max_count')
        );

        $startDate = $this->getOperationHistory()->getObject()->getData('start_date');
        $maxCountPerOneTime = (int)$this->getConfigValue('/settings/product_change/', 'max_count_per_one_time');

        $functionCode = "Mage::getModel('M2ePro/ProductChange')
                                ->clearLastProcessed('{$startDate}',{$maxCountPerOneTime});";

        $shutdownFunction = create_function('', $functionCode);
        register_shutdown_function($shutdownFunction);
    }

    //####################################

    private function getConfig()
    {
        return Mage::helper('M2ePro/Module')->getSynchronizationConfig();
    }

    // ----------------------------------------

    private function getConfigValue($group, $key)
    {
        return $this->getConfig()->getGroupValue($group, $key);
    }

    private function setConfigValue($group, $key, $value)
    {
        return $this->getConfig()->setGroupValue($group, $key, $value);
    }

    //####################################
}