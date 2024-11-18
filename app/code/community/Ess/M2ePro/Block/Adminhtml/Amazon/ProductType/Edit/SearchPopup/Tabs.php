<?php

class Ess_M2ePro_Block_Adminhtml_Amazon_ProductType_Edit_SearchPopup_Tabs
    extends Ess_M2ePro_Block_Adminhtml_Widget_Tabs
{
    const TAB_ID_BROWSE = 'browse';
    const TAB_ID_SEARCH = 'search';

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setId('amazonProductTypeSearchPopupTabs');
        $this->setDestElementId('chooser_tabs_container');
    }

    protected function _prepareLayout()
    {
        $blockName = 'M2ePro/adminhtml_amazon_productType_edit_searchPopup_tabs_browse';
        $this->addTab(
            self::TAB_ID_BROWSE, array(
                'label' => Mage::helper('M2ePro')->__('Browse'),
                'title' => Mage::helper('M2ePro')->__('Browse'),
                'content' => $this->getLayout()
                    ->createBlock($blockName)
                    ->toHtml(),
                'active' => true
            )
        );

        $blockName = 'M2ePro/adminhtml_amazon_productType_edit_searchPopup_tabs_search';
        $this->addTab(
            self::TAB_ID_SEARCH, array(
                'label' => Mage::helper('M2ePro')->__('Search'),
                'title' => Mage::helper('M2ePro')->__('Search'),
                'content' => $this->getLayout()
                    ->createBlock($blockName)
                    ->toHtml()
            )
        );

        return parent::_prepareLayout();
    }

}