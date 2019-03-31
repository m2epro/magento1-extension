<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
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

    //########################################

    /**
     * @return bool
     */
    public function isActive()
    {
        return Mage::helper('M2ePro/Component_Ebay')->isActive();
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK;
    }

    //########################################
}