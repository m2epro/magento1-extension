<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Model_Wizard_InstallationCommon extends Ess_M2ePro_Model_Wizard
{
    protected $steps = array(
        'license',
        'settings'
    );

    // ########################################

    public function getNick()
    {
        return Ess_M2ePro_Helper_View_Common::WIZARD_INSTALLATION_NICK;
    }

    // ########################################
}