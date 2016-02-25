<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Cron_Runner_Magento extends Ess_M2ePro_Model_Cron_Runner_Abstract
{
    const MIN_DISTRIBUTION_EXECUTION_TIME = 300;
    const MAX_DISTRIBUTION_WAIT_INTERVAL  = 59;

    //########################################

    protected function getNick()
    {
        return Ess_M2ePro_Helper_Module_Cron::RUNNER_MAGENTO;
    }

    protected function getInitiator()
    {
        return Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;
    }

    //########################################

    public function process()
    {
        if (Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/cron/magento/','disabled')) {
            return false;
        }

        return parent::process();
    }

    /**
     * @return Ess_M2ePro_Model_Cron_Strategy_Abstract
     */
    protected function getStrategyObject()
    {
        return Mage::getModel('M2ePro/Cron_Strategy_Serial');
    }

    //########################################

    protected function initialize()
    {
        usleep(rand(0,2000000));

        parent::initialize();

        $helper = Mage::helper('M2ePro/Module_Cron');

        if ($helper->isRunnerMagento()) {
            return;
        }

        if ($helper->isLastRunMoreThan(Ess_M2ePro_Helper_Module_Cron::RUNNER_SERVICE_MAX_INACTIVE_TIME)) {

            $helper->setRunner(Ess_M2ePro_Helper_Module_Cron::RUNNER_MAGENTO);
            $helper->setLastRunnerChange(Mage::helper('M2ePro')->getCurrentGmtDate());
        }
    }

    protected function isPossibleToRun()
    {
        return is_null(Mage::helper('M2ePro/Data_Global')->getValue('cron_running')) &&
               parent::isPossibleToRun();
    }

    // ---------------------------------------

    protected function beforeStart()
    {
        /*
         * Magento can execute M2ePro cron multiple times in same php process.
         * It can cause problems with items that were cached in first execution.
         */
        // ---------------------------------------
        Mage::helper('M2ePro/Data_Global')->setValue('cron_running',true);
        // ---------------------------------------

        parent::beforeStart();
        $this->distributeLoadIfNeed();
    }

    //########################################

    private function distributeLoadIfNeed()
    {
        if (Mage::helper('M2ePro/Module')->isDevelopmentEnvironment()) {
            return;
        }

        $maxExecutionTime = (int)@ini_get('max_execution_time');

        if ($maxExecutionTime <= 0 || $maxExecutionTime < self::MIN_DISTRIBUTION_EXECUTION_TIME) {
            return;
        }

        sleep(rand(0,self::MAX_DISTRIBUTION_WAIT_INTERVAL));
    }

    //########################################
}