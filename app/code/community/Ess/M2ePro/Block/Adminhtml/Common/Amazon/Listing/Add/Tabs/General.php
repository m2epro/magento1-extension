<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Add_Tabs_General
    extends Ess_M2ePro_Block_Adminhtml_Common_Listing_Add_Tabs_General
{
    // #############################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->sessionKey = 'amazon_listing_create';
        $this->setId('amazonListingAddTabsGeneral');
        $this->setTemplate('M2ePro/common/amazon/listing/add/tabs/general.phtml');
        //------------------------------
    }

    // #############################################
}