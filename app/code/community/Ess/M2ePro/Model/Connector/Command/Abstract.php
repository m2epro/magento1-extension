<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Command_Abstract
{
    protected $_params = array();

    /** @var Ess_M2ePro_Model_Connector_Protocol */
    protected $_protocol = null;

    /** @var Ess_M2ePro_Model_Connector_Connection_Single $_connection */
    protected $_connection = null;

    // ########################################

    public function __construct(array $params)
    {
        $this->_params = $params;
    }

    // ########################################

    public function setProtocol(Ess_M2ePro_Model_Connector_Protocol $protocol)
    {
        $this->_protocol = $protocol;
        return $this;
    }

    public function getProtocol()
    {
        return $this->_protocol;
    }

    // ########################################

    abstract public function process();

    // ########################################

    protected function getConnection()
    {
        if ($this->_connection !== null) {
            return $this->_connection;
        }

        return $this->_connection = $this->buildConnectionInstance();
    }

    protected function buildConnectionInstance()
    {
        $connection = Mage::getModel('M2ePro/Connector_Connection_Single');
        $connection->setRequest($this->buildRequestInstance());

        return $connection;
    }

    // ----------------------------------------

    protected function buildRequestInstance()
    {
        $request = Mage::getModel('M2ePro/Connector_Connection_Request');
        $request->setCommand($this->getCommand());

        $request->setComponent($this->getProtocol()->getComponent());
        $request->setComponentVersion($this->getProtocol()->getComponentVersion());

        $request->setData($this->getRequestData());

        return $request;
    }

    // ########################################

    public function getRequest()
    {
        return $this->getConnection()->getRequest();
    }

    public function getResponse()
    {
        return $this->getConnection()->getResponse();
    }

    // ########################################

    public function getRequestTime()
    {
        return $this->getResponse()->getRequestTime();
    }

    // ########################################

    /**
     * @return array
     */
    abstract public function getRequestData();

    /**
     * @return array
     */
    abstract protected function getCommand();

    // ########################################
}