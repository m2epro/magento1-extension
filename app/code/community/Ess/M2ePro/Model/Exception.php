<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Exception extends Exception
{
    protected $_additionalData = array();
    protected $_sendToServer   = null;

    //########################################

    public function __construct($message = "", $additionalData = array(), $code = 0, $sendToServer = true)
    {
        $this->_additionalData = $additionalData;
        $this->_sendToServer   = $sendToServer;

        parent::__construct($message, $code, null);
    }

    //########################################

    public function getAdditionalData()
    {
        return $this->_additionalData;
    }

    public function setAdditionalData($additionalData)
    {
        $this->_additionalData = $additionalData;
        return $this;
    }

    //########################################

    public function setSendToServer($value)
    {
        $this->_sendToServer = (bool)$value;
    }

    public function isSendToServer()
    {
        return $this->_sendToServer;
    }

    //########################################
}