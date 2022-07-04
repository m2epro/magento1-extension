<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Log_Abstract extends Ess_M2ePro_Model_Abstract
{
    const TYPE_INFO   = 1;
    const TYPE_SUCCESS  = 2;
    const TYPE_WARNING  = 3;
    const TYPE_ERROR    = 4;

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
