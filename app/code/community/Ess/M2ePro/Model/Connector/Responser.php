<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Responser
{
    protected $params = array();

    protected $messages = array();
    protected $resultType = Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_ERROR;

    protected $parsedResponseData = array();

    //########################################

    public function __construct(array $params = array())
    {
        $this->params = $params;
    }

    //########################################

    public function process(array $responseBody = array(), array $messages = array())
    {
        $this->processResponseMessages($messages);

        if (!$this->isNeedToParseResponseData($responseBody)) {
            return;
        }

        if (!$this->validateResponseData($responseBody)) {
            throw new Ess_M2ePro_Model_Exception('Validation Failed. The Server response data is not valid.');
        }

        $this->parsedResponseData = $this->prepareResponseData($responseBody);
        $this->processResponseData($this->parsedResponseData);
    }

    //########################################

    /**
     * @return array
     */
    public function getParsedResponseData()
    {
        return $this->parsedResponseData;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Processing_Request $processingRequest
     */
    public function unsetProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest) {}

    public function eventAfterProcessing() {}

    // ---------------------------------------

    public function eventFailedExecuting($message) {}

    public function eventAfterExecuting() {}

    // ---------------------------------------

    protected function isNeedToParseResponseData($responseBody)
    {
        return true;
    }

    abstract protected function validateResponseData($response);

    protected function prepareResponseData($response)
    {
        return $response;
    }

    abstract protected function processResponseData($response);

    //########################################

    protected function processResponseMessages(array $messages = array())
    {
        $this->resultType = $this->getResultType($messages);

        $internalServerErrorMessage = array();

        foreach ($messages as $message) {

            if ($this->isMessageError($message) && $this->isMessageSenderSystem($message)) {
                $internalServerErrorMessage[] = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY];
                continue;
            }

            $this->messages[] = $message;
        }

        if (!empty($internalServerErrorMessage)) {
            throw new Ess_M2ePro_Model_Exception(Mage::helper('M2ePro')->__(
                "Internal Server Error(s) [%error_message%]", implode(', ', $internalServerErrorMessage)
            ));
        }
    }

    /**
     * @param array $messages
     * @return string
     */
    public function getResultType(array $messages = array())
    {
        $types = array();

        foreach ($messages as $message) {
            $types[] = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_KEY];
        }

        if (in_array(Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_ERROR,$types)) {
            return Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_ERROR;
        }
        if (in_array(Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_WARNING,$types)) {
            return Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_WARNING;
        }

        return Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_SUCCESS;
    }

    //########################################

    /**
     * @param array $message
     * @return bool
     */
    public function isMessageError($message)
    {
        $type = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_KEY];
        return $type == Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_ERROR;
    }

    /**
     * @param array $message
     * @return bool
     */
    public function isMessageWarning($message)
    {
        $type = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_KEY];
        return $type == Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_WARNING;
    }

    /**
     * @param array $message
     * @return bool
     */
    public function isMessageSenderSystem($message)
    {
        $sender = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_KEY];
        return $sender == Ess_M2ePro_Model_Connector_Protocol::MESSAGE_SENDER_SYSTEM;
    }

    /**
     * @param array $message
     * @return bool
     */
    public function isMessageSenderComponent($message)
    {
        $sender = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_SENDER_KEY];
        return $sender == Ess_M2ePro_Model_Connector_Protocol::MESSAGE_SENDER_COMPONENT;
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getErrorMessages()
    {
        $messages = array();

        foreach ($this->messages as $message) {
            $this->isMessageError($message) && $messages[] = $message;
        }

        return $messages;
    }

    /**
     * @return array
     */
    public function getWarningMessages()
    {
        $messages = array();

        foreach ($this->messages as $message) {
            $this->isMessageWarning($message) && $messages[] = $message;
        }

        return $messages;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasErrorMessages()
    {
        return count($this->getErrorMessages()) > 0;
    }

    /**
     * @return bool
     */
    public function hasWarningMessages()
    {
        return count($this->getWarningMessages()) > 0;
    }

    // ---------------------------------------

    /**
     * @return null|string
     */
    public function getCombinedErrorMessage()
    {
        $messages = array();

        foreach ($this->getErrorMessages() as $message) {
            $messages[] = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY];
        }

        return !empty($messages) ? implode(', ', $messages) : null;
    }

    //########################################
}