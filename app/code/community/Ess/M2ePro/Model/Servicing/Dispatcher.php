<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Servicing_Dispatcher
{
    const DEFAULT_INTERVAL = 3600;
    const MAX_MEMORY_LIMIT = 256;

    private $params = array();
    private $forceTasksRunning = false;

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

        if (!is_null($minInterval) &&
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
        Mage::helper('M2ePro/Client')->setMemoryLimit(self::MAX_MEMORY_LIMIT);
        Mage::helper('M2ePro/Module_Exception')->setFatalErrorHandler();

        $dispatcherObject = Mage::getModel('M2ePro/Connector_M2ePro_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector('servicing','update','data',
                                                               $this->getRequestData($taskCodes));

        $responseData = $dispatcherObject->process($connectorObj);

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

            /** @var $taskModel Ess_M2ePro_Model_Servicing_Task */
            $taskModel = Mage::getModel('M2ePro/Servicing_Task_'.ucfirst($taskName));
            $taskModel->setParams($this->getParams());

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

            /** @var $taskModel Ess_M2ePro_Model_Servicing_Task */
            $taskModel = Mage::getModel('M2ePro/Servicing_Task_'.ucfirst($taskName));
            $taskModel->setParams($this->getParams());

            if (!isset($responseData[$taskModel->getPublicNick()]) ||
                !is_array($responseData[$taskModel->getPublicNick()])) {
                continue;
            }

            $taskModel->processResponseData($responseData[$taskModel->getPublicNick()]);
        }
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
            'backups',
            'exceptions',
            'marketplaces',
            'cron',
            'statistic'
        );
    }

    /**
     * @return array
     */
    public function getSlowTasks()
    {
        return array(
            'backups',
            'exceptions',
            'statistic'
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