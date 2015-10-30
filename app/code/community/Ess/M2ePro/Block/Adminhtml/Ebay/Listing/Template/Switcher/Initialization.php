<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Template_Switcher_Initialization
    extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('EbayListingTemplateSwitcherInitialization');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/listing/template/switcher/initialization.phtml');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        $this->setChild('confirm', $this->getLayout()->createBlock('M2ePro/adminhtml_widget_dialog_confirm'));
        // ---------------------------------------
    }

    //########################################
}