<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Cron_Checker_Task_Abstract
{
    const NICK = NULL;

    //########################################

    abstract public function performActions();

    //########################################

    public function process()
    {
        if (!$this->isPossibleToRun()) {
            return;
        }

        $this->updateLastRun();

        $this->performActions();
    }

    //########################################

    protected function getNick()
    {
        $nick = static::NICK;
        if (empty($nick)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Task NICK is not defined.');
        }

        return $nick;
    }

    //########################################

    /**
     * @return bool
     */
    public function isPossibleToRun()
    {
        return $this->isIntervalExceeded();
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

    // ---------------------------------------

    protected function updateLastRun()
    {
        $this->setConfigValue('last_run', Mage::helper('M2ePro')->getCurrentGmtDate());
    }

    //########################################

    private function getConfig()
    {
        return Mage::helper('M2ePro/Module')->getConfig();
    }

    private function getConfigGroup()
    {
        return '/cron/checker/task/'.$this->getNick().'/';
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