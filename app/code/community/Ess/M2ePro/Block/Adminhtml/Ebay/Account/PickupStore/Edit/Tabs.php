<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_PickupStore_Edit_Tabs
    extends Ess_M2ePro_Block_Adminhtml_Widget_Tabs
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayAccountPickupStoreEditTabs');
        // ---------------------------------------

        $this->setTitle(Mage::helper('M2ePro')->__('Configuration'));
        $this->setDestElementId('edit_form');
    }

    protected function _beforeToHtml()
    {
        $this->addTab(
            'general', array(
            'label'   => Mage::helper('M2ePro')->__('General'),
            'title'   => Mage::helper('M2ePro')->__('General'),
            'content' => $this->getLayout()
                ->createBlock('M2ePro/adminhtml_ebay_account_pickupStore_edit_tabs_general')
                ->toHtml(),
            )
        );

        $this->addTab(
            'location', array(
            'label'   => Mage::helper('M2ePro')->__('Location'),
            'title'   => Mage::helper('M2ePro')->__('Location'),
            'content' => $this->getLayout()
                ->createBlock('M2ePro/adminhtml_ebay_account_pickupStore_edit_tabs_location')->toHtml(),
            )
        );

        $this->addTab(
            'business_hours', array(
            'label'   => Mage::helper('M2ePro')->__('Business Hours'),
            'title'   => Mage::helper('M2ePro')->__('Business Hours'),
            'content' => $this->getLayout()
                ->createBlock('M2ePro/adminhtml_ebay_account_pickupStore_edit_tabs_businessHours')->toHtml(),
            )
        );

        $this->addTab(
            'stock_settings', array(
            'label'   => Mage::helper('M2ePro')->__('Quantity Settings'),
            'title'   => Mage::helper('M2ePro')->__('Quantity Settings'),
            'content' => $this->getLayout()
                ->createBlock('M2ePro/adminhtml_ebay_account_pickupStore_edit_tabs_stockSettings')->toHtml(),
            )
        );

        $this->setActiveTab($this->getRequest()->getParam('tab', 'general'));

        return parent::_beforeToHtml();
    }

    //########################################
}
