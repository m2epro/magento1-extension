<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Exception extends Exception
{
    protected $additionalData = array();
    protected $sendToServer = NULL;

    // ########################################

    public function __construct($message = "", $additionalData = array(), $code = 0, $sendToServer = true)
    {
        $this->additionalData = $additionalData;
        $this->sendToServer = $sendToServer;

        parent::__construct($message, $code, null);
    }

    // ########################################

    public function getAdditionalData()
    {
        return $this->additionalData;
    }

    public function setAdditionalData($additionalData)
    {
        $this->additionalData = $additionalData;
        return $this;
    }

    // ########################################

    public function setSendToServer($value)
    {
        $this->sendToServer = (bool)$value;
    }

    public function isSendToServer()
    {
        return $this->sendToServer;
    }

    // ########################################
}