<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Other_Synchronization_Edit_Tabs_General
    extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingOtherSynchronizationEditTabsGeneral');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/listing/other/synchronization/tabs/general.phtml');
    }

    // ####################################

    protected function _beforeToHtml()
    {
        $this->attributes = Mage::helper('M2ePro/Magento_Attribute')->getGeneralFromAllAttributeSets();
        $this->customerGroups = Mage::getModel('customer/group')->getCollection()->toOptionArray();

        return parent::_beforeToHtml();
    }

    // ####################################
}