<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Command_Abstract
{
    // ########################################

    protected $params = array();

    /** @var Ess_M2ePro_Model_Connector_Protocol */
    protected $protocol = NULL;

    /** @var Ess_M2ePro_Model_Connector_Connection_Single $connection */
    protected $connection = NULL;

    // ########################################

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    // ########################################

    public function setProtocol(Ess_M2ePro_Model_Connector_Protocol $protocol)
    {
        $this->protocol = $protocol;
        return $this;
    }

    public function getProtocol()
    {
        return $this->protocol;
    }

    // ########################################

    abstract public function process();

    // ########################################

    protected function getConnection()
    {
        if (!is_null($this->connection)) {
            return $this->connection;
        }

        return $this->connection = $this->buildConnectionInstance();
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