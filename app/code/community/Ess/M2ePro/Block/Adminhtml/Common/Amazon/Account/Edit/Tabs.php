<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Account_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonAccountEditTabs');
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
                              ->createBlock('M2ePro/adminhtml_common_amazon_account_edit_tabs_general')->toHtml(),
        ));

        $this->addTab('listingOther', array(
            'label'   => Mage::helper('M2ePro')->__('3rd Party Listings'),
            'title'   => Mage::helper('M2ePro')->__('3rd Party Listings'),
            'content' => $this->getLayout()
                              ->createBlock('M2ePro/adminhtml_common_amazon_account_edit_tabs_listingOther')->toHtml(),
        ));

        $this->addTab('orders', array(
            'label'   => Mage::helper('M2ePro')->__('Orders'),
            'title'   => Mage::helper('M2ePro')->__('Orders'),
            'content' => $this->getLayout()
                              ->createBlock('M2ePro/adminhtml_common_amazon_account_edit_tabs_order')->toHtml(),
        ));

        if (Mage::helper('M2ePro/Component_Amazon_Repricing')->isEnabled() &&
            Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId()) {

            $this->addTab('repricing', array(
                'label'   => Mage::helper('M2ePro')->__('Repricing Tool'),
                'title'   => Mage::helper('M2ePro')->__('Repricing Tool'),
                'content' => $this->getLayout()
                    ->createBlock('M2ePro/adminhtml_common_amazon_account_edit_tabs_repricing')->toHtml(),
            ));
        }

        $this->setActiveTab($this->getRequest()->getParam('tab', 'general'));

        return parent::_beforeToHtml();
    }

    //########################################
}