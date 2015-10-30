<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Chooser_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    const TAB_ID_RECENT      = 'recent';
    const TAB_ID_BROWSE      = 'browse';
    const TAB_ID_SEARCH      = 'search';
    const TAB_ID_ATTRIBUTE   = 'attribute';

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setId('ebayListingCategoryChooserTabs');
        $this->setDestElementId('chooser_tabs_container');
    }

    //########################################

    protected function _prepareLayout()
    {
        $hideRecent = Mage::helper('M2ePro/Data_Global')->getValue('category_chooser_hide_recent');

        !$hideRecent && $this->addTab(self::TAB_ID_RECENT, array(
            'label'   => Mage::helper('M2ePro')->__('Recently Used'),
            'title'   => Mage::helper('M2ePro')->__('Recently Used'),
            'content' => $this->getLayout()
                              ->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser_tabs_recent')
                              ->toHtml(),
            'active'  => true
        ));
        $this->addTab(self::TAB_ID_BROWSE, array(
            'label'   => Mage::helper('M2ePro')->__('Browse'),
            'title'   => Mage::helper('M2ePro')->__('Browse'),
            'content' => $this->getLayout()
                              ->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser_tabs_browse')
                              ->toHtml(),
            'active'  => $hideRecent ? true : false
        ));
        $this->addTab(self::TAB_ID_SEARCH, array(
            'label'   => Mage::helper('M2ePro')->__('Search'),
            'title'   => Mage::helper('M2ePro')->__('Search'),
            'content' => $this->getLayout()
                              ->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser_tabs_search')
                              ->toHtml()
        ));
        Mage::helper('M2ePro/View_Ebay')->isAdvancedMode() && $this->addTab(self::TAB_ID_ATTRIBUTE, array(
            'label'   => Mage::helper('M2ePro')->__('Magento Attribute'),
            'title'   => Mage::helper('M2ePro')->__('Magento Attribute'),
            'content' => $this->getLayout()
                              ->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser_tabs_attribute')
                              ->toHtml()
        ));

        return parent::_prepareLayout();
    }

    //########################################
}