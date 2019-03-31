<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Requirements_Renderer_Abstract
{
    /** @var Ess_M2ePro_Model_Requirements_Checks_Abstract */
    protected $checkObject;

    //########################################

    public function __construct(array $args = array())
    {
        list($this->checkObject) = $args;
    }

    //########################################

    public function getCheckObject()
    {
        return $this->checkObject;
    }

    //########################################

    abstract public function getTitle();
    abstract public function getMin();
    abstract public function getReal();

    //########################################
}