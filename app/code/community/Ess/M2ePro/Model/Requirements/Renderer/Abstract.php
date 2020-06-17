<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Requirements_Renderer_Abstract
{
    /** @var Ess_M2ePro_Model_Requirements_Checks_Abstract */
    protected $_checkObject;

    /** @var bool */
    protected $_hideIfMeet = false;

    //########################################

    public function __construct(array $args = array())
    {
        list($this->_checkObject) = $args;
    }

    //########################################

    public function getCheckObject()
    {
        return $this->_checkObject;
    }

    //########################################

    abstract public function getTitle();
    abstract public function getMin();
    abstract public function getReal();
    abstract public function getAdditional();

    //########################################

    public function getHideIfMeet()
    {
        return $this->_hideIfMeet;
    }

    //########################################
}
