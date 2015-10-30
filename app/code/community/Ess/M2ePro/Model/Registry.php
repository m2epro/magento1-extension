<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Registry extends Ess_M2ePro_Model_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Registry');
    }

    //########################################

    public function getKey()
    {
        return $this->getData('key');
    }

    public function getValue()
    {
        return $this->getData('value');
    }

    //########################################

    public function getValueFromJson()
    {
        return is_null($this->getId()) ?  array() : json_decode($this->getValue(), true);
    }

    //########################################
}