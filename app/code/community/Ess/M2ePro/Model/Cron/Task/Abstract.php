<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Cron_Task_Abstract
{
    private $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;

    /**
     * @var Ess_M2ePro_Model_LockItem
     */
    private $lockItem       = NULL;
    /**
     * @var Ess_M2ePro_Model_LockItem
     */
    private $parentLockItem = NULL;

    /**
     * @var Ess_M2ePro_Model_OperationHistory
     */
    private $operationHistory       = NULL;
    /**
     * @var Ess_M2ePro_Model_OperationHistory
     */
    private $parentOperationHistory = NULL;

    //########################################

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

            $this->getOperationHistory()->setContentData('exception', array(
                'message' => $exception->getMessage(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'trace'   => $exception->getTraceAsString(),
            ));

            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }

        $this->afterEnd();

        return $result;
    }

    // ---------------------------------------

    abstract protected function getNick();

    abstract protected function getMaxMemoryLimit();

    // ---------------------------------------

    abstract protected function performActions();

    //########################################

    public function setInitiator($value)
    {
        $this->initiator = (int)$value;
    }

    public function getInitiator()
    {
        return $this->initiator;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_LockItem $object
     * @return $this
     */
    public function setParentLockItem(Ess_M2ePro_Model_LockItem $object)
    {
        $this->parentLockItem = $object;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_LockItem
     */
    public function getParentLockItem()
    {
        return $this->parentLockItem;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_OperationHistory $object
     * @return $this
     */
    public function setParentOperationHistory(Ess_M2ePro_Model_OperationHistory $object)
    {
        $this->parentOperationHistory = $object;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_OperationHistory
     */
    public function getParentOperationHistory()
    {
        return $this->parentOperationHistory;
    }

    //########################################

    /**
     * @return bool
     */
    public function isPossibleToRun()
    {
        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);

        $startFrom = $this->getConfigValue('start_from');
        $startFrom = !empty($startFrom) ? strtotime($startFrom) : $currentTimeStamp;

        return $this->isModeEnabled() &&
               $startFrom <= $currentTimeStamp &&
               $this->isIntervalExceeded() &&
               !$this->getLockItem()->isExist();
    }

    //########################################

    protected function initialize()
    {
        Mage::helper('M2ePro/Client')->setMemoryLimit($this->getMaxMemoryLimit());
        Mage::helper('M2ePro/Module_Exception')->setFatalErrorHandler();
    }

    protected function updateLastAccess()
    {
        $this->setConfigValue('last_access',Mage::helper('M2ePro')->getCurrentGmtDate());
    }

    protected function updateLastRun()
    {
        $this->setConfigValue('last_run',Mage::helper('M2ePro')->getCurrentGmtDate());
    }

    // ---------------------------------------

    protected function beforeStart()
    {
        $parentId = $this->getParentLockItem() ? $this->getParentLockItem()->getId() : null;
        $this->getLockItem()->create($parentId);
        $this->getLockItem()->makeShutdownFunction();

        $parentId = $this->getParentOperationHistory()
            ? $this->getParentOperationHistory()->getObject()->getId() : null;
        $this->getOperationHistory()->start('cron_task_'.$this->getNick(), $parentId, $this->getInitiator());
        $this->getOperationHistory()->makeShutdownFunction();
    }

    protected function afterEnd()
    {
        $this->getOperationHistory()->stop();
        $this->getLockItem()->remove();
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
        $this->lockItem->setNick('cron_task_'.$this->getNick());

        return $this->lockItem;
    }

    /**
     * @return Ess_M2ePro_Model_OperationHistory
     */
    protected function getOperationHistory()
    {
        if (!is_null($this->operationHistory)) {
            return $this->operationHistory;
        }

        return $this->operationHistory = Mage::getModel('M2ePro/OperationHistory');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    protected function isModeEnabled()
    {
        return (bool)$this->getConfigValue('mode');
    }

    /**
     * @return bool
     */
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

    //########################################

    private function getConfig()
    {
        return Mage::helper('M2ePro/Module')->getConfig();
    }

    private function getConfigGroup()
    {
        return '/cron/task/'.$this->getNick().'/';
    }

    // ---------------------------------------

    private function getConfigValue($key)
    {
        return $this->getConfig()->getGroupValue($this->getConfigGroup(), $key);
    }

    private function setConfigValue($key, $value)
    {
        return $this->getConfig()->setGroupValue($this->getConfigGroup(), $key, $value);
    }

    //########################################
}