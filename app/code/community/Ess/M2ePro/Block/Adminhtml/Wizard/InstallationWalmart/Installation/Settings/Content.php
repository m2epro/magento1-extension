<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationWalmart_Installation_Settings_Content
    extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->setId('wizardInstallationWalmartSettingsContent');
    }

    //########################################

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('wizard.help.block')->setContent(
            Mage::helper('M2ePro')->__(
                <<<HTML
In this section, you can configure the general settings for interaction between M2E Pro and Walmart
Marketplaces including SKU, Product Identifiers, image URL settings.
HTML
            )
        );

        parent::_prepareLayout();
    }

    //########################################

    protected function _toHtml()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Walmart_Configuration_General_Form $settings */
        $settings = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_configuration_general_form');
        $settings->toHtml();
        $settings->getForm()->setData(
            array(
                'id'      => 'edit_form',
                'action'  => '',
                'method'  => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        return parent::_toHtml() . $settings->toHtml();
    }

    //########################################
}
