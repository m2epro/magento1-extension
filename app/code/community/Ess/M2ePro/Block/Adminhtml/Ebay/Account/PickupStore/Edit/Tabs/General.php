<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_PickupStore_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayAccountPickupStoreEditTabsGeneral');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/account/pickupStore/tabs/general.phtml');
    }

    //########################################

    public function getFormData()
    {
        $default = array(
            'name' => '',
            'location_id' => '',
            'account_id' => (int)Mage::helper('M2ePro/Component_Ebay_PickupStore')->getEnabledAccount()->getId(),
            'phone' => '',
            'url' => '',
            'pickup_instruction' => ''
        );

        $model = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        if(is_null($model)) {
            return $default;
        }

        return array_merge($default, $model->toArray());
    }

    //########################################
}