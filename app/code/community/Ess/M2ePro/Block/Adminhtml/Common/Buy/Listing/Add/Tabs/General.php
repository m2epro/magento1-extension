<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Listing_Add_Tabs_General
    extends Ess_M2ePro_Block_Adminhtml_Common_Listing_Add_Tabs_General
{
    // #############################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->sessionKey = 'buy_listing_create';
        $this->setId('buyListingAddTabsGeneral');
        $this->setTemplate('M2ePro/common/buy/listing/add/tabs/general.phtml');
        //------------------------------
    }

    // #############################################
}