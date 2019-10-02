<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Wizard_InstallationWalmart extends Ess_M2ePro_Model_Wizard
{
    protected $_steps = array(
        'license',
        'marketplace',
        'account',
        'settings'
    );

    //########################################

    /**
     * @return bool
     */
    public function isActive()
    {
        return Mage::helper('M2ePro/Component_Walmart')->isActive();
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return Ess_M2ePro_Helper_View_Walmart::WIZARD_INSTALLATION_NICK;
    }

    //########################################
}