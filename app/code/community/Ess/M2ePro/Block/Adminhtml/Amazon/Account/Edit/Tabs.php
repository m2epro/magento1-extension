<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Account_Edit_Tabs extends Ess_M2ePro_Block_Adminhtml_Widget_Tabs
{
    const TAB_ID_GENERAL                = 'general';
    const TAB_ID_LISTING_OTHER          = 'listingOther';
    const TAB_ID_ORDERS                 = 'orders';
    const TAB_ID_INVOICES_AND_SHIPMENTS = 'invoices_and_shipments';
    const TAB_ID_REPRICING              = 'repricing';

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonAccountEditTabs');

        $this->setTitle(Mage::helper('M2ePro')->__('Configuration'));
        $this->setDestElementId('edit_form');
    }

    protected function _prepareLayout()
    {
        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Data_Global')->getValue('model_account');

        $this->addTab(
            self::TAB_ID_GENERAL,
            array(
                'label'   => Mage::helper('M2ePro')->__('General'),
                'title'   => Mage::helper('M2ePro')->__('General'),
                'content' => $this->getLayout()
                    ->createBlock('M2ePro/adminhtml_amazon_account_edit_tabs_general')
                    ->toHtml(),
            )
        );

        $this->addTab(
            self::TAB_ID_LISTING_OTHER,
            array(
                'label'   => Mage::helper('M2ePro')->__('Unmanaged Listings'),
                'title'   => Mage::helper('M2ePro')->__('Unmanaged Listings'),
                'content' => $this->getLayout()
                    ->createBlock('M2ePro/adminhtml_amazon_account_edit_tabs_listingOther')
                    ->toHtml(),
            )
        );

        $this->addTab(
            self::TAB_ID_ORDERS,
            array(
                'label'   => Mage::helper('M2ePro')->__('Orders'),
                'title'   => Mage::helper('M2ePro')->__('Orders'),
                'content' => $this->getLayout()
                    ->createBlock('M2ePro/adminhtml_amazon_account_edit_tabs_order')
                    ->toHtml(),
            )
        );

        if ($account->getId()) {
            $this->addTab(
                self::TAB_ID_INVOICES_AND_SHIPMENTS,
                array(
                    'label'   => Mage::helper('M2ePro')->__('Invoices & Shipments'),
                    'title'   => Mage::helper('M2ePro')->__('Invoices & Shipments'),
                    'content' => $this->getLayout()
                        ->createBlock('M2ePro/adminhtml_amazon_account_edit_tabs_InvoicesAndShipments_Form')
                        ->toHtml(),
                )
            );
        }

        if ($account->getId()) {
            $this->addTab(
                self::TAB_ID_REPRICING,
                array(
                    'label'   => Mage::helper('M2ePro')->__('Repricing Tool'),
                    'title'   => Mage::helper('M2ePro')->__('Repricing Tool'),
                    'content' => $this->getLayout()
                        ->createBlock('M2ePro/adminhtml_amazon_account_edit_tabs_repricing')
                        ->toHtml(),
                )
            );
        }

        $this->setActiveTab($this->getRequest()->getParam('tab', self::TAB_ID_GENERAL));

        return parent::_prepareLayout();
    }
}
