<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Log_Tabs extends Ess_M2ePro_Block_Adminhtml_Widget_Tabs
{
    const TAB_ID_LISTING            = 'listing';
    const TAB_ID_ORDER              = 'order';
    const TAB_ID_SYNCHRONIZATION    = 'synchronization';

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setId('ebayLogTabs');
        $this->setDestElementId('tabs_container');
    }

    //########################################

    protected function _prepareLayout()
    {
        $this->addTab(self::TAB_ID_LISTING, $this->prepareTabListing());
        $this->addTab(self::TAB_ID_ORDER, $this->prepareTabOrder());
        $this->addTab(self::TAB_ID_SYNCHRONIZATION, $this->prepareTabSynchronization());

        $this->setActiveTab($this->getData('active_tab'));

        return parent::_prepareLayout();
    }

    //########################################

    protected function prepareTabListing()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('Listings'),
            'title' => Mage::helper('M2ePro')->__('Listings')
        );

        if ($this->getData('active_tab') == self::TAB_ID_LISTING) {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_log_listing_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_log_listing_view')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_ebay_log/listing');
        }

        return $tab;
    }

    protected function prepareTabOrder()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('Orders'),
            'title' => Mage::helper('M2ePro')->__('Orders')
        );

        if ($this->getData('active_tab') == self::TAB_ID_ORDER) {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_log_order_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock(
                'M2ePro/adminhtml_ebay_log_order', '', array(
                    'component_mode' => Ess_M2ePro_Helper_Component_Ebay::NICK
                )
            )->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_ebay_log/order');
        }

        return $tab;
    }

    protected function prepareTabSynchronization()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('Synchronization'),
            'title' => Mage::helper('M2ePro')->__('Synchronization')
        );

        if ($this->getData('active_tab') == self::TAB_ID_SYNCHRONIZATION) {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_log_synchronization')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_ebay_log/synchronization');
        }

        return $tab;
    }

    //########################################
}
