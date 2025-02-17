<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Configuration_Tabs extends Ess_M2ePro_Block_Adminhtml_Widget_Tabs
{
    const TAB_ID_GENERAL                = 'general';
    const TAB_ID_ACCOUNT                = 'account';
    const TAB_ID_MARKETPLACE            = 'marketplace';
    const TAB_ID_SYNCHRONIZATION        = 'synchronization';
    const TAB_ID_GLOBAL                 = 'global';
    const TAB_ID_TEMPLATE               = 'template';
    const TAB_ID_PRODUCT_TYPE           = 'product_type';
    const TAB_ID_MAPPING                = 'mapping';

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setId('amazonConfigurationTabs');
        $this->setDestElementId('tabs_container');
    }

    //########################################

    protected function _prepareLayout()
    {
        if (Mage::helper('M2ePro/Component_Amazon')->isEnabled()) {
            $this->addTab(self::TAB_ID_GENERAL, $this->prepareTabGeneral());
        }

        $this->addTab(self::TAB_ID_ACCOUNT, $this->prepareTabAccount());
        $this->addTab(self::TAB_ID_TEMPLATE, $this->prepareTabTemplate());
        $this->addTab(self::TAB_ID_PRODUCT_TYPE, $this->prepareTabProductType());
        $this->addTab(self::TAB_ID_MAPPING, $this->prepareTabMapping());
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
                'M2ePro/adminhtml_amazon_configuration_general_help'
            )->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock(
                'M2ePro/adminhtml_amazon_configuration_general'
            )->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_amazon_general/index');
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
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_synchronization_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_synchronization')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_amazon_synchronization/index');
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
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_template_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_template')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_amazon_template/index');
        }

        return $tab;
    }

    private function prepareTabProductType()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('Product Types'),
            'title' => Mage::helper('M2ePro')->__('Product Types')
        );

        if ($this->getData('active_tab') == self::TAB_ID_PRODUCT_TYPE) {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_productType_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_productType')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_amazon_productTypes/index');
        }

        return $tab;
    }

    private function prepareTabMapping()
    {
        $tab = array(
            'label' => Mage::helper('M2ePro')->__('Mapping'),
            'title' => Mage::helper('M2ePro')->__('Mapping')
        );

        if ($this->getData('active_tab') == self::TAB_ID_MAPPING) {
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_mapping_edit')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_amazon_mapping/index');
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
            $tab['content'] = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_account_help')->toHtml();
            $tab['content'] .= $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_account')->toHtml();
        } else {
            $tab['url'] = $this->getUrl('*/adminhtml_amazon_account/index');
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
            $tab['url'] = $this->getUrl('*/adminhtml_amazon_configuration/index');
        }

        return $tab;
    }

    //########################################
}
