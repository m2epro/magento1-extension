<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_ProductSearch_SuggestedAsinGridHelp
    extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonSuggestedAsinGridHelp');
        // ---------------------------------------

        $this->setTemplate('M2ePro/amazon/listing/product_search/suggested_asin_grid_help.phtml');
    }

    //########################################
}
