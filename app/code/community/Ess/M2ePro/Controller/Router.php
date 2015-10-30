<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Controller_Router extends Mage_Core_Controller_Varien_Router_Admin
{
    //#############################################

    /**
     * Unpatch SUPEE-6788 (APPSEC-1034, addressing bypassing custom admin URL)
     *
     * @param $frontName
     * @param $moduleName
     * @param $routeName
     * @return $this
     */
    public function addModule($frontName, $moduleName, $routeName)
    {
        return Mage_Core_Controller_Varien_Router_Standard::addModule($frontName, $moduleName, $routeName);
    }

    //#############################################
}