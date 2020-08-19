<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Result
{
    const STATE_ERROR    = 40;
    const STATE_WARNING  = 30;
    const STATE_NOTICE   = 20;
    const STATE_SUCCESS  = 10;

    /** @var Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection */
    protected $_inspection;

    /** @var int */
    protected $_state;

    /** @var string|null */
    protected $_message;

    /** @var array|string|null */
    protected $_metadata;

    //########################################

    public function __construct(array $args)
    {
        list($this->_inspection, $this->_state, $this->_message, $this->_metadata) = $args;
    }

    //########################################

    public function getInspection()
    {
        return $this->_inspection;
    }

    public function getState()
    {
        return $this->_state;
    }

    public function isError()
    {
        return $this->_state === self::STATE_ERROR;
    }

    public function isWaring()
    {
        return $this->_state === self::STATE_WARNING;
    }

    public function isNotice()
    {
        return $this->_state === self::STATE_NOTICE;
    }

    public function isSuccess()
    {
        return $this->_state === self::STATE_SUCCESS;
    }

    // ---------------------------------------

    public function getMessage()
    {
        return $this->_message;
    }

    public function getMetadata()
    {
        if (empty($this->_metadata)) {
            return  '';
        }

        if (is_array($this->_metadata)) {
            if (is_int(key($this->_metadata))) {
                return implode(PHP_EOL, $this->_metadata);
            }

            return '<pre>' . str_replace('Array', '', print_r($this->_metadata, true)) . '</pre>';
        }

        return $this->_metadata;
    }

    //########################################
}