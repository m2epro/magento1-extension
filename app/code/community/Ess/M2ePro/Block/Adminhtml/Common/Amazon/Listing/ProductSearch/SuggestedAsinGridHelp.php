<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_ProductSearch_SuggestedAsinGridHelp
    extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonSuggestedAsinGridHelp');
        //------------------------------

        $this->setTemplate('M2ePro/common/amazon/listing/product_search/suggested_asin_grid_help.phtml');
    }

    // ####################################
}