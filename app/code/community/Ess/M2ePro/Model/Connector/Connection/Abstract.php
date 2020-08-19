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
        return Mage::helper('M2ePro')->__(
            'M2E Pro Server connection failed. Find the solution <a target="_blank" href="%url%">here</a>',
            Mage::helper('M2ePro/Module_Support')->getKnowledgebaseUrl('664870')
        );
    }

    //########################################
}
