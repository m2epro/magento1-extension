<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Marketplace_Help extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayMarketplaceHelp');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/marketplace/help.phtml');
    }

    // ####################################
}