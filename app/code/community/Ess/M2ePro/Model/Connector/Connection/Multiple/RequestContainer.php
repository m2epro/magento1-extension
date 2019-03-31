<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Connection_Multiple_RequestContainer
{
    private $request = NULL;
    private $timeout = NULL;

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Connector_Connection_Request|null
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Ess_M2ePro_Model_Connector_Connection_Request $request
     * @return $this
     */
    public function setRequest(Ess_M2ePro_Model_Connector_Connection_Request $request)
    {
        $this->request = $request;
        return $this;
    }

    // ########################################

    /**
     * @return null
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    // ########################################
}