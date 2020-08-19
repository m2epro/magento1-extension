<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Response_Message
{
    const TEXT_KEY   = 'text';
    const TYPE_KEY   = 'type';

    const TYPE_ERROR   = 'error';
    const TYPE_WARNING = 'warning';
    const TYPE_SUCCESS = 'success';
    const TYPE_NOTICE  = 'notice';

    //########################################

    protected $_text = '';
    protected $_type = null;

    //########################################

    public function initFromResponseData(array $responseData)
    {
        $this->_text = $responseData[self::TEXT_KEY];
        $this->_type = $responseData[self::TYPE_KEY];
    }

    public function initFromPreparedData($text, $type)
    {
        $this->_text = $text;
        $this->_type = $type;
    }

    public function initFromException(Exception $exception)
    {
        $this->_text = $exception->getMessage();
        $this->_type = self::TYPE_ERROR;
    }

    //########################################

    public function asArray()
    {
        return array(
            self::TEXT_KEY   => $this->_text,
            self::TYPE_KEY   => $this->_type,
        );
    }

    //########################################

    public function getText()
    {
        return $this->_text;
    }

    public function getType()
    {
        return $this->_type;
    }

    //########################################

    public function isError()
    {
        return $this->_type == self::TYPE_ERROR;
    }

    public function isWarning()
    {
        return $this->_type == self::TYPE_WARNING;
    }

    public function isSuccess()
    {
        return $this->_type == self::TYPE_SUCCESS;
    }

    public function isNotice()
    {
        return $this->_type == self::TYPE_NOTICE;
    }

    //########################################
}
