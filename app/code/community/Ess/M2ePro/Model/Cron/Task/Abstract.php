<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = NULL;

    private $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;

    /**
     * @var Ess_M2ePro_Model_Lock_Item_Manager
     */
    private $lockItemManager = NULL;

    /**
     * @var Ess_M2ePro_Model_Cron_OperationHistory
     */
    private $operationHistory       = NULL;
    /**
     * @var Ess_M2ePro_Model_Cron_OperationHistory
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

            Mage::dispatchEvent(
                Ess_M2ePro_Model_Cron_Strategy_Abstract::PROGRESS_START_EVENT_NAME,
                array('progress_nick' => $this->getNick())
            );

            $tempResult = $this->performActions();

            if (!is_null($tempResult) && !$tempResult) {
                $result = false;
            }

            Mage::dispatchEvent(
                Ess_M2ePro_Model_Cron_Strategy_Abstract::PROGRESS_STOP_EVENT_NAME,
                array('progress_nick' => $this->getNick())
            );

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
        $this->initiator = (int)$value;
    }

    public function getInitiator()
    {
        return $this->initiator;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Lock_Item_Manager $lockItemManager
     * @return $this
     */
    public function setLockItemManager(Ess_M2ePro_Model_Lock_Item_Manager $lockItemManager)
    {
        $this->lockItemManager = $lockItemManager;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Lock_Item_Manager
     */
    public function getLockItemManager()
    {
        return $this->lockItemManager;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Cron_OperationHistory $object
     * @return $this
     */
    public function setParentOperationHistory(Ess_M2ePro_Model_Cron_OperationHistory $object)
    {
        $this->parentOperationHistory = $object;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Cron_OperationHistory
     */
    public function getParentOperationHistory()
    {
        return $this->parentOperationHistory;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getSynchronizationLog()
    {
        $synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $synchronizationLog->setInitiator($this->initiator);
        $synchronizationLog->setOperationHistoryId($this->getOperationHistory()->getId());

        return $synchronizationLog;
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
               (($startFrom <= $currentTimeStamp && $this->isIntervalExceeded()) ||
                 $this->getInitiator() == Ess_M2ePro_Helper_Data::INITIATOR_DEVELOPER);
    }

    //########################################

    protected function initialize()
    {
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
        if (!is_null($this->operationHistory)) {
            return $this->operationHistory;
        }

        return $this->operationHistory = Mage::getModel('M2ePro/Cron_OperationHistory');
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

    protected function processTaskException(Exception $exception)
    {
        $this->getOperationHistory()->addContentData('exceptions', array(
            'message' => $exception->getMessage(),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'trace'   => $exception->getTraceAsString(),
        ));

        $this->getSynchronizationLog()->addMessage(
            Mage::helper('M2ePro')->__($exception->getMessage()),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
        );

        Mage::helper('M2ePro/Module_Exception')->process($exception);
    }

    protected function processTaskAccountException($message, $file, $line, $trace = null)
    {
        $this->getOperationHistory()->addContentData('exceptions', array(
            'message' => $message,
            'file'    => $file,
            'line'    => $line,
            'trace'   => $trace,
        ));

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