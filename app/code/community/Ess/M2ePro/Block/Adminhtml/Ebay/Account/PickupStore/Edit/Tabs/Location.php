<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_PickupStore_Edit_Tabs_Location extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayAccountPickupStoreEditTabsLocation');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/account/pickupStore/tabs/location.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $tempMarketplaces = Mage::helper('M2ePro/Component_Ebay')->getCollection('Marketplace')
            ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
            ->addFieldToFilter('is_in_store_pickup', 1)
            ->setOrder('sorder','ASC')
            ->setOrder('title','ASC')
            ->toArray();

        $marketplaceList =  !empty($tempMarketplaces['items']) ? $tempMarketplaces['items'] : array();

        // ---------------------------------------
        $this->setData('marketplaces', $marketplaceList);
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################

    public function getFormData()
    {
        $default = array(
            'marketplace_id' => 0,
            'country' => '',
            'region' => '',
            'city' => '',
            'postal_code' => '',
            'address_1' => '',
            'address_2' => '',
            'latitude' => '',
            'longitude' => '',
            'utc_offset' => ''
        );

        $model = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        if(is_null($model)) {
            return $default;
        }

        return array_merge($default, $model->toArray());
    }

    //########################################
}