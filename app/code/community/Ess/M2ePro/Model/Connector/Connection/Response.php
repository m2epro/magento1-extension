<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Connection_Response
{
    protected $_data = array();

    /** @var Ess_M2ePro_Model_Connector_Connection_Response_Message_Set $_messages */
    protected $_messages;

    protected $_resultType = Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_SUCCESS;

    protected $_requestTime;

    //########################################

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

        $this->_data = $response['data'];

        $this->initMessages($response['response']['result']['messages']);
        $this->initResultType($response['response']['result']['type']);
    }

    public function initFromPreparedResponse(array $data = array(), array $messagesData = array(), $resultType = null)
    {
        $this->_data = $data;

        $this->initMessages($messagesData);
        $this->initResultType($resultType);
    }

    //########################################

    public function getResult()
    {
        return $this->_resultType;
    }

    public function getMessages()
    {
        return $this->_messages;
    }

    public function getData()
    {
        return $this->_data;
    }

    //########################################

    public function isResultError()
    {
        return $this->_resultType == Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR;
    }

    public function isResultWarning()
    {
        return $this->_resultType == Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING;
    }

    public function isResultSuccess()
    {
        return $this->_resultType == Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_SUCCESS;
    }

    public function isResultNotice()
    {
        return $this->_resultType == Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_NOTICE;
    }

    //########################################

    public function setRequestTime($requestTime)
    {
        $this->_requestTime = $requestTime;
        return $this;
    }

    public function getRequestTime()
    {
        return $this->_requestTime;
    }

    //########################################

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

    //########################################

    protected function initMessages(array $messagesData)
    {
        $this->_messages = Mage::getModel('M2ePro/Connector_Connection_Response_Message_Set');
        $this->_messages->init($messagesData);
    }

    protected function initResultType($resultType = null)
    {
        if ($resultType !== null) {
            $this->_resultType = $resultType;
            return;
        }

        if ($this->getMessages()->hasErrorEntities()) {
            $this->_resultType = Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR;
            return;
        }

        if ($this->getMessages()->hasWarningEntities()) {
            $this->_resultType = Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING;
            return;
        }

        $this->_resultType = Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_SUCCESS;
    }

    //########################################
}
