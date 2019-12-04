<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Module_Cron extends Mage_Core_Helper_Abstract
{
    const RUNNER_MAGENTO   = 'magento';
    const RUNNER_SERVICE   = 'service';
    const RUNNER_DEVELOPER = 'developer';

    const STRATEGY_SERIAL   = 'serial';
    const STRATEGY_PARALLEL = 'parallel';

    //########################################

    public function isModeEnabled()
    {
        return (bool)$this->getConfigValue('mode');
    }

    //########################################

    public function getRunner()
    {
        return $this->getConfigValue('runner');
    }

    public function setRunner($value)
    {
        if ($this->getRunner() != $value) {
            $this->log(
                "Cron runner was changed from [" . $this->getRunner() . "] to [" . $value . "] - ".
                Mage::helper('M2ePro')->getCurrentGmtDate(), 'cron_runner_change'
            );
        }

        return $this->setConfigValue('runner', $value);
    }

    // ---------------------------------------

    public function isRunnerMagento()
    {
        return $this->getRunner() == self::RUNNER_MAGENTO;
    }

    public function isRunnerService()
    {
        return $this->getRunner() == self::RUNNER_SERVICE;
    }

    //########################################

    public function getLastRunnerChange()
    {
        return $this->getConfigValue('last_runner_change');
    }

    public function setLastRunnerChange($value)
    {
        $this->setConfigValue('last_runner_change', $value);
    }

    // ---------------------------------------

    public function isLastRunnerChangeMoreThan($interval, $isHours = false)
    {
        $isHours && $interval *= 3600;

        $lastRunnerChange = $this->getLastRunnerChange();
        if ($lastRunnerChange === null) {
            return false;
        }

        return Mage::helper('M2ePro')->getCurrentGmtDate(true) > strtotime($lastRunnerChange) + $interval;
    }

    //########################################

    public function getLastAccess()
    {
        return $this->getConfigValue('last_access');
    }

    public function setLastAccess($value)
    {
        return $this->setConfigValue('last_access', $value);
    }

    // ---------------------------------------

    public function isLastAccessMoreThan($interval, $isHours = false)
    {
        $isHours && $interval *= 3600;

        $lastAccess = $this->getLastAccess();
        if ($lastAccess === null) {
            return false;
        }

        return Mage::helper('M2ePro')->getCurrentGmtDate(true) > strtotime($lastAccess) + $interval;
    }

    //########################################

    public function getLastRun()
    {
        return $this->getConfigValue('last_run');
    }

    public function setLastRun($value)
    {
        return $this->setConfigValue('last_run', $value);
    }

    // ---------------------------------------

    public function isLastRunMoreThan($interval, $isHours = false)
    {
        $isHours && $interval *= 3600;

        $lastRun = $this->getLastRun();
        if ($lastRun === null) {
            return false;
        }

        return Mage::helper('M2ePro')->getCurrentGmtDate(true) > strtotime($lastRun) + $interval;
    }

    //########################################

    public function getLastExecutedSlowTask()
    {
        return $this->getConfigValue('last_executed_slow_task');
    }

    public function setLastExecutedSlowTask($taskNick)
    {
        $this->setConfigValue('last_executed_slow_task', $taskNick);
    }

    //########################################

    protected function getConfig()
    {
        return Mage::helper('M2ePro/Module')->getConfig();
    }

    // ---------------------------------------

    protected function getConfigValue($key)
    {
        return $this->getConfig()->getGroupValue('/cron/', $key);
    }

    protected function setConfigValue($key, $value)
    {
        return $this->getConfig()->setGroupValue('/cron/', $key, $value);
    }

    //########################################

    protected function log($message, $type)
    {
        /** @var Ess_M2ePro_Model_Log_System $log */
        $log = Mage::getModel('M2ePro/Log_System');

        $log->setType($type);
        $log->setDescription($message);

        $log->save();
    }

    //########################################
}
