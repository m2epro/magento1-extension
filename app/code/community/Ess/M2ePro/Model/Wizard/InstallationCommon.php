<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Wizard_InstallationCommon extends Ess_M2ePro_Model_Wizard
{
    protected $steps = array(
        'license',
        'settings'
    );

    //########################################

    /**
     * @return string
     */
    public function getNick()
    {
        return Ess_M2ePro_Helper_View_Common::WIZARD_INSTALLATION_NICK;
    }

    //########################################
}