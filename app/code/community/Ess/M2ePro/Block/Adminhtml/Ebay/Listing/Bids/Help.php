<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Bids_Help
    extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingProductBidsHelp');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/listing/bids/help.phtml');
    }

    // ####################################
}
