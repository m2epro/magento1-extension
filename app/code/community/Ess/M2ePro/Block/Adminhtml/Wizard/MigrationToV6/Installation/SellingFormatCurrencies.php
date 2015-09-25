<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_MigrationToV6_Installation_SellingFormatCurrencies
    extends Ess_M2ePro_Block_Adminhtml_Wizard_MigrationToV6_Installation
{
    // ########################################

    protected function getStep()
    {
        return 'sellingFormatCurrencies';
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

        //------------------------------
        $previousStep = Mage::helper('M2ePro/Module_Wizard')->getWizard($this->getNick())->getPrevStep();
        $previousOnClick = <<<JS
WizardHandlerObj.setStep('{$previousStep}',setLocation.bind(window, location.href));
JS;

        $this->_addButton('previous_step_button', array(
            'label' => Mage::helper('M2ePro')->__('Previous Step'),
            'onclick' => $previousOnClick,
            'class' => 'back'
        ));
        //------------------------------

        //------------------------------
        $nextOnClick = <<<JS
saveCurrencies();
JS;

        $this->_addButton('next_step_button', array(
            'label' => Mage::helper('M2ePro')->__('Next Step'),
            'onclick' => $nextOnClick,
            'class' => 'next'
        ));
        //------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}