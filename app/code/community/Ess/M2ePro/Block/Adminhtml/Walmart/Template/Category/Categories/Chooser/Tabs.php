<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Template_Category_Categories_Chooser_Tabs
    extends Mage_Adminhtml_Block_Widget_Tabs
{
    //########################################

    const TAB_ID_RECENT = 'recent';
    const TAB_ID_BROWSE = 'browse';
    const TAB_ID_SEARCH = 'search';

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setId('walmartTemplateCategoryCategoriesChooserTabs');
        $this->setDestElementId('chooser_tabs_container');
    }

    //########################################

    protected function _prepareLayout()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Walmart_Template_Category_Categories_Chooser_Tabs_Recent $recentBlock */
        $recentBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_template_category_categories_chooser_tabs_recent'
        );
        $isRecentTabsShouldBeShown = $recentBlock->shouldBeShown();

        $isRecentTabsShouldBeShown && $this->addTab(
            self::TAB_ID_RECENT, array(
            'label'   => Mage::helper('M2ePro')->__('Recently Used'),
            'title'   => Mage::helper('M2ePro')->__('Recently Used'),
            'content' => $recentBlock->toHtml(),
            'active'  => true
            )
        );

        $blockName = 'M2ePro/adminhtml_walmart_template_category_categories_chooser_tabs_browse';
        $this->addTab(
            self::TAB_ID_BROWSE, array(
            'label'   => Mage::helper('M2ePro')->__('Browse'),
            'title'   => Mage::helper('M2ePro')->__('Browse'),
            'content' => $this->getLayout()
                              ->createBlock($blockName)
                              ->toHtml(),
            'active'  => $isRecentTabsShouldBeShown ? false : true
            )
        );

        $blockName = 'M2ePro/adminhtml_walmart_template_category_categories_chooser_tabs_search';
        $this->addTab(
            self::TAB_ID_SEARCH, array(
            'label'   => Mage::helper('M2ePro')->__('Search'),
            'title'   => Mage::helper('M2ePro')->__('Search'),
            'content' => $this->getLayout()
                              ->createBlock($blockName)
                              ->toHtml()
            )
        );

        return parent::_prepareLayout();
    }

    //########################################

    public function isNeedToHideRecent()
    {
        return Mage::helper('M2ePro/Data_Global')->getValue('categories_chooser_hide_recent', true);
    }

    //########################################
}
