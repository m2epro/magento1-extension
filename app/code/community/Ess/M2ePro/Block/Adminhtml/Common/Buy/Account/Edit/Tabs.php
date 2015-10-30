<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Account_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('buyAccountEditTabs');
        // ---------------------------------------

        $this->setTitle(Mage::helper('M2ePro')->__('Configuration'));
        $this->setDestElementId('edit_form');
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
            'label'   => Mage::helper('M2ePro')->__('General'),
            'title'   => Mage::helper('M2ePro')->__('General'),
            'content' => $this->getLayout()
                              ->createBlock('M2ePro/adminhtml_common_buy_account_edit_tabs_general')
                              ->toHtml(),
        ));

        $this->addTab('listingOther', array(
            'label'   => Mage::helper('M2ePro')->__('3rd Party Listings'),
            'title'   => Mage::helper('M2ePro')->__('3rd Party Listings'),
            'content' => $this->getLayout()
                              ->createBlock('M2ePro/adminhtml_common_buy_account_edit_tabs_listingOther')
                              ->toHtml(),
        ));

        $this->addTab('orders', array(
            'label'   => Mage::helper('M2ePro')->__('Orders'),
            'title'   => Mage::helper('M2ePro')->__('Orders'),
            'content' => $this->getLayout()
                              ->createBlock('M2ePro/adminhtml_common_buy_account_edit_tabs_order')
                              ->toHtml(),
        ));

        $this->setActiveTab($this->getRequest()->getParam('tab', 'general'));

        return parent::_beforeToHtml();
    }

    //########################################
}