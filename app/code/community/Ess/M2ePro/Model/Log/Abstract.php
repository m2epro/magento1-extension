<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Log_Abstract extends Ess_M2ePro_Model_Abstract
{
    const TYPE_NOTICE   = 1;
    const TYPE_SUCCESS  = 2;
    const TYPE_WARNING  = 3;
    const TYPE_ERROR    = 4;

    const PRIORITY_HIGH    = 1;
    const PRIORITY_MEDIUM  = 2;
    const PRIORITY_LOW     = 3;

    protected $_componentMode = null;

    //########################################

    public function setComponentMode($mode)
    {
        $mode = strtolower((string)$mode);
        $mode && $this->_componentMode = $mode;
        return $this;
    }

    public function getComponentMode()
    {
        return $this->_componentMode;
    }

    //########################################
}
