<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Connection_Single extends Ess_M2ePro_Model_Connector_Connection_Abstract
{
    /** @var Ess_M2ePro_Model_Connector_Connection_Request $_request */
    protected $_request = null;

    /** @var Ess_M2ePro_Model_Connector_Connection_Response $_response */
    protected $_response = null;

    protected $_timeout = 300;

    //########################################

    protected function sendRequest()
    {
        $package = array(
            'headers' => $this->getHeaders(),
            'data'    => $this->getBody(),
            'timeout' => $this->getTimeout()
        );

        return Mage::helper('M2ePro/Server_Request')->single(
            $package,
            $this->isCanIgnoreMaintenance()
        );
    }

    protected function processRequestResult(array $result)
    {
        try {
            $this->_response = Mage::getModel('M2ePro/Connector_Connection_Response');
            $this->_response->initFromRawResponse($result['body']);
            $this->_response->setRequestTime($this->_requestTime);
        } catch (Ess_M2ePro_Model_Exception_Connection_InvalidResponse $exception) {
            Mage::helper('M2ePro/Module_Logger')->process($result, 'Invalid Response Format');
            throw new Ess_M2ePro_Model_Exception_Connection($this->getConnectionErrorMessage(), $result);
        }

        if ($this->getResponse()->isServerInMaintenanceMode()) {
            Mage::helper('M2ePro/Server_Maintenance')->processUnexpectedMaintenance();
        }

        if ($this->getResponse()->getMessages()->hasSystemErrorEntity()) {
            throw new Ess_M2ePro_Model_Exception(
                Mage::helper('M2ePro')->__(
                    'Internal Server Error(s) [%error_message%]',
                    $this->getResponse()->getMessages()->getCombinedSystemErrorsString()
                )
            );
        }
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Connector_Connection_Request $request
     * @return $this
     */
    public function setRequest(Ess_M2ePro_Model_Connector_Connection_Request $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Connector_Connection_Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    // ----------------------------------------

    /**
     * @param Ess_M2ePro_Model_Connector_Connection_Response $response
     * @return $this
     */
    public function setResponse(Ess_M2ePro_Model_Connector_Connection_Response $response)
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Connector_Connection_Response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    // ----------------------------------------

    /**
     * @param $value
     * @return $this
     */
    public function setTimeout($value)
    {
        $this->_timeout = (int)$value;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }

    //########################################

    public function getHeaders()
    {
        $command = $this->getRequest()->getCommand();

        return array(
            'M2EPRO-API-VERSION: '.self::API_VERSION,
            'M2EPRO-API-COMPONENT: '.$this->getRequest()->getComponent(),
            'M2EPRO-API-COMPONENT-VERSION: '.$this->getRequest()->getComponentVersion(),
            'M2EPRO-API-COMMAND: /'.$command[0] .'/'.$command[1].'/'.$command[2].'/'
        );
    }

    public function getBody()
    {
        return array(
            'api_version' => self::API_VERSION,
            'request'     => Mage::helper('M2ePro')->jsonEncode($this->getRequest()->getInfo()),
            'data'        => Mage::helper('M2ePro')->jsonEncode($this->getRequest()->getData()),
            'raw_data'    => $this->getRequest()->getRawData()
        );
    }

    //########################################
}
