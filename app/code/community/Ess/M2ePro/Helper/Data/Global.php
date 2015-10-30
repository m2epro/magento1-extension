<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Data_Global extends Mage_Core_Helper_Abstract
{
    //########################################

    public function getValue($key)
    {
        $globalKey = Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER.'_'.$key;
        return Mage::registry($globalKey);
    }

    public function setValue($key, $value)
    {
        $globalKey = Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER.'_'.$key;
        Mage::register($globalKey,$value,!Mage::helper('M2ePro/Module')->isDevelopmentEnvironment());
    }

    //########################################

    public function unsetValue($key)
    {
        $globalKey = Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER.'_'.$key;
        Mage::unregister($globalKey);
    }

    //########################################
}