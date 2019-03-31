<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Command_Pending_Responser
{
    protected $params = array();

    /** @var Ess_M2ePro_Model_Connector_Connection_Response $response */
    protected $response = NULL;

    protected $preparedResponseData = array();

    // ########################################

    public function __construct(array $params = array(), Ess_M2ePro_Model_Connector_Connection_Response $response)
    {
        $this->params   = $params;
        $this->response = $response;
    }

    // ########################################

    protected function getResponse()
    {
        return $this->response;
    }

    // ########################################

    public function process()
    {
        $this->processResponseMessages();

        if (!$this->isNeedProcessResponse()) {
            return NULL;
        }

        if (!$this->validateResponse()) {
            throw new Ess_M2ePro_Model_Exception('Validation Failed. The Server response data is not valid.');
        }

        $this->prepareResponseData();
        $this->processResponseData();

        return $this->getPreparedResponseData();
    }

    // ########################################

    public function getPreparedResponseData()
    {
        return $this->preparedResponseData;
    }

    // ########################################

    public function failDetected($messageText) {}

    public function eventAfterExecuting() {}

    //-----------------------------------------

    protected function isNeedProcessResponse()
    {
        return true;
    }

    abstract protected function validateResponse();

    protected function prepareResponseData()
    {
        $this->preparedResponseData = $this->getResponse()->getData();
    }

    abstract protected function processResponseData();

    // ########################################

    protected function processResponseMessages() {}

    // ########################################
}