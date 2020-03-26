<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_PickupStore_Edit_Tabs_StockSettings extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayAccountPickupStoreEditTabsStockSettings');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/account/pickupStore/tabs/stockSettings.phtml');

        $this->attributes = Mage::helper('M2ePro/Magento_Attribute')->getAll();
    }

    //########################################

    public function getFormData()
    {
        $default = array(
            'qty_mode' => Ess_M2ePro_Model_Ebay_Account_PickupStore::QTY_MODE_SELLING_FORMAT_TEMPLATE,
            'qty_custom_value' => 1,
            'qty_custom_attribute' => '',
            'qty_percentage' => 100,
            'qty_modification_mode' => 0,
            'qty_min_posted_value' => 1,
            'qty_max_posted_value' => 100
        );

        $model = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        if ($model === null) {
            return $default;
        }

        return array_merge($default, $model->toArray());
    }

    //########################################
}
