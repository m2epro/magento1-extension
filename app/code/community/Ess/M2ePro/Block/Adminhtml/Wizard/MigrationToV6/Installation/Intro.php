<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_MigrationToV6_Installation_Intro
    extends Ess_M2ePro_Block_Adminhtml_Wizard_MigrationToV6_Installation
{
    // ########################################

    protected function getStep()
    {
        return 'intro';
    }

    // ########################################

    protected function _beforeToHtml()
    {
        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__(
            'M2E Pro Migration to v. %version%', Mage::helper('M2ePro/Module')->getVersion()
        );
        //------------------------------

        $nextStep = Mage::helper('M2ePro/Module_Wizard')->getWizard($this->getNick())->getNextStep();
        $onClick = <<<JS
WizardHandlerObj.setStep('{$nextStep}',setLocation.bind(window, location.href));
JS;

        $this->_addButton('next_step_button', array(
            'label' => Mage::helper('M2ePro')->__('Next Step'),
            'onclick' => $onClick,
            'class' => 'next'
        ));

        //------------------------------
        return parent::_beforeToHtml();
    }

    // ########################################
}