<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_Edit_Tabs extends Ess_M2ePro_Block_Adminhtml_Widget_Tabs
{
    const TAB_ID_GENERAL = 'general';
    const TAB_ID_LISTING_OTHER = 'listingOther';
    const TAB_ID_STORE = 'store';
    const TAB_ID_ORDER = 'order';
    const TAB_ID_INVOICES_AND_SHIPMENTS = 'invoices_and_shipments';
    const TAB_ID_FEEDBACK = 'feedback';

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayAccountEditTabs');

        $this->setTitle(Mage::helper('M2ePro')->__('Configuration'));
        $this->setDestElementId('edit_form');
    }

    protected function _prepareLayout()
    {
        $this->addTab(
            self::TAB_ID_GENERAL,
            array(
                'label'   => Mage::helper('M2ePro')->__('General'),
                'title'   => Mage::helper('M2ePro')->__('General'),
                'content' => $this->getLayout()
                    ->createBlock('M2ePro/adminhtml_ebay_account_edit_tabs_general')->toHtml()
            )
        );

        $this->addTab(
            self::TAB_ID_LISTING_OTHER,
            array(
                'label'   => Mage::helper('M2ePro')->__('Unmanaged Listings'),
                'title'   => Mage::helper('M2ePro')->__('Unmanaged Listings'),
                'content' => $this->getLayout()
                    ->createBlock('M2ePro/adminhtml_ebay_account_edit_tabs_listingOther')->toHtml()
            )
        );

        if (Mage::helper('M2ePro/Data_Global')->getValue('temp_data') &&
            Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId()) {

            $this->addTab(
                self::TAB_ID_STORE,
                array(
                    'label'   => Mage::helper('M2ePro')->__('eBay Store'),
                    'title'   => Mage::helper('M2ePro')->__('eBay Store'),
                    'content' => $this->getLayout()
                        ->createBlock('M2ePro/adminhtml_ebay_account_edit_tabs_store')->toHtml()
                )
            );
            $this->addTab(
                self::TAB_ID_ORDER,
                array(
                    'label'   => Mage::helper('M2ePro')->__('Orders'),
                    'title'   => Mage::helper('M2ePro')->__('Orders'),
                    'content' => $this->getLayout()
                        ->createBlock('M2ePro/adminhtml_ebay_account_edit_tabs_order')->toHtml()
                )
            );

            $this->addTab(
                self::TAB_ID_INVOICES_AND_SHIPMENTS,
                array(
                    'label'   => Mage::helper('M2ePro')->__('Invoices & Shipments'),
                    'title'   => Mage::helper('M2ePro')->__('Invoices & Shipments'),
                    'content' => $this->getLayout()
                        ->createBlock('M2ePro/adminhtml_ebay_account_edit_tabs_InvoicesAndShipments_Form')
                        ->toHtml()
                )
            );

            $this->addTab(
                self::TAB_ID_FEEDBACK,
                array(
                    'label'   => Mage::helper('M2ePro')->__('Feedback'),
                    'title'   => Mage::helper('M2ePro')->__('Feedback'),
                    'content' => $this->getLayout()
                        ->createBlock('M2ePro/adminhtml_ebay_account_edit_tabs_feedback')->toHtml()
                )
            );
        }

        $this->setActiveTab($this->getRequest()->getParam('tab', self::TAB_ID_GENERAL));

        return parent::_prepareLayout();
    }

    //########################################
}
