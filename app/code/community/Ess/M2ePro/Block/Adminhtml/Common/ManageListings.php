<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_ManageListings extends Ess_M2ePro_Block_Adminhtml_Widget_Container
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
        $this->setId('commonManageListings');
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
        $this->setTemplate('M2ePro/common/manageListings.phtml');
        // ---------------------------------------
    }

    //########################################

    protected function _toHtml()
    {
        /* @var $tabsContainer Ess_M2ePro_Block_Adminhtml_Common_ManageListings_Tabs */
        $tabsContainer = $this->getLayout()->createBlock('M2ePro/adminhtml_common_manageListings_tabs');
        $tabsContainer->setDestElementId('tabs_container');

        $tabsContainer->addTab(self::TAB_ID_LISTING, $this->prepareListingTab());

        $script = '';

        if (Mage::helper('M2ePro/View_Common')->is3rdPartyShouldBeShown(Ess_M2ePro_Helper_Component_Amazon::NICK) ||
            Mage::helper('M2ePro/View_Common')->is3rdPartyShouldBeShown(Ess_M2ePro_Helper_Component_Buy::NICK)) {

            $tabsContainer->addTab(self::TAB_ID_LISTING_OTHER, $this->prepareListingOtherTab());
            $script = $this->getScriptFor3rdPartyControlVisibility($tabsContainer);
        }

        $tabsContainer->addTab(self::TAB_ID_SEARCH, $this->prepareSearchTab());

        $tabsContainer->setActiveTab($this->getActiveTab());

        return parent::_toHtml() .
               $tabsContainer->toHtml() .
               '<div id="tabs_container"></div>' . $script;
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
            $tab['url'] = $this->getUrl('*/adminhtml_common_listing/getListingTab');
        } else {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing')->toHtml();
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
            $tab['url'] = $this->getUrl('*/adminhtml_common_listing/getListingOtherTab');
        } else {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_other')->toHtml();
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
            $tab['url'] = $this->getUrl('*/adminhtml_common_listing/getSearchTab');
        } else {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_search')->toHtml();
        }

        return $tab;
    }

    //########################################

    private function getScriptFor3rdPartyControlVisibility($tabsContainer)
    {
        $listingOtherId = self::TAB_ID_LISTING_OTHER;
        $amazonNick = Ess_M2ePro_Helper_Component_Amazon::NICK;
        $buyNick = Ess_M2ePro_Helper_Component_Buy::NICK;
        $isAmazon3rdPartyShouldBeShown = (int)Mage::helper('M2ePro/View_Common')
            ->is3rdPartyShouldBeShown(Ess_M2ePro_Helper_Component_Amazon::NICK);
        $isBuy3rdPartyShouldBeShown = (int)Mage::helper('M2ePro/View_Common')
            ->is3rdPartyShouldBeShown(Ess_M2ePro_Helper_Component_Buy::NICK);

        return "<script>
                   function change3rdPartyVisibility(event) {
                                var targetId = $(this).readAttribute('id'),
                                    tab = $('{$tabsContainer->getId()}_{$listingOtherId}');

                                if (!tab) {
                                    return true;
                                }

                                if (targetId == 'listing_{$amazonNick}') {
                                    (!{$isAmazon3rdPartyShouldBeShown}) ?
                                        tab.style.display = 'none':
                                        tab.style.display = '';
                                }

                                if (targetId == 'listing_{$buyNick}') {
                                    (!{$isBuy3rdPartyShouldBeShown}) ?
                                        tab.style.display = 'none':
                                        tab.style.display = '';
                                }

                                return true;
                            }
               </script>";
    }

    //########################################
}
