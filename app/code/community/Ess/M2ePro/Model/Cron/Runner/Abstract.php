<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Cron_Runner_Abstract
{
    const MAX_INACTIVE_TIME = 300;
    const MAX_MEMORY_LIMIT  = 2048;

    protected $_previousStoreId = null;

    /** @var Ess_M2ePro_Model_Cron_OperationHistory $_operationHistory */
    protected $_operationHistory = null;

    //########################################

    abstract public function getNick();

    abstract public function getInitiator();

    //########################################

    public function process()
    {
        if (!$this->canProcess()) {
            return false;
        }

        $runnerSwitcher = Mage::getModel('M2ePro/Cron_Runner_Switcher');
        $runnerSwitcher->check($this);

        Mage::getModel('M2ePro/Cron_Checker_Dispatcher')->process();

        /** @var Ess_M2ePro_Model_Lock_Transactional_Manager $transactionalManager */
        $transactionalManager = Mage::getModel(
            'M2ePro/Lock_Transactional_Manager', array(
                'nick' => 'cron_runner'
            )
        );

        $transactionalManager->lock();

        if (!$this->canProcessRunner()) {
            return false;
        }

        $this->initialize();
        $this->updateLastAccess();

        if (!$this->isPossibleToRun()) {
            $this->deInitialize();
            $transactionalManager->unlock();

            return true;
        }

        $this->updateLastRun();
        $this->beforeStart();

        $transactionalManager->unlock();

        try {

            /** @var Ess_M2ePro_Model_Cron_Strategy_Abstract $strategyObject */
            $strategyObject = $this->getStrategyObject();

            $strategyObject->setInitiator($this->getInitiator());
            $strategyObject->setParentOperationHistory($this->getOperationHistory());

            $result = $strategyObject->process();
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
        $this->deInitialize();

        return $result;
    }

    /**
     * @return Ess_M2ePro_Model_Cron_Strategy_Abstract
     */
    abstract protected function getStrategyObject();

    //########################################

    protected function canProcess()
    {
        if (Mage::helper('M2ePro/Module_Maintenance')->isEnabled()) {
            return false;
        }

        if (Mage::helper('M2ePro/Module')->isDisabled()) {
            return false;
        }

        if (Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/cron/'.$this->getNick().'/', 'disabled')) {
            return false;
        }

        return true;
    }

    protected function canProcessRunner()
    {
        return $this->getNick() === Mage::helper('M2ePro/Module_Cron')->getRunner();
    }

    //########################################

    protected function initialize()
    {
        $this->_previousStoreId = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        Mage::helper('M2ePro/Client')->setMemoryLimit(self::MAX_MEMORY_LIMIT);
        Mage::helper('M2ePro/Module_Exception')->setFatalErrorHandler();
    }

    protected function deInitialize()
    {
        if ($this->_previousStoreId !== null) {
            Mage::app()->setCurrentStore($this->_previousStoreId);
            $this->_previousStoreId = null;
        }
    }

    //########################################

    protected function updateLastAccess()
    {
        $currentDateTime = Mage::helper('M2ePro')->getCurrentGmtDate();
        Mage::helper('M2ePro/Module_Cron')->setLastAccess($currentDateTime);
    }

    protected function isPossibleToRun()
    {
        if (!Mage::helper('M2ePro/Module')->isReadyToWork()) {
            return false;
        }

        if (!Mage::helper('M2ePro/Module_Cron')->isModeEnabled()) {
            return false;
        }

        return true;
    }

    protected function updateLastRun()
    {
        $currentDateTime = Mage::helper('M2ePro')->getCurrentGmtDate();
        Mage::helper('M2ePro/Module_Cron')->setLastRun($currentDateTime);
    }

    // ---------------------------------------

    protected function beforeStart()
    {
        $this->getOperationHistory()->start(
            'cron_runner', null, $this->getInitiator(), $this->getOperationHistoryData()
        );
        $this->getOperationHistory()->makeShutdownFunction();
        $this->getOperationHistory()->cleanOldData();
    }

    protected function afterEnd()
    {
        $this->getOperationHistory()->stop();
    }

    // ---------------------------------------

    protected function getOperationHistoryData()
    {
        return array(
            'runner' => $this->getNick()
        );
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Cron_OperationHistory
     */
    public function getOperationHistory()
    {
        if ($this->_operationHistory !== null) {
            return $this->_operationHistory;
        }

        return $this->_operationHistory = Mage::getModel('M2ePro/Cron_OperationHistory');
    }

    //########################################
}
