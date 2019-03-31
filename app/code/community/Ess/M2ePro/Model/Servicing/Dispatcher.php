<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Servicing_Dispatcher
{
    const DEFAULT_INTERVAL = 3600;

    private $params = array();
    private $forceTasksRunning = false;
    private $initiator;

    //########################################

    public function getForceTasksRunning()
    {
        return $this->forceTasksRunning;
    }

    public function setForceTasksRunning($value)
    {
        $this->forceTasksRunning = (bool)$value;
    }

    // ---------------------------------------

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
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params = array())
    {
        $this->params = $params;
    }

    //########################################

    public function process($minInterval = NULL, $taskCodes = NULL)
    {
        $timeLastUpdate = $this->getLastUpdateTimestamp();

        if ($this->getInitiator() !== Ess_M2ePro_Helper_Data::INITIATOR_DEVELOPER &&
            !is_null($minInterval) &&
            $timeLastUpdate + (int)$minInterval > Mage::helper('M2ePro')->getCurrentGmtDate(true)) {
            return false;
        }

        $this->setLastUpdateDateTime();

        !is_array($taskCodes) && $taskCodes = $this->getRegisteredTasks();
        return $this->processTasks($taskCodes);
    }

    // ---------------------------------------

    public function processTask($taskCode)
    {
        return $this->processTasks(array($taskCode));
    }

    public function processTasks(array $taskCodes)
    {
        Mage::helper('M2ePro/Module_Exception')->setFatalErrorHandler();

        $dispatcherObject = Mage::getModel('M2ePro/M2ePro_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector('servicing','update','data',
                                                               $this->getRequestData($taskCodes));

        $dispatcherObject->process($connectorObj);
        $responseData = $connectorObj->getResponseData();

        if (!is_array($responseData)) {
            return false;
        }

        $this->dispatchResponseData($responseData,$taskCodes);

        return true;
    }

    //########################################

    private function getRequestData(array $taskCodes)
    {
        $requestData = array();

        foreach ($this->getRegisteredTasks() as $taskName) {

            if (!in_array($taskName,$taskCodes)) {
                continue;
            }

            $taskModel = $this->getTaskModel($taskName);

            if (!$this->getForceTasksRunning() && !$taskModel->isAllowed()) {
                continue;
            }

            $requestData[$taskModel->getPublicNick()] = $taskModel->getRequestData();
        }

        return $requestData;
    }

    private function dispatchResponseData(array $responseData, array $taskCodes)
    {
        foreach ($this->getRegisteredTasks() as $taskName) {

            if (!in_array($taskName,$taskCodes)) {
                continue;
            }

            $taskModel = $this->getTaskModel($taskName);

            if (!isset($responseData[$taskModel->getPublicNick()]) ||
                !is_array($responseData[$taskModel->getPublicNick()])) {
                continue;
            }

            $taskModel->processResponseData($responseData[$taskModel->getPublicNick()]);
        }
    }

    //########################################

    private function getTaskModel($taskName)
    {
        $taskName = preg_replace_callback('/_([a-z])/i', function($matches) {
            return ucfirst($matches[1]);
        }, $taskName);

        /** @var $taskModel Ess_M2ePro_Model_Servicing_Task */
        $taskModel = Mage::getModel('M2ePro/Servicing_Task_'.ucfirst($taskName));
        $taskModel->setParams($this->getParams());
        $taskModel->setInitiator($this->getInitiator());

        return $taskModel;
    }

    //########################################

    /**
     * @return array
     */
    public function getRegisteredTasks()
    {
        return array(
            'license',
            'messages',
            'settings',
            'exceptions',
            'marketplaces',
            'cron',
            'statistic',
            'analytics',
            'maintenance_schedule',
            'product_variation_vocabulary'
        );
    }

    /**
     * @return array
     */
    public function getSlowTasks()
    {
        return array(
            'exceptions',
            'statistic',
            'analytics'
        );
    }

    /**
     * @return array
     */
    public function getFastTasks()
    {
        return array_diff($this->getRegisteredTasks(), $this->getSlowTasks());
    }

    // ---------------------------------------

    private function getLastUpdateTimestamp()
    {
        $lastUpdateDate = Mage::helper('M2ePro/Module')->getCacheConfig()
                            ->getGroupValue('/servicing/','last_update_time');

        if (is_null($lastUpdateDate)) {
            return Mage::helper('M2ePro')->getCurrentGmtDate(true) - 3600*24*30;
        }

        return Mage::helper('M2ePro')->getDate($lastUpdateDate,true);
    }

    private function setLastUpdateDateTime()
    {
        Mage::helper('M2ePro/Module')->getCacheConfig()
            ->setGroupValue('/servicing/', 'last_update_time',
                            Mage::helper('M2ePro')->getCurrentGmtDate());
    }

    //########################################
}