<?php


class Ess_M2ePro_Block_Adminhtml_Wizard_MigrationToInnodb_Installation_MarketplacesSynchronization_Content
    extends Mage_Adminhtml_Block_Template
{
    protected $_enabledMarketplaces = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('wizardInstallationMarketplacesSynchronization');
        $this->setTemplate('M2ePro/wizard/migrationToInnodb/installation/marketplacesSynchronization.phtml');
    }

    //########################################

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('wizard.help.block')->setContent(
            Mage::helper('M2ePro')->__(
                <<<HTML
Click <b>Continue</b> to synchronize the Marketplaces enabled in your Account configuration.
HTML
            )
        );

        parent::_prepareLayout();
    }

    //########################################

    protected function _beforeToHtml()
    {
        $collection = Mage::getModel('M2ePro/Marketplace')->getCollection();
        $collection->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);

        foreach ($collection->getItems() as $marketplace) {
            /** @var Ess_M2ePro_Model_Marketplace $marketplace */
            if (!$marketplace->getResource()->isDictionaryExist($marketplace)) {
                $component = Mage::helper('M2ePro/Component')->getComponentTitle($marketplace->getComponentMode());
                $this->_enabledMarketplaces[$component][] = $marketplace;
            }
        }

        return parent::_beforeToHtml();
    }

    //########################################

    public function getEnabledMarketplaces()
    {
        return $this->_enabledMarketplaces;
    }

    //########################################
}
