<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Configuration_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    const TAB_ID_GENERAL                = 'general';
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
        $this->setId('walmartConfigurationTabs');
        $this->setDestElementId('tabs_container');
    }

    //########################################

    protected function _prepareLayout()
    {

        if (Mage::helper('M2ePro/Component_Walmart')->isActive()) {
            $this->addTab(self::TAB_ID_GENERAL, $this->prepareTabGeneral());
        }

        $this->addTab(self::TAB_ID_ACCOUNT, $this->prepareTabAccount());
        $this->addTab(self::TAB_ID_MARKETPLACE, $this->prepareTabMarketplace());
        $this->addTab(self::TAB_ID_TEMPLATE, $this->prepareTabTemplate());
        $this->addTab(self::TAB_ID_SYNCHRONIZATION, $this->prepareTabSynchronization());
        $this->addTab(self::TAB_ID_GLOBAL, $this->prepareTabGlobal());

        $this->setActiveTab($this->getData('active_tab'));

        return parent::_prepareLayout();
    }

    //########################################

    protected function prepareTabGeneral()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('General'),
            'title' => Mage::helper('M2ePro')->__('General')
        );

        if ($this->getData('active_tab') == self::TAB_ID_GENERAL) {
            $tab['content'] = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_walmart_configuration_general_help'
            )->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock(
                'M2ePro/adminhtml_walmart_configuration_general'
            )->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_walmart_general/index');
        }

        return $tab;
    }

    protected function prepareTabMarketplace()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('Marketplaces'),
            'title' => Mage::helper('M2ePro')->__('Marketplaces')
        );

        if ($this->getData('active_tab') == self::TAB_ID_MARKETPLACE) {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_marketplace_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_marketplace')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_walmart_marketplace/index');
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
            $tab['content'] = $this->getLayout()
                ->createBlock('M2ePro/adminhtml_walmart_synchronization_help')->toHtml();

            $tab['content'] .= $this->getLayout()
                ->createBlock('M2ePro/adminhtml_walmart_synchronization')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_walmart_synchronization/index');
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
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_template_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_template')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_walmart_template/index');
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
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_account_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_account')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_walmart_account/index');
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
            $tab['url'] = $this->getUrl('*/adminhtml_walmart_configuration/index');
        }

        return $tab;
    }

    //########################################
}
