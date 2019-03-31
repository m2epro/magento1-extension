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

    //########################################

    protected $sender = NULL;
    protected $code   = NULL;

    //########################################

    public function initFromResponseData(array $responseData)
    {
        parent::initFromResponseData($responseData);

        $this->sender = $responseData[self::SENDER_KEY];
        $this->code   = $responseData[self::CODE_KEY];
    }

    public function initFromPreparedData($text, $type, $sender = NULL, $code = NULL)
    {
        parent::initFromPreparedData($text, $type);

        $this->sender = $sender;
        $this->code   = $code;
    }

    //########################################

    public function asArray()
    {
        return array_merge(parent::asArray(), array(
            self::SENDER_KEY => $this->sender,
            self::CODE_KEY   => $this->code,
        ));
    }

    //########################################

    public function isSenderSystem()
    {
        return $this->sender == self::SENDER_SYSTEM;
    }

    public function isSenderComponent()
    {
        return $this->sender == self::SENDER_COMPONENT;
    }

    //########################################

    public function getCode()
    {
        return $this->code;
    }

    //########################################
}