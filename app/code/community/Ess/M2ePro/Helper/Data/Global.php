<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Data_Global extends Mage_Core_Helper_Abstract
{
    // ########################################

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

    // ########################################

    public function unsetValue($key)
    {
        $globalKey = Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER.'_'.$key;
        Mage::unregister($globalKey);
    }

    // ########################################
}