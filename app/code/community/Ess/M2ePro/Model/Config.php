<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Config extends Ess_M2ePro_Model_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Config');
    }

    //########################################

    public function getGroup()
    {
        return $this->getData('group');
    }

    public function getKey()
    {
        return $this->getData('key');
    }

    public function getValue()
    {
        return $this->getData('value');
    }

    //########################################
}
