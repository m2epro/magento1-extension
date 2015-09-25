<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Variation_Product_Manage_Tabs_Variations_Help
    extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonListingViewHelp');
        //------------------------------

        $this->setTemplate('M2ePro/common/amazon/listing/variation/product/manage/tabs/variations/help.phtml');
    }

    // ####################################
}