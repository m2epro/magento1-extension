<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Connection_Multiple extends Ess_M2ePro_Model_Connector_Connection_Abstract
{
    /** @var Ess_M2ePro_Model_Connector_Connection_Multiple_RequestContainer[] $request */
    protected $_requestsContainers = array();

    /** @var Ess_M2ePro_Model_Connector_Connection_Response[] $response */
    protected $_responses = array();

    protected $_asynchronous = false;

    //########################################

    protected function sendRequest()
    {
        $packages = array();

        foreach ($this->getRequestsContainers() as $key => $requestContainer) {
            $packages[$key] = array(
                'headers' => $this->getHeaders($requestContainer->getRequest()),
                'data'    => $this->getBody($requestContainer->getRequest()),
                'timeout' => $requestContainer->getTimeout()
            );
        }

        return Mage::helper('M2ePro/Server_Request')->multiple(
            $packages,
            $this->isCanIgnoreMaintenance()
        );
    }

    protected function processRequestResult(array $result)
    {
        $successResponses = array();

        foreach ($result as $key => $response) {
            try {
                if ($response['body'] === false) {
                    throw new Ess_M2ePro_Model_Exception_Connection(
                        $this->getConnectionErrorMessage(),
                        array(
                            'curl_error_number'  => $response['curl_error_number'],
                            'curl_error_message' => $response['curl_error_message'],
                            'curl_info'          => $response['curl_info']
                        )
                    );
                }

                $responseObj = Mage::getModel('M2ePro/Connector_Connection_Response');
                $responseObj->initFromRawResponse($response['body']);
                $responseObj->setRequestTime($this->_requestTime);

                $this->_responses[$key] = $responseObj;
                $successResponses[]     = $responseObj;
            } catch (Ess_M2ePro_Model_Exception_Connection_InvalidResponse $exception) {
                $this->_responses[$key] = $this->createFailedResponse($this->getConnectionErrorMessage());
                Mage::helper('M2ePro/Module_Logger')->process($response, 'Invalid Response Format');
            } catch (Exception $exception) {
                $this->_responses[$key] = $this->createFailedResponse($this->getConnectionErrorMessage());
                Mage::helper('M2ePro/Module_Exception')->process($exception);
            }
        }

        foreach ($successResponses as $response) {
            if ($response->isServerInMaintenanceMode()) {
                Mage::helper('M2ePro/Server_Maintenance')->processUnexpectedMaintenance();
            }

            if ($response->getMessages()->hasSystemErrorEntity()) {
                $exception = new Ess_M2ePro_Model_Exception(
                    Mage::helper('M2ePro')->__(
                        'Internal Server Error(s) [%error_message%]',
                        $response->getMessages()->getCombinedSystemErrorsString()
                    )
                );

                Mage::helper('M2ePro/Module_Exception')->process($exception);
            }
        }
    }

    //########################################

    protected function createFailedResponse($errorMessage)
    {
        $messages = array(array(
            Ess_M2ePro_Model_Connector_Connection_Response_Message::CODE_KEY => 0,
            Ess_M2ePro_Model_Connector_Connection_Response_Message::TEXT_KEY => $errorMessage,
            Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_KEY =>
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR,
            Ess_M2ePro_Model_Connector_Connection_Response_Message::SENDER_KEY =>
                Ess_M2ePro_Model_Connector_Connection_Response_Message::SENDER_SYSTEM
        ));

        $failedResponse = Mage::getModel('M2ePro/Connector_Connection_Response');
        $failedResponse->initFromPreparedResponse(array(), $messages);
        $failedResponse->setRequestTime($this->_requestTime);
        return $failedResponse;
    }

    //########################################

    /**
     * @param $key
     * @param Ess_M2ePro_Model_Connector_Connection_Multiple_RequestContainer $requestContainer
     * @return $this
     */
    public function addRequestContainer(
        $key,
        Ess_M2ePro_Model_Connector_Connection_Multiple_RequestContainer $requestContainer
    ) {
        $this->_requestsContainers[$key] = $requestContainer;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Connector_Connection_Multiple_RequestContainer[]
     */
    public function getRequestsContainers()
    {
        return $this->_requestsContainers;
    }

    /**
     * @param $key
     * @return Ess_M2ePro_Model_Connector_Connection_Response
     */
    public function getRequest($key)
    {
        return isset($this->_requestsContainers[$key]) ? $this->_requestsContainers[$key]->getRequest() : null;
    }

    // ----------------------------------------

    /**
     * @param $key
     * @return Ess_M2ePro_Model_Connector_Connection_Response
     */
    public function getResponse($key)
    {
        return isset($this->_responses[$key]) ? $this->_responses[$key] : null;
    }

    /**
     * @return Ess_M2ePro_Model_Connector_Connection_Response[]
     */
    public function getResponses()
    {
        return $this->_responses;
    }

    // ----------------------------------------

    /**
     * @param $flag
     * @return $this
     */
    public function setAsynchronous($flag)
    {
        $this->_asynchronous = (bool)$flag;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAsynchronous()
    {
        return $this->_asynchronous;
    }

    //########################################

    public function getHeaders(Ess_M2ePro_Model_Connector_Connection_Request $request)
    {
        $command = $request->getCommand();

        return array(
            'M2EPRO-API-VERSION: '.self::API_VERSION,
            'M2EPRO-API-COMPONENT: '.$request->getComponent(),
            'M2EPRO-API-COMPONENT-VERSION: '.$request->getComponentVersion(),
            'M2EPRO-API-COMMAND: /'.$command[0] .'/'.$command[1].'/'.$command[2].'/'
        );
    }

    public function getBody(Ess_M2ePro_Model_Connector_Connection_Request $request)
    {
        return array(
            'api_version' => self::API_VERSION,
            'request'     => Mage::helper('M2ePro')->jsonEncode($request->getInfo()),
            'data'        => Mage::helper('M2ePro')->jsonEncode($request->getData()),
            'raw_data'    => $request->getRawData()
        );
    }

    //########################################
}
