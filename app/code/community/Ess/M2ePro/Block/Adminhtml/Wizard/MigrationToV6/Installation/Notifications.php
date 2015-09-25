<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_MigrationToV6_Installation_Notifications
    extends Ess_M2ePro_Block_Adminhtml_Wizard_MigrationToV6_Installation
{
    // ########################################

    protected function getStep()
    {
        return 'notifications';
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
        $completeStatus = Ess_M2ePro_Helper_Module_Wizard::STATUS_COMPLETED;
        $nextOnClick = <<<JS
WizardHandlerObj.setStatus('{$completeStatus}',setLocation.bind(window, location.href));
JS;

        $this->_addButton('complete_button', array(
            'label' => Mage::helper('M2ePro')->__('Complete'),
            'onclick' => $nextOnClick,
            'class' => 'save'
        ));
        //------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}