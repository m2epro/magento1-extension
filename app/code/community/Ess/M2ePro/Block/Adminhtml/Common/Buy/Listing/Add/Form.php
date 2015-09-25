<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Listing_Add_Form
    extends Ess_M2ePro_Block_Adminhtml_Common_Listing_Add_Form
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->component = Ess_M2ePro_Helper_Component_Buy::NICK;
        $this->setId('buyListingEditForm');
        //------------------------------
    }

    // ########################################
}