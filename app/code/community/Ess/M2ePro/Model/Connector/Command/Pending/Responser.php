<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Command_Pending_Responser
{
    protected $_params = array();

    /** @var Ess_M2ePro_Model_Connector_Connection_Response $_response */
    protected $_response = null;

    protected $_preparedResponseData = array();

    //########################################

    public function __construct(array $params = array(), Ess_M2ePro_Model_Connector_Connection_Response $response)
    {
        $this->_params   = $params;
        $this->_response = $response;
    }

    //########################################

    protected function getResponse()
    {
        return $this->_response;
    }

    //########################################

    public function process()
    {
        $this->processResponseMessages();

        if (!$this->isNeedProcessResponse()) {
            return null;
        }

        if (!$this->validateResponse()) {
            throw new Ess_M2ePro_Model_Exception('Validation Failed. The Server response data is not valid.');
        }

        $this->prepareResponseData();
        $this->processResponseData();

        return $this->getPreparedResponseData();
    }

    //########################################

    public function getPreparedResponseData()
    {
        return $this->_preparedResponseData;
    }

    //########################################

    public function failDetected($messageText)
    {
        return null;
    }

    public function eventAfterExecuting()
    {
        return null;
    }

    //-----------------------------------------

    protected function isNeedProcessResponse()
    {
        return true;
    }

    abstract protected function validateResponse();

    protected function prepareResponseData()
    {
        $this->_preparedResponseData = $this->getResponse()->getData();
    }

    abstract protected function processResponseData();

    //########################################

    protected function processResponseMessages()
    {
        return null;
    }

    //########################################
}
