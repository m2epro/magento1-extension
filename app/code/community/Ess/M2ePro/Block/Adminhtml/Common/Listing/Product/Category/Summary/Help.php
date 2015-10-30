<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Product_Category_Summary_Help
    extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('commonListingProductSourceCategoriesSummaryHelp');
        // ---------------------------------------

        $this->setTemplate('M2ePro/common/listing/product/summary/help.phtml');
    }

    //########################################
}