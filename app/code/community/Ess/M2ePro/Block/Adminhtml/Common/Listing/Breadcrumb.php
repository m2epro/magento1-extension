<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Breadcrumb extends Mage_Adminhtml_Block_Template
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('commonListingBreadcrumb');
        //------------------------------

        $this->setTemplate('M2ePro/common/listing/breadcrumb.phtml');
    }

    // ####################################
}