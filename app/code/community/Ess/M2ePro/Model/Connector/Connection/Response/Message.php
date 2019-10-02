<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Connection_Response_Message extends Ess_M2ePro_Model_Response_Message
{
    const SENDER_KEY = 'sender';
    const CODE_KEY   = 'code';

    const SENDER_SYSTEM    = 'system';
    const SENDER_COMPONENT = 'component';

    protected $_sender = null;
    protected $_code   = null;

    //########################################

    public function initFromResponseData(array $responseData)
    {
        parent::initFromResponseData($responseData);

        $this->_sender = $responseData[self::SENDER_KEY];
        $this->_code   = $responseData[self::CODE_KEY];
    }

    public function initFromPreparedData($text, $type, $sender = NULL, $code = NULL)
    {
        parent::initFromPreparedData($text, $type);

        $this->_sender = $sender;
        $this->_code   = $code;
    }

    //########################################

    public function asArray()
    {
        return array_merge(
            parent::asArray(), array(
                self::SENDER_KEY => $this->_sender,
                self::CODE_KEY   => $this->_code,
            )
        );
    }

    //########################################

    public function isSenderSystem()
    {
        return $this->_sender == self::SENDER_SYSTEM;
    }

    public function isSenderComponent()
    {
        return $this->_sender == self::SENDER_COMPONENT;
    }

    //########################################

    public function getCode()
    {
        return $this->_code;
    }

    //########################################
}