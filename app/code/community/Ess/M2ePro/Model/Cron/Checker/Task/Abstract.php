<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Cron_Checker_Task_Abstract
{
    const NICK = null;

    /**
     * @var int (in seconds)
     */
    protected $_interval = 3600;

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

    // ---------------------------------------

    protected function updateLastRun()
    {
        $this->setCacheConfigValue('last_run', Mage::helper('M2ePro')->getCurrentGmtDate());
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
        return '/cron/checker/task/'.$this->getNick().'/';
    }

    // ---------------------------------------

    protected function getConfigValue($key)
    {
        return $this->getConfig()->getGroupValue($this->getConfigGroup(), $key);
    }

    protected function setConfigValue($key, $value)
    {
        return $this->getConfig()->setGroupValue($this->getConfigGroup(), $key, $value);
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