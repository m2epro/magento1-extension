<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayAccountEditTabs');
        // ---------------------------------------

        $this->setTitle(Mage::helper('M2ePro')->__('Configuration'));
        $this->setDestElementId('edit_form');
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
            'label'   => Mage::helper('M2ePro')->__('General'),
            'title'   => Mage::helper('M2ePro')->__('General'),
            'content' => $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_account_edit_tabs_general')->toHtml(),
        ));

        if (Mage::helper('M2ePro/Data_Global')->getValue('temp_data') &&
            Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId()) {

            $isAdvancedMode = Mage::helper('M2ePro/View_Ebay')->isAdvancedMode();

            if ($isAdvancedMode) {
                $this->addTab('listingOther', array(
                    'label'   => Mage::helper('M2ePro')->__('3rd Party Listings'),
                    'title'   => Mage::helper('M2ePro')->__('3rd Party Listings'),
                    'content' => $this->getLayout()
                        ->createBlock('M2ePro/adminhtml_ebay_account_edit_tabs_listingOther')->toHtml(),
                ));
            }

            $this->addTab('store', array(
                     'label'   => Mage::helper('M2ePro')->__('eBay Store'),
                     'title'   => Mage::helper('M2ePro')->__('eBay Store'),
                     'content' => $this->getLayout()
                                       ->createBlock('M2ePro/adminhtml_ebay_account_edit_tabs_store')->toHtml(),
                 ));
            $this->addTab('order', array(
                     'label'   => Mage::helper('M2ePro')->__('Orders'),
                     'title'   => Mage::helper('M2ePro')->__('Orders'),
                     'content' => $this->getLayout()
                                       ->createBlock('M2ePro/adminhtml_ebay_account_edit_tabs_order')->toHtml(),
                 ));

            if ($isAdvancedMode) {
                $this->addTab('feedback', array(
                    'label'   => Mage::helper('M2ePro')->__('Feedback'),
                    'title'   => Mage::helper('M2ePro')->__('Feedback'),
                    'content' => $this->getLayout()
                        ->createBlock('M2ePro/adminhtml_ebay_account_edit_tabs_feedback')->toHtml(),
                ));
            }
        }

        $this->setActiveTab($this->getRequest()->getParam('tab', 'general'));

        return parent::_beforeToHtml();
    }

    //########################################
}