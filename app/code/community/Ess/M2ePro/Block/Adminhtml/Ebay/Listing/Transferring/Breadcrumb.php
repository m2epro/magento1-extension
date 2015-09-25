<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Transferring_Breadcrumb extends Mage_Adminhtml_Block_Template
{
    //#############################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingTransferringBreadcrumb');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/listing/transferring/breadcrumb.phtml');
    }

    //#############################################
}