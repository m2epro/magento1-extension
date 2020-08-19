<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Servicing_Dispatcher
{
    const DEFAULT_INTERVAL = 3600;

    protected $_params = array();
    protected $_initiator;

    //########################################

    public function setInitiator($initiator)
    {
        $this->_initiator = $initiator;
        return $this;
    }

    public function getInitiator()
    {
        return $this->_initiator;
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params = array())
    {
        $this->_params = $params;
    }

    //########################################

    public function process($taskCodes = null)
    {
        $lastUpdate = $this->getLastUpdateDate();
        $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));

        if ($this->getInitiator() !== Ess_M2ePro_Helper_Data::INITIATOR_DEVELOPER &&
            $lastUpdate !== null &&
            $lastUpdate->getTimestamp() + self::DEFAULT_INTERVAL > $currentDate->getTimestamp()
        ) {
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

        /** @var $dispatcherObject Ess_M2ePro_Model_M2ePro_Connector_Dispatcher */
        $dispatcherObject = Mage::getModel('M2ePro/M2ePro_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getConnector(
            'server', 'servicing', 'updateData',
            $this->getRequestData($taskCodes)
        );

        $dispatcherObject->process($connectorObj);
        $responseData = $connectorObj->getResponseData();

        if (!is_array($responseData)) {
            return false;
        }

        $this->dispatchResponseData($responseData, $taskCodes);

        return true;
    }

    //########################################

    protected function getRequestData(array $taskCodes)
    {
        $requestData = array();

        foreach ($this->getRegisteredTasks() as $taskName) {
            if (!in_array($taskName, $taskCodes)) {
                continue;
            }

            $taskModel = $this->getTaskModel($taskName);
            if (!$taskModel->isAllowed()) {
                continue;
            }

            $requestData[$taskModel->getPublicNick()] = $taskModel->getRequestData();
        }

        return $requestData;
    }

    protected function dispatchResponseData(array $responseData, array $taskCodes)
    {
        foreach ($this->getRegisteredTasks() as $taskName) {
            if (!in_array($taskName, $taskCodes)) {
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

    protected function getTaskModel($taskName)
    {
        $taskName = preg_replace_callback(
            '/_([a-z])/i', function($matches) {
            return ucfirst($matches[1]);
            }, $taskName
        );

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

    protected function getLastUpdateDate()
    {
        $lastUpdateDate = Mage::helper('M2ePro/Module')->getRegistry()->getValue('/servicing/last_update_time/');

        if ($lastUpdateDate !== null) {
            $lastUpdateDate = new \DateTime($lastUpdateDate, new \DateTimeZone('UTC'));
        }

        return $lastUpdateDate;
    }

    protected function setLastUpdateDateTime()
    {
        Mage::helper('M2ePro/Module')->getRegistry()->setValue(
            '/servicing/last_update_time/',
            Mage::helper('M2ePro')->getCurrentGmtDate()
        );
    }

    //########################################
}
