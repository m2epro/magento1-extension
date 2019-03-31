<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Cron_Runner_Developer extends Ess_M2ePro_Model_Cron_Runner_Abstract
{
    private $allowedTasks = NULL;

    //########################################

    protected function getNick()
    {
        return NULL;
    }

    protected function getInitiator()
    {
        return Ess_M2ePro_Helper_Data::INITIATOR_DEVELOPER;
    }

    //########################################

    public function process()
    {
        session_write_close();
        return parent::process();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Cron_Strategy_Abstract
     */
    protected function getStrategyObject()
    {
        /** @var Ess_M2ePro_Model_Cron_Strategy_Abstract $strategyObject */
        $strategyObject = Mage::getModel('M2ePro/Cron_Strategy_Serial');

        if (!empty($this->allowedTasks)) {
            $strategyObject->setAllowedTasks($this->allowedTasks);
        }

        return $strategyObject;
    }

    //########################################

    /**
     * @param array $tasks
     * @return $this
     */
    public function setAllowedTasks(array $tasks)
    {
        $this->allowedTasks = $tasks;
        return $this;
    }

    protected function isPossibleToRun()
    {
        return true;
    }

    //########################################
}