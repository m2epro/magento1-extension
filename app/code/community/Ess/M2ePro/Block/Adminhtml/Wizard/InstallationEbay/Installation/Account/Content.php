<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationEbay_Installation_Account_Content
    extends Mage_Adminhtml_Block_Template
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('wizardInstallationAccount');
        // ---------------------------------------

        $this->setTemplate('M2ePro/wizard/installationEbay/installation/account.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $this->setData(
            'account_id',
            Mage::helper('M2ePro/Component_Ebay')->getCollection('Account')->getLastItem()->getId()
        );
        // ---------------------------------------

        // ---------------------------------------
        $nextStep = Mage::helper('M2ePro/Module_Wizard')->getWizard($this->getNick())->getNextStep();
        $onClick = <<<JS
WizardHandlerObj.setStep('{$nextStep}',setLocation.bind(window, location.href));
JS;

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('M2ePro')->__('Create an M2E Pro Listing'),
                'onclick' => $onClick,
                'id' => 'create_listing_button'
            ));
        $this->setChild('create_listing_button', $buttonBlock);
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}