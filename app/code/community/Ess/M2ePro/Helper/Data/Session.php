<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Data_Session extends Mage_Core_Helper_Abstract
{
    // ########################################

    public function getValue($key, $clear = false)
    {
        return Mage::getSingleton('adminhtml/session')->getData(
            Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER.'_'.$key, $clear
        );
    }

    public function setValue($key, $value)
    {
        Mage::getSingleton('adminhtml/session')->setData(Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER.'_'.$key, $value);
    }

    //-----------------------------------------

    public function getAllValues()
    {
        $return = array();
        $session = Mage::getSingleton('adminhtml/session')->getData();

        $identifierLength = strlen(Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER);

        foreach ($session as $key => $value) {
            if (substr($key, 0, $identifierLength) == Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER) {
                $tempReturnedKey = substr($key, strlen(Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER)+1);
                $return[$tempReturnedKey] = Mage::getSingleton('adminhtml/session')->getData($key);
            }
        }
        return $return;
    }

    // ########################################

    public function removeValue($key)
    {
        Mage::getSingleton('adminhtml/session')->getData(Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER.'_'.$key, true);
    }

    public function removeAllValues()
    {
        $session = Mage::getSingleton('adminhtml/session')->getData();

        $identifierLength = strlen(Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER);

        foreach ($session as $key => $value) {
            if (substr($key, 0, $identifierLength) == Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER) {
                Mage::getSingleton('adminhtml/session')->getData($key, true);
            }
        }
    }

    // ########################################
}