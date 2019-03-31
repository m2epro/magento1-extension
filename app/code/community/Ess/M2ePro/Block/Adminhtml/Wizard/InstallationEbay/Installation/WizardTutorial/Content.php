<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationEbay_Installation_WizardTutorial_Content
    extends Mage_Adminhtml_Block_Template
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('wizardInstallationWizardTutorial');
        // ---------------------------------------

        $this->setTemplate('M2ePro/wizard/installationEbay/installation/wizard_tutorial.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $nextStep = Mage::helper('M2ePro/Module_Wizard')->getWizard($this->getNick())->getNextStep();

        $onClick = <<<JS
WizardHandlerObj.setStep('{$nextStep}',setLocation.bind(window, location.href));
JS;

        // ---------------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('M2ePro')->__('Register and Link to eBay'),
                'onclick' => $onClick,
                'class'   => 'start_wizard_button'
            ));
        $this->setChild('start_wizard_button', $buttonBlock);

        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}