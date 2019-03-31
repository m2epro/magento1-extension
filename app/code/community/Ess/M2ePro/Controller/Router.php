<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Controller_Router extends Mage_Core_Controller_Varien_Router_Admin
{
    //#############################################

    /**
     * Custom implementation of APPSEC-1034 (SUPEE-6788) [see additional information below].
     * M2E Pro prevents redirect to Magento Admin Panel login page.
     *
     * The code below allows to use M2E Pro under non-default admin URLs.
     * \Ess_M2ePro_Controller_Adminhtml_BaseController::preDispatch method
     * has custom implementation which prevents redirect to Magento Admin Panel login page.
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