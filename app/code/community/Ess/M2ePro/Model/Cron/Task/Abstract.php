<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Cron_Task_Abstract
{
    private $lockItem = NULL;
    private $operationHistory = NULL;

    private $parentLockItem = NULL;
    private $parentOperationHistory = NULL;

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

            $tempResult = $this->performActions();

            if (!is_null($tempResult) && !$tempResult) {
                $result = false;
            }

            $this->getLockItem()->activate();

        } catch (Exception $exception) {

            $result = false;

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->getOperationHistory()->setContentData('exception', array(
                'message' => $exception->getMessage(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'trace'   => $exception->getTraceAsString(),
            ));
        }

        $this->afterEnd();

        return $result;
    }

    // ----------------------------------

    abstract protected function getNick();

    abstract protected function getMaxMemoryLimit();

    // ----------------------------------

    abstract protected function performActions();

    //####################################

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

    // -----------------------------------

    public function setInitiator($value)
    {
        $this->initiator = (int)$value;
    }

    public function getInitiator()
    {
        return $this->initiator;
    }

    //####################################

    protected function initialize()
    {
        Mage::helper('M2ePro/Client')->setMemoryLimit($this->getMaxMemoryLimit());
        Mage::helper('M2ePro/Module_Exception')->setFatalErrorHandler();
    }

    protected function updateLastAccess()
    {
        $this->setConfigValue('last_access',Mage::helper('M2ePro')->getCurrentGmtDate());
    }

    protected function isPossibleToRun()
    {
        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);

        $startFrom = $this->getConfigValue('start_from');
        $startFrom = !empty($startFrom) ? strtotime($startFrom) : $currentTimeStamp;

        return $this->isModeEnabled() &&
               $startFrom <= $currentTimeStamp &&
               $this->isIntervalExceeded() &&
               !$this->getLockItem()->isExist();
    }

    protected function updateLastRun()
    {
        $this->setConfigValue('last_run',Mage::helper('M2ePro')->getCurrentGmtDate());
    }

    // -----------------------------------

    protected function beforeStart()
    {
        $lockItemParentId = $this->getParentLockItem() ? $this->getParentLockItem()->getRealId() : NULL;
        $this->getLockItem()->create($lockItemParentId);
        $this->getLockItem()->makeShutdownFunction();

        $operationHistoryParentId = $this->getParentOperationHistory() ?
                $this->getParentOperationHistory()->getObject()->getId() : NULL;
        $this->getOperationHistory()->start('cron_'.$this->getNick(),
                                            $operationHistoryParentId,
                                            $this->getInitiator());
        $this->getOperationHistory()->makeShutdownFunction();
    }

    protected function afterEnd()
    {
        $this->getOperationHistory()->stop();
        $this->getLockItem()->remove();
    }

    //####################################

    protected function getLockItem()
    {
        if (is_null($this->lockItem)) {
            $this->lockItem = Mage::getModel('M2ePro/LockItem');
            $this->lockItem->setNick('cron_'.$this->getNick());
        }
        return $this->lockItem;
    }

    protected function getOperationHistory()
    {
        if (is_null($this->operationHistory)) {
            $this->operationHistory = Mage::getModel('M2ePro/OperationHistory');
        }
        return $this->operationHistory;
    }

    // -----------------------------------

    protected function isModeEnabled()
    {
        return (bool)$this->getConfigValue('mode');
    }

    protected function isIntervalExceeded()
    {
        $lastRun = $this->getConfigValue('last_run');

        if (is_null($lastRun)) {
            return true;
        }

        $interval = (int)$this->getConfigValue('interval');
        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);

        return $currentTimeStamp > strtotime($lastRun) + $interval;
    }

    //####################################

    private function getConfig()
    {
        return Mage::helper('M2ePro/Module')->getConfig();
    }

    private function getConfigGroup()
    {
        return '/cron/task/'.$this->getNick().'/';
    }

    // ----------------------------------------

    private function getConfigValue($key)
    {
        return $this->getConfig()->getGroupValue($this->getConfigGroup(), $key);
    }

    private function setConfigValue($key, $value)
    {
        return $this->getConfig()->setGroupValue($this->getConfigGroup(), $key, $value);
    }

    //####################################
}