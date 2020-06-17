<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Cron_Runner_Service extends Ess_M2ePro_Model_Cron_Runner_Abstract
{
    protected $_requestAuthKey      = null;
    protected $_requestConnectionId = null;

    //########################################

    public function getNick()
    {
        return Ess_M2ePro_Helper_Module_Cron::RUNNER_SERVICE;
    }

    public function getInitiator()
    {
        return Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION;
    }

    //########################################

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
        $this->_requestAuthKey = $value;
    }

    public function setRequestConnectionId($value)
    {
        $this->_requestConnectionId = $value;
    }

    // ---------------------------------------

    public function resetTasksStartFrom()
    {
        $this->resetTaskStartFrom(Ess_M2ePro_Model_Cron_Task_System_Servicing_Synchronize::NICK);
    }

    //########################################

    protected function isPossibleToRun()
    {
        $authKey = Mage::helper('M2ePro/Module')->getConfig()
                        ->getGroupValue('/cron/service/', 'auth_key');

        return $authKey !== null &&
               $this->_requestAuthKey !== null &&
               $this->_requestConnectionId !== null &&
               $authKey == $this->_requestAuthKey &&
               parent::isPossibleToRun();
    }

    //########################################

    protected function getOperationHistoryData()
    {
        return array_merge(
            parent::getOperationHistoryData(), array(
                'auth_key'      => $this->_requestAuthKey,
                'connection_id' => $this->_requestConnectionId
            )
        );
    }

    //########################################

    protected function resetTaskStartFrom($taskName)
    {
        $config = Mage::helper('M2ePro/Module')->getConfig();

        $startDate = new DateTime(Mage::helper('M2ePro')->getCurrentGmtDate(), new DateTimeZone('UTC'));
        $shift = 60 + rand(0, (int)$config->getGroupValue('/cron/task/'.$taskName.'/', 'interval'));
        $startDate->modify('+'.$shift.' seconds');

        $config->setGroupValue('/cron/task/'.$taskName.'/', 'start_from', $startDate->format('Y-m-d H:i:s'));
    }

    //########################################
}
