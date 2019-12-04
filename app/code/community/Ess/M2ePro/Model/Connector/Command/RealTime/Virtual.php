<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Command_RealTime_Virtual extends Ess_M2ePro_Model_Connector_Command_RealTime
{
    protected $_command = null;

    protected $_requestData = array();

    protected $_responseDataKey = null;

    protected $_requestTimeOut = null;

    //########################################

    public function setCommand(array $command)
    {
        $this->_command = $command;
        return $this;
    }

    public function setRequestData(array $requestData)
    {
        $this->_requestData = $requestData;
        return $this;
    }

    public function setResponseDataKey($key)
    {
        $this->_responseDataKey = $key;
        return $this;
    }

    public function setRequestTimeOut($value)
    {
        $this->_requestTimeOut = $value;
        return $this;
    }

    //########################################

    protected function getCommand()
    {
        if ($this->_command === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Command was not set.');
        }

        return $this->_command;
    }

    public function getRequestData()
    {
        return $this->_requestData;
    }

    //########################################

    protected function getConnection()
    {
        if ($this->_requestTimeOut === null) {
            return parent::getConnection();
        }

        $connection = parent::getConnection();
        $connection->setTimeout($this->_requestTimeOut);

        return $connection;
    }

    //########################################

    protected function prepareResponseData()
    {
        $responseData = $this->getResponse()->getData();
        if ($this->_responseDataKey === null) {
            $this->_responseData = $responseData;
            return;
        }

        if (!isset($responseData[$this->_responseDataKey])) {
            $this->_responseData = $responseData;
            return;
        }

        $this->_responseData = $responseData[$this->_responseDataKey];
    }

    //########################################
}