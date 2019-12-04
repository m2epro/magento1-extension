<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Runner_Switcher
{
    /** @var array  */
    protected $_runnerPriority = array(
        Ess_M2ePro_Helper_Module_Cron::RUNNER_SERVICE   => 30,
        Ess_M2ePro_Helper_Module_Cron::RUNNER_MAGENTO   => 10,
        Ess_M2ePro_Helper_Module_Cron::RUNNER_DEVELOPER => -1,
    );

    //########################################

    public function check(Ess_M2ePro_Model_Cron_Runner_Abstract $currentRunner)
    {
        $helper = Mage::helper('M2ePro/Module_Cron');

        $currentPriority = $this->getRunnerPriority($currentRunner->getNick());
        $configPriority  = $this->getRunnerPriority($helper->getRunner());

        // switch to a new runner by higher priority
        if ($currentPriority > $configPriority) {
            $helper->setRunner($currentRunner->getNick());
            $helper->setLastRunnerChange(Mage::helper('M2ePro/Data')->getCurrentGmtDate());

            if ($currentRunner instanceof Ess_M2ePro_Model_Cron_Runner_Service) {
                $currentRunner->resetTasksStartFrom();
            }

            return;
        }

        if ($currentRunner instanceof Ess_M2ePro_Model_Cron_Runner_Service &&
            $helper->isLastAccessMoreThan(Ess_M2ePro_Model_Cron_Runner_Abstract::MAX_INACTIVE_TIME)) {
            $currentRunner->resetTasksStartFrom();
        }

        //switch to a new runner by inactivity
        if ($currentPriority < $configPriority && $currentPriority > 0 &&
            $helper->isLastAccessMoreThan(Ess_M2ePro_Model_Cron_Runner_Abstract::MAX_INACTIVE_TIME)) {
            $helper->setRunner($currentRunner->getNick());
            $helper->setLastRunnerChange(Mage::helper('M2ePro/Data')->getCurrentGmtDate());
        }
    }

    //########################################

    protected function getRunnerPriority($nick)
    {
        return isset($this->_runnerPriority[$nick]) ? $this->_runnerPriority[$nick] : -1;
    }

    //########################################
}
