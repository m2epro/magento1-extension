<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Servicing_Task
{
    protected $_params = array();
    protected $_initiator;

    //########################################

    public function setInitiator($initiator)
    {
        $this->_initiator = $initiator;
        return $this;
    }

    public function getInitiator()
    {
        return $this->_initiator;
    }

    //########################################

    /**
     * @return string
     */
    abstract public function getPublicNick();

    //########################################

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params = array())
    {
        $this->_params = $params;
    }

    //########################################

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return true;
    }

    // ---------------------------------------
    /**
     * @return array
     */
    abstract public function getRequestData();

    /**
     * @param array $data
     * @return null
     */
    abstract public function processResponseData(array $data);

    //########################################
}
