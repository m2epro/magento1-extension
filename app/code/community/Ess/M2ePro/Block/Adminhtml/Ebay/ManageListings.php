<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_ManageListings extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    const TAB_ID_LISTING = 'listing';
    const TAB_ID_LISTING_OTHER = 'listing_other';
    const TAB_ID_SEARCH = 'search';

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayManageListings');
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Ebay')->getTitle();
            $this->_headerText = Mage::helper('M2ePro')->__('%component_name% / Listings', $componentName);
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('Listings');
        }

        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        // ---------------------------------------
        $this->setTemplate('M2ePro/ebay/manageListings.phtml');
        // ---------------------------------------
    }

    //########################################

    protected function _toHtml()
    {
        /** @var $tabsContainer Ess_M2ePro_Block_Adminhtml_Ebay_ManageListings_Tabs */
        $tabsContainer = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_manageListings_tabs');
        $tabsContainer->setDestElementId('tabs_container');

        $tabsContainer->addTab(self::TAB_ID_LISTING, $this->prepareListingTab());

        if (Mage::helper('M2ePro/View_Ebay')->is3rdPartyShouldBeShown()) {
            $tabsContainer->addTab(self::TAB_ID_LISTING_OTHER, $this->prepareListingOtherTab());
        }

        $tabsContainer->addTab(self::TAB_ID_SEARCH, $this->prepareSearchTab());

        $tabsContainer->setActiveTab($this->getData('tab'));

        return parent::_toHtml() .
               $tabsContainer->toHtml() .
               '<div id="tabs_container"></div>';
    }

    //########################################

    protected function prepareListingTab()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('M2E Pro'),
            'title' => Mage::helper('M2ePro')->__('M2E Pro')
        );

        if ($this->getData('tab') != self::TAB_ID_LISTING) {
            $tab['url'] = $this->getUrl('*/adminhtml_ebay_listing/getListingTab', array('_current' => true));
        } else {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing')->toHtml();
        }

        return $tab;
    }

    protected function prepareListingOtherTab()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('Unmanaged'),
            'title' => Mage::helper('M2ePro')->__('Unmanaged')
        );

        if ($this->getData('tab') != self::TAB_ID_LISTING_OTHER) {
            $tab['url'] = $this->getUrl('*/adminhtml_ebay_listing/getListingOtherTab', array('_current' => true));
        } else {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_other')->toHtml();
        }

        return $tab;
    }

    protected function prepareSearchTab()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('Search'),
            'title' => Mage::helper('M2ePro')->__('Search')
        );

        if ($this->getData('tab') != self::TAB_ID_SEARCH) {
            $tab['url'] = $this->getUrl('*/adminhtml_ebay_listing/getSearchTab', array('_current' => true));
        } else {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_search')->toHtml();
        }

        return $tab;
    }

    //########################################
}
