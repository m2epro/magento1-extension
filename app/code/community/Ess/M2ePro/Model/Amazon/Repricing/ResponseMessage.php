<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Repricing_ResponseMessage
{
    const TYPE_ERROR   = 'error';
    const TYPE_WARNING = 'warning';
    const TYPE_SUCCESS = 'success';
    const TYPE_NOTICE  = 'notice';

    const DEFAULT_CODE           = 0;
    const NOT_FOUND_ACCOUNT_CODE = 100;

    /** @var int */
    private $code;
    /** @var string */
    private $text;
    /** @var string */
    private $type;

    /**
     * @param string $text
     * @param string $type
     * @param int $code
     */
    public function __construct(
        $text,
        $type = self::TYPE_WARNING,
        $code = self::DEFAULT_CODE
    ) {
        $this->text = $text;
        $this->type = $type;
        $this->code = $code;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->type === self::TYPE_ERROR;
    }

    /**
     * @return bool
     */
    public function isWarning()
    {
        return $this->type === self::TYPE_WARNING;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->type === self::TYPE_SUCCESS;
    }

    /**
     * @return bool
     */
    public function isNotice()
    {
        return $this->type === self::TYPE_NOTICE;
    }
}
