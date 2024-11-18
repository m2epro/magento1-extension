<?php

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationAmazon_Installation_Settings_Content
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('wizardInstallationAmazonSettingsContent');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_configuration';
        $this->_mode = 'general';
        // ---------------------------------------

        $this->_headerText = '';

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        // -------------------
    }

    protected function _prepareLayout()
    {
        /** @var Ess_M2ePro_Helper_Module_Support $supportHelper */
        $supportHelper = Mage::helper('M2ePro/Module_Support');

        $this->getLayout()->getBlock('wizard.help.block')->setContent(
            Mage::helper('M2ePro')->__(
                <<<HTML
                In this section, you can configure the general settings for the interaction between M2E Pro and
                Amazon Marketplaces.<br/><br/>

                Specify Product Identifier values for your Amazon items at a global level.<br/>
                If you prefer to set product IDs per listing, please navigate
                to Listings > Items > Edit Settings > Product Identifiers.<br/><br/>

                Enable <a href="%url%" target="_blank">Amazon Business (B2B)</a> to apply the
                Business Price and QTY Discounts to your offers on the selected marketplaces.
HTML
                ,
                $supportHelper->getDocumentationUrl(null, null, 'help/m2/amazon-integration/amazon-business')
            )
        );

        parent::_prepareLayout();
    }
}