<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Command_RealTime_Virtual extends Ess_M2ePro_Model_Connector_Command_RealTime
{
    // ########################################

    private $command = null;

    private $requestData = array();

    private $responseDataKey = NULL;

    private $requestTimeOut = NULL;

    // ########################################

    public function setCommand(array $command)
    {
        $this->command = $command;
        return $this;
    }

    public function setRequestData(array $requestData)
    {
        $this->requestData = $requestData;
        return $this;
    }

    public function setResponseDataKey($key)
    {
        $this->responseDataKey = $key;
        return $this;
    }

    public function setRequestTimeOut($value)
    {
        $this->requestTimeOut = $value;
        return $this;
    }

    // ########################################

    protected function getCommand()
    {
        if (is_null($this->command)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Command was not set.');
        }

        return $this->command;
    }

    public function getRequestData()
    {
        return $this->requestData;
    }

    // ########################################

    protected function getConnection()
    {
        if (is_null($this->requestTimeOut)) {
            return parent::getConnection();
        }

        $connection = parent::getConnection();
        $connection->setTimeout($this->requestTimeOut);

        return $connection;
    }

    // ########################################

    protected function prepareResponseData()
    {
        $responseData = $this->getResponse()->getData();
        if (is_null($this->responseDataKey)) {
            $this->responseData = $responseData;
            return;
        }

        if (!isset($responseData[$this->responseDataKey])) {
            $this->responseData = $responseData;
            return;
        }

        $this->responseData = $responseData[$this->responseDataKey];
    }

    // ########################################
}