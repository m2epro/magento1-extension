<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Model_Wizard_InstallationEbay extends Ess_M2ePro_Model_Wizard
{
    protected $steps = array(
        'wizardTutorial',
        'license',
        'modeConfirmation',
        'account',

        'listingTutorial',
        'listingAccount',
        'listingGeneral',
        'listingSelling',
        'listingSynchronization',

        'productTutorial',
        'sourceMode',
        'productSelection',
        'productSettings',

        'categoryStepOne',
        'categoryStepTwo',
        'categoryStepThree',
    );

    // ########################################

    public function isActive()
    {
        return Mage::helper('M2ePro/Component_Ebay')->isActive();
    }

    public function getNick()
    {
        return Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK;
    }

    // ########################################
}