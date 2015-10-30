<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Account_Edit_Tabs_ListingOther extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonAccountEditTabsListingOther');
        // ---------------------------------------

        $this->setTemplate('M2ePro/common/amazon/account/tabs/listing_other.phtml');
    }

    protected function _beforeToHtml()
    {
        $this->attributes = Mage::helper('M2ePro/Magento_Attribute')->getGeneralFromAllAttributeSets();

        return parent::_beforeToHtml();
    }

    //########################################
}