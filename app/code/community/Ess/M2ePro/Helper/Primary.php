<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Primary extends Mage_Core_Helper_Abstract
{
    // ########################################

    /**
     * @return Ess_M2ePro_Model_Config_Primary
     */
    public function getConfig()
    {
        return Mage::getSingleton('M2ePro/Config_Primary');
    }

    // ########################################

    public function getModules()
    {
        return $this->getConfig()->getAllGroupValues('/modules/');
    }

    // ########################################
}