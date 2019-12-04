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
            return true;
        }

        $this->updateLastRun();
        $this->beforeStart();

        $result = true;

        try {
            Mage::dispatchEvent(
                Ess_M2ePro_Model_Cron_Strategy_Abstract::PROGRESS_START_EVENT_NAME,
                array('progress_nick' => $this->getNick())
            );

            $tempResult = $this->performActions();

            if ($tempResult !== null && !$tempResult) {
                $result = false;
            }

            Mage::dispatchEvent(
                Ess_M2ePro_Model_Cron_Strategy_Abstract::PROGRESS_STOP_EVENT_NAME,
                array('progress_nick' => $this->getNick())
            );

            $this->getLockItemManager()->activate();
        } catch (Exception $exception) {
            $result = false;

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

        $this->afterEnd();

        return $result;
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
        if (!$this->isModeEnabled()) {
            return false;
        }

        if ($this->getInitiator() === Ess_M2ePro_Helper_Data::INITIATOR_DEVELOPER) {
            return true;
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
    }

    protected function updateLastAccess()
    {
        $this->setCacheConfigValue('last_access', Mage::helper('M2ePro')->getCurrentGmtDate());
    }

    protected function updateLastRun()
    {
        $this->setCacheConfigValue('last_run', Mage::helper('M2ePro')->getCurrentGmtDate());
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
        $lastRun = $this->getCacheConfigValue('last_run');
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

        $this->getSynchronizationLog()->addMessage(
            Mage::helper('M2ePro')->__($exception->getMessage()),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
        );

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
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
        );
    }

    //########################################

    protected function setRegistryValue($key, $value)
    {
        $registryModel = Mage::getModel('M2ePro/Registry')->load($key, 'key');
        $registryModel->setData('key', $key);
        $registryModel->setData('value', $value);
        $registryModel->save();
    }

    protected function deleteRegistryValue($key)
    {
        $registryModel = Mage::getModel('M2ePro/Registry');
        $registryModel->load($key, 'key');

        if ($registryModel->getId()) {
            $registryModel->delete();
        }
    }

    protected function getRegistryValue($key)
    {
        $registryModel = Mage::getModel('M2ePro/Registry');
        $registryModel->load($key, 'key');

        return $registryModel->getValue();
    }

    //########################################

    protected function getConfig()
    {
        return Mage::helper('M2ePro/Module')->getConfig();
    }

    protected function getCacheConfig()
    {
        return Mage::helper('M2ePro/Module')->getCacheConfig();
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

    // ---------------------------------------

    protected function setCacheConfigValue($key, $value)
    {
        return $this->getCacheConfig()->setGroupValue($this->getConfigGroup(), $key, $value);
    }

    protected function getCacheConfigValue($key)
    {
        return $this->getCacheConfig()->getGroupValue($this->getConfigGroup(), $key);
    }

    //########################################
}
