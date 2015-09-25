<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_Edit_Tabs_ListingOther extends Mage_Adminhtml_Block_Widget
{
    public $marketplaces;

    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayAccountEditTabsListingOther');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/account/tabs/listing_other.phtml');
    }

    protected function _beforeToHtml()
    {
        //-------------------------------
        $this->attributes = Mage::helper('M2ePro/Magento_Attribute')->getGeneralFromAllAttributeSets();
        //-------------------------------

        //-------------------------------
        $back = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_ebay_account/edit', array(
            'id' => $this->getRequest()->getParam('id'),
            'tab' => 'listingOther'
        ));
        $url = $this->getUrl('*/adminhtml_ebay_listing_other_synchronization/edit', array('back' => $back));
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Synchronization Settings'),
            'onclick' => 'window.open(\'' . $url . '\', \'_blank\')',
            'class'   => 'button_link'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('ebay_other_listings_synchronization_settings', $buttonBlock);
        //-------------------------------

        //-------------------------------
        $account = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        $marketplacesData = $account->getData('marketplaces_data');
        $marketplacesData = !empty($marketplacesData) ? json_decode($marketplacesData, true) : array();

        $marketplaces = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Marketplace')
            ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
            ->setOrder('sorder','ASC')
            ->setOrder('title','ASC')
            ->toArray();
        $marketplaces = $marketplaces['items'];

        foreach ($marketplaces as &$marketplace) {
            $marketplaceId = $marketplace['id'];
            $marketplace['related_store_id'] = isset($marketplacesData[$marketplaceId]['related_store_id'])
                ? $marketplacesData[$marketplaceId]['related_store_id']
                : Mage_Core_Model_App::ADMIN_STORE_ID;
        }

        $this->marketplaces = $marketplaces;
        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ####################################
}