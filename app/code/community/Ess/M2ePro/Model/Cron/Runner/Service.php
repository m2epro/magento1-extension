<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Cron_Runner_Service extends Ess_M2ePro_Model_Cron_Runner_Abstract
{
    private $requestAuthKey      = NULL;
    private $requestConnectionId = NULL;

    //########################################

    protected function getNick()
    {
        return Ess_M2ePro_Helper_Module_Cron::RUNNER_SERVICE;
    }

    protected function getInitiator()
    {
        return Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION;
    }

    //########################################

    public function process()
    {
        if (Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/cron/service/','disabled')) {
            return false;
        }

        return parent::process();
    }

    /**
     * @return Ess_M2ePro_Model_Cron_Strategy_Abstract
     */
    protected function getStrategyObject()
    {
        return Mage::getModel('M2ePro/Cron_Strategy_Parallel');
    }

    //########################################

    public function setRequestAuthKey($value)
    {
        $this->requestAuthKey = $value;
    }

    public function setRequestConnectionId($value)
    {
        $this->requestConnectionId = $value;
    }

    // ---------------------------------------

    public function resetTasksStartFrom()
    {
        $this->resetTaskStartFrom('servicing');
        $this->resetTaskStartFrom('synchronization');
    }

    //########################################

    protected function initialize()
    {
        parent::initialize();

        $helper = Mage::helper('M2ePro/Module_Cron');

        if ($helper->isRunnerService()) {

            $helper->isLastAccessMoreThan(Ess_M2ePro_Helper_Module_Cron::RUNNER_SERVICE_MAX_INACTIVE_TIME) &&
                $this->resetTasksStartFrom();

            return;
        }

        $helper->setRunner(Ess_M2ePro_Helper_Module_Cron::RUNNER_SERVICE);
        $helper->setLastRunnerChange(Mage::helper('M2ePro')->getCurrentGmtDate());

        $this->resetTasksStartFrom();
    }

    protected function isPossibleToRun()
    {
        $authKey = Mage::helper('M2ePro/Module')->getConfig()
                        ->getGroupValue('/cron/service/','auth_key');

        return !is_null($authKey) &&
               !is_null($this->requestAuthKey) &&
               !is_null($this->requestConnectionId) &&
               $authKey == $this->requestAuthKey &&
               parent::isPossibleToRun();
    }

    //########################################

    private function resetTaskStartFrom($taskName)
    {
        $config = Mage::helper('M2ePro/Module')->getConfig();

        $startDate = new DateTime(Mage::helper('M2ePro')->getCurrentGmtDate(), new DateTimeZone('UTC'));
        $shift = 60 + rand(0,(int)$config->getGroupValue('/cron/task/'.$taskName.'/','interval'));
        $startDate->modify('+'.$shift.' seconds');

        $config->setGroupValue('/cron/task/'.$taskName.'/','start_from',$startDate->format('Y-m-d H:i:s'));
    }

    //########################################
}