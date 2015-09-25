<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Registry extends Ess_M2ePro_Model_Abstract
{
    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Registry');
    }

    //####################################

    public function getKey()
    {
        return $this->getData('key');
    }

    public function getValue()
    {
        return $this->getData('value');
    }

    //####################################

    public function getValueFromJson()
    {
        return is_null($this->getId()) ?  array() : json_decode($this->getValue(), true);
    }

    //####################################
}