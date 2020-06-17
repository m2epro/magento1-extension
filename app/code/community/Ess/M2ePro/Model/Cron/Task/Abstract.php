<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = null;

    protected $_initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;

    /**
     * @var int (in seconds)
     */
    protected $_interval = 60;

    /**
     * @var Ess_M2ePro_Model_Lock_Item_Manager
     */
    protected $_lockItemManager;

    /**
     * @var Ess_M2ePro_Model_Cron_OperationHistory
     */
    protected $_operationHistory;
    /**
     * @var Ess_M2ePro_Model_Cron_OperationHistory
     */
    protected $_parentOperationHistory;

    //########################################

    public function process()
    {
        $this->initialize();
        $this->updateLastAccess();

        if (!$this->isPossibleToRun()) {
            return;
        }

        $this->updateLastRun();
        $this->beforeStart();

        try {
            Mage::dispatchEvent(
                Ess_M2ePro_Model_Cron_Strategy_Abstract::PROGRESS_START_EVENT_NAME,
                array('progress_nick' => $this->getNick())
            );

            $this->performActions();

            Mage::dispatchEvent(
                Ess_M2ePro_Model_Cron_Strategy_Abstract::PROGRESS_STOP_EVENT_NAME,
                array('progress_nick' => $this->getNick())
            );
        } catch (Exception $exception) {
            $this->processTaskException($exception);
        }

        $this->afterEnd();
    }

    // ---------------------------------------

    abstract protected function performActions();

    //########################################

    protected function getNick()
    {
        $nick = static::NICK;
        if (empty($nick)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Task NICK is not defined.');
        }

        return $nick;
    }

    // ---------------------------------------

    public function setInitiator($value)
    {
        $this->_initiator = (int)$value;
    }

    public function getInitiator()
    {
        return $this->_initiator;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Lock_Item_Manager $lockItemManager
     * @return $this
     */
    public function setLockItemManager(Ess_M2ePro_Model_Lock_Item_Manager $lockItemManager)
    {
        $this->_lockItemManager = $lockItemManager;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Lock_Item_Manager
     */
    public function getLockItemManager()
    {
        return $this->_lockItemManager;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Cron_OperationHistory $object
     * @return $this
     */
    public function setParentOperationHistory(Ess_M2ePro_Model_Cron_OperationHistory $object)
    {
        $this->_parentOperationHistory = $object;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Cron_OperationHistory
     */
    public function getParentOperationHistory()
    {
        return $this->_parentOperationHistory;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getSynchronizationLog()
    {
        $synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $synchronizationLog->setInitiator($this->_initiator);
        $synchronizationLog->setOperationHistoryId($this->getOperationHistory()->getId());

        return $synchronizationLog;
    }

    //########################################

    /**
     * @return bool
     */
    public function isPossibleToRun()
    {
        if ($this->getInitiator() === Ess_M2ePro_Helper_Data::INITIATOR_DEVELOPER) {
            return true;
        }

        if (!$this->isModeEnabled()) {
            return false;
        }

        if ($this->isComponentDisabled()) {
            return false;
        }

        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);

        $startFrom = $this->getConfigValue('start_from');
        $startFrom = !empty($startFrom) ? strtotime($startFrom) : $currentTimeStamp;

        return $startFrom <= $currentTimeStamp && $this->isIntervalExceeded();
    }

    //########################################

    protected function initialize()
    {
        Mage::helper('M2ePro/Module_Exception')->setFatalErrorHandler();
        Mage::getModel('M2ePro/Synchronization_Log')->setFatalErrorHandler();
    }

    protected function updateLastAccess()
    {
        $this->setConfigValue('last_access', Mage::helper('M2ePro')->getCurrentGmtDate());
    }

    protected function updateLastRun()
    {
        Mage::helper('M2ePro/Module')->setRegistryValue(
            $this->getConfigGroup() . 'last_run/',
            Mage::helper('M2ePro')->getCurrentGmtDate()
        );
    }

    // ---------------------------------------

    protected function beforeStart()
    {
        $parentId = $this->getParentOperationHistory()
            ? $this->getParentOperationHistory()->getObject()->getId() : null;
        $nick = str_replace("/", "_", $this->getNick());
        $this->getOperationHistory()->start('cron_task_'.$nick, $parentId, $this->getInitiator());
        $this->getOperationHistory()->makeShutdownFunction();
    }

    protected function afterEnd()
    {
        $this->getOperationHistory()->stop();
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

    // ---------------------------------------

    /**
     * @return bool
     */
    protected function isModeEnabled()
    {
        $mode = $this->getConfigValue('mode');
        if ($mode !== null) {
            return (bool)$mode;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function isIntervalExceeded()
    {
        $lastRun = Mage::helper('M2ePro/Module')->getRegistryValue($this->getConfigGroup() . 'last_run/');
        if ($lastRun === null) {
            return true;
        }

        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        return $currentTimeStamp > strtotime($lastRun) + $this->getInterval();
    }

    public function getInterval()
    {
        $interval = $this->getConfigValue('interval');
        return $interval === null ? $this->_interval : (int)$interval;
    }

    public function isComponentDisabled()
    {
        if (count(Mage::helper('M2ePro/Component')->getEnabledComponents()) === 0) {
            return true;
        }

        /** @var Ess_M2ePro_Model_Cron_Task_Repository $taskRepo */
        $taskRepo = Mage::getSingleton('M2ePro/Cron_Task_Repository');
        return in_array(
            $taskRepo->getTaskComponent($this->getNick()),
            Mage::helper('M2ePro/Component')->getDisabledComponents(),
            true
        );
    }

    //########################################

    protected function processTaskException(Exception $exception)
    {
        $this->getOperationHistory()->addContentData(
            'exceptions', array(
                'message' => $exception->getMessage(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'trace'   => $exception->getTraceAsString(),
            )
        );

        $this->getSynchronizationLog()->addMessageFromException($exception);

        Mage::helper('M2ePro/Module_Exception')->process($exception);
    }

    protected function processTaskAccountException($message, $file, $line, $trace = null)
    {
        $this->getOperationHistory()->addContentData(
            'exceptions', array(
                'message' => $message,
                'file'    => $file,
                'line'    => $line,
                'trace'   => $trace,
            )
        );

        $this->getSynchronizationLog()->addMessage(
            $message,
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
        );
    }

    //########################################

    protected function getConfig()
    {
        return Mage::helper('M2ePro/Module')->getConfig();
    }

    protected function getConfigGroup()
    {
        return '/cron/task/'.$this->getNick().'/';
    }

    // ---------------------------------------

    protected function setConfigValue($key, $value)
    {
        return $this->getConfig()->setGroupValue($this->getConfigGroup(), $key, $value);
    }

    protected function getConfigValue($key)
    {
        return $this->getConfig()->getGroupValue($this->getConfigGroup(), $key);
    }

    //########################################
}
