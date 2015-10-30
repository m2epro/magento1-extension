<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_AutoAction_Mode_Category_Form
    extends Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode_Category_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingAutoActionModeCategoryForm');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/listing/auto_action/mode/category/form.phtml');
    }

    //########################################
}
