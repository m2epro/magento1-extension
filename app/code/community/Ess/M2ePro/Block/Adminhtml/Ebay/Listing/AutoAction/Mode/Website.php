<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_AutoAction_Mode_Website
    extends Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode_Website
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingAutoActionModeWebsite');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/listing/auto_action/mode/website.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        $breadcrumb = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_autoAction_mode_breadcrumb');
        $breadcrumb->setData('step', 1);
        $this->setChild('breadcrumb', $breadcrumb);
        // ---------------------------------------
    }

    //########################################
}