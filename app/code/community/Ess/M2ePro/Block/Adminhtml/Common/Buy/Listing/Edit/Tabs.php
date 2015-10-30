<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Listing_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('buyListingEditTabs');
        // ---------------------------------------

        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setDestElementId('edit_form');
    }

    protected function _beforeToHtml()
    {
        $this->addTab('selling', array(
            'label'   => Mage::helper('M2ePro')->__('Selling Settings'),
            'title'   => Mage::helper('M2ePro')->__('Selling Settings'),
            'content' => $this->getLayout()
                              ->createBlock('M2ePro/adminhtml_common_buy_listing_add_tabs_selling')
                              ->toHtml(),
        ));

        $this->addTab('search', array(
            'label'   => Mage::helper('M2ePro')->__('Search Settings'),
            'title'   => Mage::helper('M2ePro')->__('Search Settings'),
            'content' => $this->getLayout()
                              ->createBlock('M2ePro/adminhtml_common_buy_listing_add_tabs_search')
                              ->toHtml(),
        ));

        $this->setActiveTab($this->getRequest()->getParam('tab', 'selling'));

        return parent::_beforeToHtml();
    }

    //########################################
}