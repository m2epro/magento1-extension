<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
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
        $this->_headerText = Mage::helper('M2ePro')->__('Listings');
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
        /* @var $tabsContainer Ess_M2ePro_Block_Adminhtml_Ebay_ManageListings_Tabs */
        $tabsContainer = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_manageListings_tabs');
        $tabsContainer->setDestElementId('tabs_container');

        $tabsContainer->addTab(self::TAB_ID_LISTING, $this->prepareListingTab());

        if (Mage::helper('M2ePro/View_Ebay')->isAdvancedMode() &&
            Mage::helper('M2ePro/View_Ebay')->is3rdPartyShouldBeShown()) {

            $tabsContainer->addTab(self::TAB_ID_LISTING_OTHER, $this->prepareListingOtherTab());
        }

        $tabsContainer->addTab(self::TAB_ID_SEARCH, $this->prepareSearchTab());

        $tabsContainer->setActiveTab($this->getActiveTab());

        return parent::_toHtml() .
               $tabsContainer->toHtml() .
               '<div id="tabs_container"></div>';
    }

    //########################################

    protected function getActiveTab()
    {
        return $this->getRequest()->getParam('tab', self::TAB_ID_LISTING);
    }

    //########################################

    private function prepareListingTab()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('M2E Pro'),
            'title' => Mage::helper('M2ePro')->__('M2E Pro')
        );

        if ($this->getActiveTab() != self::TAB_ID_LISTING) {
            $tab['class'] = 'ajax';
            $tab['url'] = $this->getUrl('*/adminhtml_ebay_listing/getListingTab');
        } else {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing')->toHtml();
        }

        return $tab;
    }

    private function prepareListingOtherTab()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('3rd Party'),
            'title' => Mage::helper('M2ePro')->__('3rd Party')
        );

        if ($this->getActiveTab() != self::TAB_ID_LISTING_OTHER) {
            $tab['class'] = 'ajax';
            $tab['url'] = $this->getUrl('*/adminhtml_ebay_listing/getListingOtherTab');
        } else {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_other')->toHtml();
        }

        return $tab;
    }

    private function prepareSearchTab()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('Search'),
            'title' => Mage::helper('M2ePro')->__('Search')
        );

        if ($this->getActiveTab() != self::TAB_ID_SEARCH) {
            $tab['class'] = 'ajax';
            $tab['url'] = $this->getUrl('*/adminhtml_ebay_listing/getSearchTab');
        } else {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_search')->toHtml();
        }

        return $tab;
    }

    //########################################
}
