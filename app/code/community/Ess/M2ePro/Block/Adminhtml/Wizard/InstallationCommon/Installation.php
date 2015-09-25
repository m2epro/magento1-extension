<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationCommon_Installation
    extends Ess_M2ePro_Block_Adminhtml_Wizard_Installation
{
    public $isLicenseStepFinished = false;

    // ########################################

    protected function _beforeToHtml()
    {
        /** @var Ess_M2ePro_Helper_Module_Wizard $wizardHelper */
        $wizardHelper = $this->helper('M2ePro/Module_Wizard');

        $earlierFormData = Mage::getModel('M2ePro/Registry')->load('/wizard/license_form_data/', 'key')
                                                            ->getData('value');

        if (Mage::helper('M2ePro/Module_License')->getKey() && $earlierFormData) {
            $this->isLicenseStepFinished = true;
        }

        if ($this->isLicenseStepFinished && $wizardHelper->getStep($this->getNick()) == 'license') {
            $nextStep = $wizardHelper->getWizard($this->getNick())->getNextStep();
            $wizardHelper->setStep($this->getNick(), $nextStep);
        }

        //------------------------------
        $block = $wizardHelper->createBlock('installation_description', $this->getNick());
        $this->setChild('description_block', $block);

        $block = $wizardHelper->createBlock('installation_license', $this->getNick());
        $block->setData('isLicenseStepFinished', $this->isLicenseStepFinished);
        $this->setChild('step_license', $block);

        $block = $wizardHelper->createBlock('installation_settings', $this->getNick());
        $this->setChild('step_settings', $block);
        //------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################

    protected function getHeaderTextHtml()
    {
        return 'Configuration Wizard (Magento Multi-Channels Integration)';
    }

    protected function _toHtml()
    {
        $urls = json_encode(Mage::helper('M2ePro')->getControllerActions('adminhtml_wizard_installationCommon'));

        $additionalJs = <<<SCRIPT
<script type="text/javascript">
    M2ePro.url.add({$urls});
    InstallationCommonWizardObj = new WizardInstallationCommon();
</script>
SCRIPT;

        return parent::_toHtml()
            . $additionalJs
            . $this->getChildHtml('description_block')
            . $this->getChildHtml('step_license')
            . $this->getChildHtml('step_settings');
    }

    // ########################################
}