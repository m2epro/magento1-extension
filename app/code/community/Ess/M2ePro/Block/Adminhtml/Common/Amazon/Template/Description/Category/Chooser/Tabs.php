<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_Description_Category_Chooser_Tabs
    extends Mage_Adminhtml_Block_Widget_Tabs
{
    // ########################################

    const TAB_ID_RECENT = 'recent';
    const TAB_ID_BROWSE = 'browse';
    const TAB_ID_SEARCH = 'search';

    // ########################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setId('amazonTemplateDescriptionCategoryChooserTabs');
        $this->setDestElementId('chooser_tabs_container');
    }

    // ########################################

    protected function _prepareLayout()
    {
        $blockName = 'M2ePro/adminhtml_common_amazon_template_description_category_chooser_tabs_recent';
        !$this->isNeedToHideRecent() && $this->addTab(self::TAB_ID_RECENT, array(
            'label'   => Mage::helper('M2ePro')->__('Recently Used'),
            'title'   => Mage::helper('M2ePro')->__('Recently Used'),
            'content' => $this->getLayout()
                              ->createBlock($blockName)
                              ->toHtml(),
            'active'  => true
        ));

        $blockName = 'M2ePro/adminhtml_common_amazon_template_description_category_chooser_tabs_browse';
        $this->addTab(self::TAB_ID_BROWSE, array(
            'label'   => Mage::helper('M2ePro')->__('Browse'),
            'title'   => Mage::helper('M2ePro')->__('Browse'),
            'content' => $this->getLayout()
                              ->createBlock($blockName)
                              ->toHtml(),
            'active'  => $this->isNeedToHideRecent() ? true : false
        ));

        $blockName = 'M2ePro/adminhtml_common_amazon_template_description_category_chooser_tabs_search';
        $this->addTab(self::TAB_ID_SEARCH, array(
            'label'   => Mage::helper('M2ePro')->__('Search'),
            'title'   => Mage::helper('M2ePro')->__('Search'),
            'content' => $this->getLayout()
                              ->createBlock($blockName)
                              ->toHtml()
        ));

        return parent::_prepareLayout();
    }

    // ########################################

    public function isNeedToHideRecent()
    {
        return Mage::helper('M2ePro/Data_Global')->getValue('category_chooser_hide_recent', true);
    }

    // ########################################
}