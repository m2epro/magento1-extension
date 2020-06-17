<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Category_View_Tabs extends Ess_M2ePro_Block_Adminhtml_Widget_Tabs
{
    const TAB_ID_ITEM_SPECIFICS = 'item_specifics';
    const TAB_ID_PRODUCTS_PRIMARY = 'products_primary';
    const TAB_ID_PRODUCTS_SECONDARY = 'products_secondary';

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setId('ebayConfigurationCategoryViewTabs');
        $this->setDestElementId('tabs_container');
    }

    //########################################

    protected function _prepareLayout()
    {
        $this->addTab(self::TAB_ID_ITEM_SPECIFICS, $this->prepareTabItemSpecifics());
        $this->addTab(self::TAB_ID_PRODUCTS_PRIMARY, $this->prepareTabProductsPrimary());
        $this->addTab(self::TAB_ID_PRODUCTS_SECONDARY, $this->prepareTabProductsSecondary());

        $this->setActiveTab($this->getActiveTab());

        return parent::_prepareLayout();
    }

    //########################################

    protected function prepareTabItemSpecifics()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('Item Specifics (Default)'),
            'title' => Mage::helper('M2ePro')->__('Item Specifics (Default)')
        );

        if ($this->getActiveTab() == self::TAB_ID_ITEM_SPECIFICS) {
            $tab['content'] = $this->getLayout()
                ->createBlock('M2ePro/adminhtml_ebay_configuration_category_view_tabs_itemSpecifics_edit')->toHtml();
        } else {
            $tab['url'] = $this->getUrl(
                '*/adminhtml_ebay_category/view',
                array(
                    'active_tab' => self::TAB_ID_ITEM_SPECIFICS,
                    'template_id' => $this->getTemplateCategoryId()
                )
            );
        }

        return $tab;
    }

    protected function prepareTabProductsPrimary()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('Primary Category'),
            'title' => Mage::helper('M2ePro')->__('Products with Primary Category')
        );

        if ($this->getActiveTab() == self::TAB_ID_PRODUCTS_PRIMARY) {
            $tab['content'] = $this->getLayout()
                ->createBlock('M2ePro/adminhtml_ebay_configuration_category_view_tabs_productsPrimary')->toHtml();
        } else {
            $tab['url'] = $this->getUrl(
                '*/adminhtml_ebay_category/view',
                array(
                    'active_tab' => self::TAB_ID_PRODUCTS_PRIMARY,
                    'template_id' => $this->getTemplateCategoryId()
                )
            );
        }

        return $tab;
    }

    protected function prepareTabProductsSecondary()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('Secondary Category'),
            'title' => Mage::helper('M2ePro')->__('Products with Secondary Category')
        );

        if ($this->getActiveTab() == self::TAB_ID_PRODUCTS_SECONDARY) {
            $tab['content'] = $this->getLayout()
                ->createBlock('M2ePro/adminhtml_ebay_configuration_category_view_tabs_productsSecondary')->toHtml();
        } else {
            $tab['url'] = $this->getUrl(
                '*/adminhtml_ebay_category/view',
                array(
                    'active_tab' => self::TAB_ID_PRODUCTS_SECONDARY,
                    'template_id' => $this->getTemplateCategoryId()
                )
            );
        }

        return $tab;
    }

    //########################################

    protected function getActiveTab()
    {
        return $this->getRequest()->getParam('active_tab', self::TAB_ID_ITEM_SPECIFICS);
    }

    protected function getTemplateCategoryId()
    {
        return $this->getRequest()->getParam('template_id');
    }

    //########################################
}
