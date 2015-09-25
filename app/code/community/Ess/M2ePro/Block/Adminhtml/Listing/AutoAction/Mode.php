<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingAutoActionMode');
        //------------------------------

        $this->setTemplate('M2ePro/listing/auto_action/mode.phtml');
    }

    // ####################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        //------------------------------
        $data = array(
            'id'      => 'continue_button',
            'class'   => 'next continue_button',
            'label'   => Mage::helper('M2ePro')->__('Continue')
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('continue_button', $buttonBlock);
        //------------------------------
    }

    // ####################################

    public function isAdminStore()
    {
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('listing');

        return $listing->getStoreId() == Mage_Core_Model_App::ADMIN_STORE_ID;
    }

    public function getWebsiteName()
    {
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('listing');

        return Mage::helper('M2ePro/Magento_Store')->getWebsiteName($listing->getStoreId());
    }

    // ####################################
}
