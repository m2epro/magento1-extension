<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Configuration_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    const TAB_ID_ACCOUNT                = 'account';
    const TAB_ID_MARKETPLACE            = 'marketplace';
    const TAB_ID_SYNCHRONIZATION        = 'synchronization';
    const TAB_ID_GLOBAL                 = 'global';
    const TAB_ID_TEMPLATE               = 'template';
    const TAB_ID_DESCRIPTION_TEMPLATE   = 'description_template';

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setId('commonConfigurationTabs');
        $this->setDestElementId('tabs_container');
    }

    //########################################

    protected function _prepareLayout()
    {

        $this->addTab(self::TAB_ID_ACCOUNT, $this->prepareTabAccount());
        $this->addTab(self::TAB_ID_MARKETPLACE, $this->prepareTabMarketplace());
        $this->addTab(self::TAB_ID_TEMPLATE, $this->prepareTabTemplate());
        $this->addTab(self::TAB_ID_SYNCHRONIZATION, $this->prepareTabSynchronization());
        $this->addTab(self::TAB_ID_GLOBAL, $this->prepareTabGlobal());

        $this->setActiveTab($this->getData('active_tab'));

        return parent::_prepareLayout();
    }

    //########################################

    protected function prepareTabMarketplace()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('Marketplaces'),
            'title' => Mage::helper('M2ePro')->__('Marketplaces')
        );

        if ($this->getData('active_tab') == self::TAB_ID_MARKETPLACE) {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_common_marketplace_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_common_marketplace')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_common_marketplace/index');
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
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_common_synchronization_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_common_synchronization')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_common_synchronization/index');
        }

        return $tab;
    }

    protected function prepareTabTemplate()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('Policies'),
            'title' => Mage::helper('M2ePro')->__('Policies')
        );

        if ($this->getData('active_tab') == self::TAB_ID_TEMPLATE) {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_Common_template_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_common_' .
                $this->getActiveChannelTab() . '_template')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_common_template/index', array(
                'channel' => $this->getActiveChannelTab()
            ));
        }

        return $tab;
    }

    protected function prepareTabAccount()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('Accounts'),
            'title' => Mage::helper('M2ePro')->__('Accounts')
        );

        if ($this->getData('active_tab') == self::TAB_ID_ACCOUNT) {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_common_account_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_common_account')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_common_account/index');
        }

        return $tab;
    }

    protected function prepareTabGlobal()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('Global Settings'),
            'title' => Mage::helper('M2ePro')->__('Global Settings')
        );

        if ($this->getData('active_tab') == self::TAB_ID_GLOBAL) {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_configuration_linker')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_common_configuration/index');
        }

        return $tab;
    }

    //########################################

    protected function getActiveChannelTab()
    {
        $activeTab = $this->getRequest()->getParam('channel');
        if (is_null($activeTab)) {
            Mage::helper('M2ePro/View_Common_Component')->isAmazonDefault() &&
                $activeTab = Ess_M2ePro_Block_Adminhtml_Common_Template_Tabs::TAB_ID_AMAZON;
            Mage::helper('M2ePro/View_Common_Component')->isBuyDefault()    &&
                $activeTab = Ess_M2ePro_Block_Adminhtml_Common_Template_Tabs::TAB_ID_BUY;
        }

        return $activeTab;
    }

    //########################################
}