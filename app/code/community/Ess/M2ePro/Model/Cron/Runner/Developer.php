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

        if (!empty($this->_allowedTasks)) {
            $strategyObject->setAllowedTasks($this->_allowedTasks);
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
        $this->_allowedTasks = $tasks;
        return $this;
    }

    protected function isPossibleToRun()
    {
        return true;
    }

    //########################################
}
