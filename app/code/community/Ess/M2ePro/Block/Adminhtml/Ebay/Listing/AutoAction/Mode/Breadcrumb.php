<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_AutoAction_Mode_Breadcrumb extends Mage_Adminhtml_Block_Template
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingAutoActionModeBreadcrumb');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/listing/auto_action/mode/breadcrumb.phtml');
    }

    // ####################################
}