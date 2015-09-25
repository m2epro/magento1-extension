<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Account_Edit_Tabs_ListingOther extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyAccountEditTabsListingOther');
        //------------------------------

        $this->setTemplate('M2ePro/common/buy/account/tabs/listing_other.phtml');
    }

    protected function _beforeToHtml()
    {
        $this->attributes = Mage::helper('M2ePro/Magento_Attribute')->getGeneralFromAllAttributeSets();

        return parent::_beforeToHtml();
    }

    // ####################################
}