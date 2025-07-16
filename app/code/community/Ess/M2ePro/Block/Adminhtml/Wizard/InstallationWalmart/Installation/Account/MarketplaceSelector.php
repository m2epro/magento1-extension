<?php

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationWalmart_Installation_Account_MarketplaceSelector extends
    Mage_Adminhtml_Block_Abstract

{
    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/wizard/installationWalmart/account/marketplace_selector.phtml');
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace[]
     */
    public function getMarketplaces()
    {
        /** @var Ess_M2ePro_Model_Resource_Marketplace_Collection $marketplacesCollection */
        $marketplacesCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Marketplace')
            ->addFieldToFilter('developer_key', array('notnull' => true))
            ->setOrder('sorder', 'ASC');

       return $marketplacesCollection->getItems();
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Button
     */
    public function getConnectButton()
    {
        $url = $this->getUrl(
            '*/adminhtml_wizard_walmart_beforeGetToken/beforeGetToken',
            array(
                'marketplace_id' => Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_US,
                '_current'       => true,
            )
        );

        /** @var Mage_Adminhtml_Block_Widget_Button $button */
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(
            array(
                'id' => 'account_us_connect',
                'label' => Mage::helper('M2ePro')->__('Connect'),
                'onclick' => 'setLocation(\'' . $url . '\')',
                'class' => 'check M2ePro_check_button primary',
            )
        );

        return $button;
    }
}