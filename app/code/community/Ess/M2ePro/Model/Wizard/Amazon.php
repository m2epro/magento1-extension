<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Model_Wizard_Amazon extends Ess_M2ePro_Model_Wizard
{
    protected $steps = array(
        'marketplace',
        'account'
    );

    // ########################################

    public function isActive()
    {
        return Mage::helper('M2ePro/Component_Amazon')->isActive();
    }

    public function getNick()
    {
        return 'amazon';
    }

    // ########################################
}