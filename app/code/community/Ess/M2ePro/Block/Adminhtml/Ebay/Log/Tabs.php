<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Log_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    const TAB_ID_LISTING            = 'listing';
    const TAB_ID_LISTING_OTHER      = 'listing_other';
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

        if (Mage::helper('M2ePro/View_Ebay')->isAdvancedMode() &&
            Mage::helper('M2ePro/View_Ebay')->is3rdPartyShouldBeShown()) {

            $this->addTab(self::TAB_ID_LISTING_OTHER, $this->prepareTabListingOther());
        }

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
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_log_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_log')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_ebay_log/listing');
        }

        return $tab;
    }

    protected function prepareTabListingOther()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('3rd Party Listings'),
            'title' => Mage::helper('M2ePro')->__('3rd Party Listings')
        );

        if ($this->getData('active_tab') == self::TAB_ID_LISTING_OTHER) {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_other_log_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_other_log')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_ebay_log/listingOther');
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
            $tab['content'] = $this->getLayout()
                                   ->createBlock('M2ePro/adminhtml_ebay_synchronization_log_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_synchronization_log')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_ebay_log/synchronization');
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
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_order_log_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_order_log')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_ebay_log/order');
        }

        return $tab;
    }

    //########################################
}