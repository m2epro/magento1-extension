<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationEbay_Installation_ProductTutorial_Content
    extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardInstallationProductTutorial');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/installationEbay/installation/product_tutorial.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //-------------------------------
        $nextStep = Mage::helper('M2ePro/Module_Wizard')->getWizard($this->getNick())->getNextStep();

        $onClick = <<<JS
WizardHandlerObj.setStep('{$nextStep}',setLocation.bind(window, location.href));
JS;

        //-------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Continue'),
                'onclick' => $onClick,
                'class'   => 'continue_button'
            ) );
        $this->setChild('continue_button',$buttonBlock);

        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}