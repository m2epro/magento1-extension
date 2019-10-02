<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Resource_Registry _getResource()
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

    // ---------------------------------------

    public function setValue($value)
    {
        is_array($value) && $value = Mage::helper('M2ePro')->jsonEncode($value);
        return $this->setData('value', $value);
    }

    //########################################

    public function getValueFromJson()
    {
        return $this->getId() === null ?  array() : Mage::helper('M2ePro')->jsonDecode($this->getValue());
    }

    //########################################

    public function loadByKey($key)
    {
        $this->_getResource()->loadByKey($this, $key);
        return $this;
    }

    //########################################
}
