<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationWalmart_Installation_Marketplace
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('wizardInstallationMarketplace');
        // ---------------------------------------

        $this->setTemplate('M2ePro/wizard/installationWalmart/installation/marketplace.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        // ---------------------------------------

        $this->setChild(
            'wizard_marketplace_form',
            $this->getLayout()->createBlock('M2ePro/adminhtml_wizard_installationWalmart_installation_marketplace_form')
        );

        // ---------------------------------------
        $step = 'marketplace';
        $buttonBlock = $this->getLayout()
                        ->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label'   => Mage::helper('M2ePro')->__('Proceed'),
                            'onclick' => 'WizardInstallationWalmartMarketplaceHandlerObj.proceedAction(\''.$step.'\');',
                            'class' => 'process_marketplace_button'
                        ));
        $this->setChild('process_marketplace_button', $buttonBlock);
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        return '<div id="marketplaces_progress_bar"></div>' .
               '<div id="marketplaces_content_container">' .
               parent::_toHtml() .
               '</div>';
    }

    //########################################
}