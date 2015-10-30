<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Exception extends Exception
{
    protected $additionalData = array();
    protected $sendToServer = NULL;

    //########################################

    public function __construct($message = "", $additionalData = array(), $code = 0, $sendToServer = true)
    {
        $this->additionalData = $additionalData;
        $this->sendToServer = $sendToServer;

        parent::__construct($message, $code, null);
    }

    //########################################

    public function getAdditionalData()
    {
        return $this->additionalData;
    }

    public function setAdditionalData($additionalData)
    {
        $this->additionalData = $additionalData;
        return $this;
    }

    //########################################

    public function setSendToServer($value)
    {
        $this->sendToServer = (bool)$value;
    }

    public function isSendToServer()
    {
        return $this->sendToServer;
    }

    //########################################
}