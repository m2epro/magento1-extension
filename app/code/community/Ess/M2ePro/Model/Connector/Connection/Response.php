<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Connection_Response
{
    private $data = array();

    /** @var Ess_M2ePro_Model_Connector_Connection_Response_Message_Set $messages */
    private $messages = NULL;

    private $resultType = Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_SUCCESS;

    private $requestTime = NULL;

    // ########################################

    public function initFromRawResponse($response)
    {
        $response = Mage::helper('M2ePro')->jsonDecode($response);

        if (!is_array($response) ||
            !isset($response['data']) || !is_array($response['data']) ||
            !isset($response['response']['result']['messages']) ||
            !is_array($response['response']['result']['messages']) ||
            !isset($response['response']['result']['type'])) {

            throw new Ess_M2ePro_Model_Exception_Connection_InvalidResponse('Invalid Response Format.');
        }

        $this->data = $response['data'];

        $this->initMessages($response['response']['result']['messages']);
        $this->initResultType($response['response']['result']['type']);
    }

    public function initFromPreparedResponse(array $data = array(), array $messagesData = array(), $resultType = NULL)
    {
        $this->data = $data;

        $this->initMessages($messagesData);
        $this->initResultType($resultType);
    }

    // ########################################

    public function getResult()
    {
        return $this->resultType;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function getData()
    {
        return $this->data;
    }

    // ########################################

    public function isResultError()
    {
        return $this->resultType == Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR;
    }

    public function isResultWarning()
    {
        return $this->resultType == Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING;
    }

    public function isResultSuccess()
    {
        return $this->resultType == Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_SUCCESS;
    }

    public function isResultNotice()
    {
        return $this->resultType == Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_NOTICE;
    }

    // ########################################

    public function setRequestTime($requestTime)
    {
        $this->requestTime = $requestTime;
        return $this;
    }

    public function getRequestTime()
    {
        return $this->requestTime;
    }

    // ########################################

    public function isServerInMaintenanceMode()
    {
        if (!$this->getMessages()->hasSystemErrorEntity()) {
            return false;
        }

        foreach ($this->getMessages()->getErrorEntities() as $message) {

            if (!$message->isSenderSystem()) {
                continue;
            }

            if ($message->getCode() == 3) {
                return true;
            }
        }

        return false;
    }

    // ########################################

    private function initMessages(array $messagesData)
    {
        $this->messages = Mage::getModel('M2ePro/Connector_Connection_Response_Message_Set');
        $this->messages->init($messagesData);
    }

    private function initResultType($resultType = NULL)
    {
        if (!is_null($resultType)) {
            $this->resultType = $resultType;
            return;
        }

        if ($this->getMessages()->hasErrorEntities()) {
            $this->resultType = Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR;
            return;
        }

        if ($this->getMessages()->hasWarningEntities()) {
            $this->resultType = Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING;
            return;
        }

        $this->resultType = Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_SUCCESS;
    }

    // ########################################
}