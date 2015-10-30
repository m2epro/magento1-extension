<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Wizard_Amazon extends Ess_M2ePro_Model_Wizard
{
    protected $steps = array(
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
        return 'amazon';
    }

    //########################################
}