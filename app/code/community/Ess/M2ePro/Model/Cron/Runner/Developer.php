<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Cron_Runner_Developer extends Ess_M2ePro_Model_Cron_Runner_Abstract
{
    protected $_allowedTasks = null;

    //########################################

    public function getNick()
    {
        return null;
    }

    public function getInitiator()
    {
        return Ess_M2ePro_Helper_Data::INITIATOR_DEVELOPER;
    }

    //########################################

    public function process()
    {
        session_write_close();
        parent::process();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Cron_Strategy_Abstract
     */
    protected function getStrategyObject()
    {
        $tasks = $this->_allowedTasks;
        empty($tasks) && $tasks = Mage::getSingleton('M2ePro/Cron_Task_Repository')->getRegisteredTasks();

        $strategyObject = Mage::getModel('M2ePro/Cron_Strategy_Serial');
        $strategyObject->setAllowedTasks($tasks);

        return $strategyObject;
    }

    //########################################

    /**
     * @param array $tasks
     * @return $this
     */
    public function setAllowedTasks(array $tasks)
    {
        $this->_allowedTasks = $tasks;
        return $this;
    }

    protected function isPossibleToRun()
    {
        return true;
    }

    protected function canProcessRunner()
    {
        return true;
    }

    //########################################
}
