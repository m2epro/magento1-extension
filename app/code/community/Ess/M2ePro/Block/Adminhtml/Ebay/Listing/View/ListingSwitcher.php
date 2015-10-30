<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View_ListingSwitcher
    extends Ess_M2ePro_Block_Adminhtml_Listing_View_ListingSwitcher_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block

        $this->setAddListingUrl('*/adminhtml_ebay_listing_create/index');
    }

    //########################################
}