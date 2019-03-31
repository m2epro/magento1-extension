<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationWalmart_Installation_Settings
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('wizardInstallationSettings');
        // ---------------------------------------

        $this->setTemplate('M2ePro/wizard/installationWalmart/installation/settings.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        // ---------------------------------------

        $this->setChild(
            'wizard_settings_form',
            $this->getLayout()->createBlock('M2ePro/adminhtml_wizard_installationWalmart_installation_settings_form')
        );

        // ---------------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('M2ePro')->__('Proceed'),
                'onclick' => 'InstallationWalmartWizardObj.saveSettingsStep()',
                'class' => 'process_settings_button'
            ));
        $this->setChild('process_settings_button', $buttonBlock);
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        return '<div id="settings_content_container">' . parent::_toHtml() . '</div>';
    }
}