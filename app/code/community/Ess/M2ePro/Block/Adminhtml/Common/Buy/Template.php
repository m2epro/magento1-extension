<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Template extends Ess_M2ePro_Block_Adminhtml_Common_Template
{
    protected $nick = Ess_M2ePro_Helper_Component_Buy::NICK;

    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('commonBuyTemplate');
        //------------------------------
    }

    // ########################################
}