<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_AmazonShippingOverridePolicy_Installation_MarketplacesSynchronization
    extends Mage_Adminhtml_Block_Template
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('wizardInstallationMarketplacesSynchronization');
        // ---------------------------------------

        $this->setTemplate(
            'M2ePro/wizard/amazonShippingOverridePolicy/installation/marketplacesSynchronization.phtml'
        );
    }

    //########################################

    protected function _beforeToHtml()
    {
        /** @var $marketplace Ess_M2ePro_Model_Marketplace */
        $marketplace = Mage::helper('M2ePro/Component_Amazon')->getModel('Marketplace');
        $collection = $marketplace->getCollection()
                                  ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
                                  ->setOrder('group_title', 'ASC')
                                  ->setOrder('sorder','ASC')
                                  ->setOrder('title','ASC');

        // ---------------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                    'label'   => Mage::helper('M2ePro')->__('Proceed'),
                    'onclick' => "AmazonShippingOverridePolicy."
                                 ."marketplacesSynchronizationAction(this)",
                    'class' => 'process_marketplaces_button'
                ));
        $this->setChild('process_marketplaces_synchronization_button', $buttonBlock);
        // ---------------------------------------

        $this->setData('enabledMarketplaces', $collection->getData());

        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}