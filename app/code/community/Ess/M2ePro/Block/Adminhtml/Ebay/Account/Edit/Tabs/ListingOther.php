<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_Edit_Tabs_ListingOther extends Mage_Adminhtml_Block_Widget
{
    public $marketplaces;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayAccountEditTabsListingOther');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/account/tabs/listing_other.phtml');
    }

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        /** @var Ess_M2ePro_Helper_Magento_Attribute $magentoAttributeHelper */
        $magentoAttributeHelper = Mage::helper('M2ePro/Magento_Attribute');

        $generalAttributes = $magentoAttributeHelper->getGeneralFromAllAttributeSets();

        $this->attributes = $magentoAttributeHelper->filterByInputTypes(
            $generalAttributes, array(
                'text', 'textarea', 'select'
            )
        );
        // ---------------------------------------

        // ---------------------------------------
        $account = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        $marketplacesData = $account->getData('marketplaces_data');
        $marketplacesData = !empty($marketplacesData) ? Mage::helper('M2ePro')->jsonDecode($marketplacesData) : array();

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
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}