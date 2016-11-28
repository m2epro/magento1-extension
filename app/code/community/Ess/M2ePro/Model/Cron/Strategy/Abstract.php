<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Cron_Strategy_Abstract
{
    private $initiator = null;

    private $allowedTasks = NULL;

    /**
     * @var Ess_M2ePro_Model_OperationHistory
     */
    private $operationHistory = NULL;
    /**
     * @var Ess_M2ePro_Model_OperationHistory
     */
    private $parentOperationHistory = NULL;

    //########################################

    public function setInitiator($initiator)
    {
        $this->initiator = $initiator;
        return $this;
    }

    public function getInitiator()
    {
        return $this->initiator;
    }

    // ---------------------------------------

    /**
     * @param array $tasks
     * @return $this
     */
    public function setAllowedTasks(array $tasks)
    {
        $this->allowedTasks = $tasks;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getAllowedTasks()
    {
        if (!is_null($this->allowedTasks)) {
            return $this->allowedTasks;
        }

        return $this->allowedTasks = array(
            Ess_M2ePro_Model_Cron_Task_RepricingInspectProducts::NICK,
            Ess_M2ePro_Model_Cron_Task_RepricingUpdateSettings::NICK,
            Ess_M2ePro_Model_Cron_Task_RepricingSynchronizationGeneral::NICK,
            Ess_M2ePro_Model_Cron_Task_RepricingSynchronizationActualPrice::NICK,
            Ess_M2ePro_Model_Cron_Task_LogsClearing::NICK,
            Ess_M2ePro_Model_Cron_Task_Servicing::NICK,
            Ess_M2ePro_Model_Cron_Task_Synchronization::NICK
        );
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_OperationHistory $operationHistory
     * @return $this
     */
    public function setParentOperationHistory(Ess_M2ePro_Model_OperationHistory $operationHistory)
    {
        $this->parentOperationHistory = $operationHistory;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_OperationHistory
     */
    public function getParentOperationHistory()
    {
        return $this->parentOperationHistory;
    }

    //########################################

    abstract protected function getNick();

    //########################################

    public function process()
    {
        $this->beforeStart();

        try {

            $result = $this->processTasks();

        } catch (Exception $exception) {

            $result = false;

            $this->getOperationHistory()->setContentData('exception', array(
                'message' => $exception->getMessage(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'trace'   => $exception->getTraceAsString(),
            ));

            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }

        $this->afterEnd();

        return $result;
    }

    // ---------------------------------------

    /**
     * @param $taskNick
     * @return Ess_M2ePro_Model_Cron_Task_Abstract
     */
    protected function getTaskObject($taskNick)
    {
        $taskNick = str_replace('_', ' ', $taskNick);
        $taskNick = str_replace(' ', '', ucwords($taskNick));

        /** @var $task Ess_M2ePro_Model_Cron_Task_Abstract **/
        $task = Mage::getModel('M2ePro/Cron_Task_'.trim($taskNick));

        $task->setInitiator($this->getInitiator());
        $task->setParentOperationHistory($this->getOperationHistory());

        return $task;
    }

    abstract protected function processTasks();

    //########################################

    protected function beforeStart()
    {
        $parentId = $this->getParentOperationHistory()
            ? $this->getParentOperationHistory()->getObject()->getId() : null;
        $this->getOperationHistory()->start('cron_strategy_'.$this->getNick(), $parentId, $this->getInitiator());
        $this->getOperationHistory()->makeShutdownFunction();
    }

    protected function afterEnd()
    {
        $this->getOperationHistory()->stop();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_OperationHistory
     */
    protected function getOperationHistory()
    {
        if (!is_null($this->operationHistory)) {
            return $this->operationHistory;
        }

        return $this->operationHistory = Mage::getModel('M2ePro/OperationHistory');
    }

    //########################################
}