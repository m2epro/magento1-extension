<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Wizard_InstallationWalmart extends Ess_M2ePro_Model_Wizard
{
    protected $_steps = array(
        'registration',
        'account',
        'settings',
        'listingTutorial'
    );

    //########################################

    /**
     * @return bool
     */
    public function isActive($view)
    {
        return Mage::helper('M2ePro/Component_Walmart')->isEnabled();
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