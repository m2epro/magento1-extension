<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Connection_Multiple_RequestContainer
{
    protected $_request = null;
    protected $_timeout = null;

    //########################################

    /**
     * @return Ess_M2ePro_Model_Connector_Connection_Request|null
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @param Ess_M2ePro_Model_Connector_Connection_Request $request
     * @return $this
     */
    public function setRequest(Ess_M2ePro_Model_Connector_Connection_Request $request)
    {
        $this->_request = $request;
        return $this;
    }

    //########################################

    /**
     * @return null
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }

    /**
     * @param $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->_timeout = $timeout;
        return $this;
    }

    //########################################
}