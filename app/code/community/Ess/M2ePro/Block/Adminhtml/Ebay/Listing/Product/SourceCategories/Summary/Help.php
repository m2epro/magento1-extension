<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_SourceCategories_Summary_Help
    extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingProductSourceCategoriesSummaryHelp');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/listing/product/source_categories/summary/help.phtml');
    }

    // ####################################
}