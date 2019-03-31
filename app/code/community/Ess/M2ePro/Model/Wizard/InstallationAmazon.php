<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Wizard_InstallationAmazon extends Ess_M2ePro_Model_Wizard
{
    protected $steps = array(
        'license',
        'marketplace',
        'account'
    );

    //########################################

    /**
     * @return bool
     */
    public function isActive()
    {
        return Mage::helper('M2ePro/Component_Amazon')->isActive();
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return Ess_M2ePro_Helper_View_Amazon::WIZARD_INSTALLATION_NICK;
    }

    //########################################
}