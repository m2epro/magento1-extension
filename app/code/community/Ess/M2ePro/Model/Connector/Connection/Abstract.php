<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Connection_Abstract
{
    const API_VERSION = 1;

    protected $_requestTime = null;

    protected $_serverBaseUrl  = null;
    protected $_serverHostName = null;

    protected $_tryToResendOnError         = true;
    protected $_tryToSwitchEndpointOnError = true;
    protected $_canIgnoreMaintenance       = false;

    //########################################

    public function process()
    {
        try {
            $this->_requestTime = Mage::helper('M2ePro')->getCurrentGmtDate();

            $result = $this->sendRequest();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Client')->updateMySqlConnection();
            throw $exception;
        }

        Mage::helper('M2ePro/Client')->updateMySqlConnection();

        $this->processRequestResult($result);
    }

    // ----------------------------------------

    abstract protected function sendRequest();

    abstract protected function processRequestResult(array $result);

    //########################################

    public function setServerBaseUrl($value)
    {
        $this->_serverBaseUrl = $value;
        return $this;
    }

    public function getServerBaseUrl()
    {
        return $this->_serverBaseUrl;
    }

    // ----------------------------------------

    public function setServerHostName($value)
    {
        $this->_serverHostName = $value;
        return $this;
    }

    public function getServerHostName()
    {
        return $this->_serverHostName;
    }

    // ----------------------------------------

    /**
     * @param boolean $tryToResendOnError
     * @return $this
     */
    public function setTryToResendOnError($tryToResendOnError)
    {
        $this->_tryToResendOnError = $tryToResendOnError;
        return $this;
    }

     /**
     * @return boolean
     */
    public function isTryToResendOnError()
    {
        return $this->_tryToResendOnError;
    }

    // ----------------------------------------

    /**
     * @param boolean $tryToSwitchEndpointOnError
     * @return $this
     */
    public function setTryToSwitchEndpointOnError($tryToSwitchEndpointOnError)
    {
        $this->_tryToSwitchEndpointOnError = $tryToSwitchEndpointOnError;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isTryToSwitchEndpointOnError()
    {
        return $this->_tryToSwitchEndpointOnError;
    }

    //----------------------------------------

    /**
     * @return bool
     */
    public function isCanIgnoreMaintenance()
    {
        return $this->_canIgnoreMaintenance;
    }

    /**
     * @param bool $canIgnoreMaintenance
     */
    public function setCanIgnoreMaintenance($canIgnoreMaintenance)
    {
        $this->_canIgnoreMaintenance = $canIgnoreMaintenance;
    }

    //########################################

    protected function getConnectionErrorMessage()
    {
        return sprintf(
            'The Action was not completed because connection with M2E Pro Server was not set.
            There are several possible reasons:  temporary connection problem – please wait and try again later;
            block of outgoing connection by firewall – please, ensure that connection to s1.m2epro.com and
            s2.m2epro.com, port 443 is allowed; CURL library is not installed or it does not support HTTPS Protocol –
            please, install/update CURL library on your server and ensure it supports HTTPS Protocol.
            More information you can find <a target="_blank" href="%s">here</a>',
            Mage::helper('M2ePro/Module_Support')->getKnowledgebaseUrl('server-connection')
        );
    }

    //########################################
}
