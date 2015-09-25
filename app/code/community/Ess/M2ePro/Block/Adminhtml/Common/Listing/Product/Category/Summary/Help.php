<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Product_Category_Summary_Help
    extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('commonListingProductSourceCategoriesSummaryHelp');
        //------------------------------

        $this->setTemplate('M2ePro/common/listing/product/summary/help.phtml');
    }

    // ####################################
}