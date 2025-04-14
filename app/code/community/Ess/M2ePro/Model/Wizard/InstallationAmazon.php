<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Wizard_InstallationAmazon extends Ess_M2ePro_Model_Wizard
{
    protected $_steps = array(
        'registration',
        'account',
        'settings',

        'listingTutorial',
        'listingGeneral',
        'listingSelling',
        'listingSearch',

        'sourceMode',
        'productSelection',
        'newAsin',
        'validateProductType',
        'searchAsin'
    );

    //########################################

    /**
     * @return bool
     */
    public function isActive($view)
    {
        return Mage::helper('M2ePro/Component_Amazon')->isEnabled();
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