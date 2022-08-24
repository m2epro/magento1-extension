<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Exception extends Exception
{
    private $_additionalData;

    public function __construct($message = '', $additionalData = array(), $code = 0)
    {
        $this->_additionalData = $additionalData;

        parent::__construct($message, $code, null);
    }

    public function getAdditionalData()
    {
        return $this->_additionalData;
    }
}
