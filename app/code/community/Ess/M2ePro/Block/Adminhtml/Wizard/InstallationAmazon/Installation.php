<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationAmazon_Installation
    extends Ess_M2ePro_Block_Adminhtml_Wizard_Installation
{
    public $isLicenseStepFinished = false;

    //########################################

    protected function _beforeToHtml()
    {
        /** @var Ess_M2ePro_Helper_Module_Wizard $wizardHelper */
        $wizardHelper = $this->helper('M2ePro/Module_Wizard');

        $earlierFormData = Mage::getModel('M2ePro/Registry')->load('/wizard/license_form_data/', 'key')
            ->getSettings('value');

        if (Mage::helper('M2ePro/Module_License')->getKey() && !empty($earlierFormData)) {
            $this->isLicenseStepFinished = true;

            if (empty($earlierFormData['email']) ||
                empty($earlierFormData['firstname']) ||
                empty($earlierFormData['lastname']) ||
                empty($earlierFormData['phone']) ||
                empty($earlierFormData['country']) ||
                empty($earlierFormData['city']) ||
                empty($earlierFormData['postal_code'])
            ) {
                $this->isLicenseStepFinished = false;
            }
        }

        if ($this->isLicenseStepFinished && $wizardHelper->getStep($this->getNick()) == 'license') {
            $nextStep = $wizardHelper->getWizard($this->getNick())->getNextStep();
            $wizardHelper->setStep($this->getNick(), $nextStep);
        }

        // Steps
        // ---------------------------------------
        $this->setChild(
            'step_description',
            $this->helper('M2ePro/Module_Wizard')->createBlock('installation_description',$this->getNick())
        );

        $block = $this->helper('M2ePro/Module_Wizard')->createBlock('installation_license',$this->getNick());
        $block->setData('isLicenseStepFinished', $this->isLicenseStepFinished);
        $this->setChild('step_license', $block);

        $this->setChild(
            'step_marketplace',
            $this->helper('M2ePro/Module_Wizard')->createBlock('installation_marketplace',$this->getNick())
        );

        $this->setChild(
            'step_account',
            $this->helper('M2ePro/Module_Wizard')->createBlock('installation_account',$this->getNick())
        );
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################

    protected function getHeaderTextHtml()
    {
        return 'Configuration Wizard (Magento Amazon Integration)';
    }

    protected function _toHtml()
    {
        $urls = Mage::helper('M2ePro')->jsonEncode(
            Mage::helper('M2ePro')->getControllerActions('adminhtml_wizard_installationAmazon')
        );

        $additionalJs = <<<SCRIPT
<script type="text/javascript">
    M2ePro.url.add({$urls});
    InstallationAmazonWizardObj = new WizardInstallationAmazon();
</script>
SCRIPT;

        return parent::_toHtml()
            . $additionalJs
            . $this->getChildHtml('step_description')
            . $this->getChildHtml('step_license')
            . $this->getChildHtml('step_marketplace')
            . $this->getChildHtml('step_account');
    }

    //########################################
}