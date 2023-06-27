<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * Class Ess_M2ePro_Block_Adminhtml_Configuration_Settings_Form
 *
 * @method Ess_M2ePro_Helper_Module_Configuration getConfigurationHelper()
 */
class Ess_M2ePro_Block_Adminhtml_Configuration_Settings_Form extends Ess_M2ePro_Block_Adminhtml_Configuration_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('configurationSettingsForm');
        $this->setTemplate('M2ePro/configuration/settings.phtml');
        $this->setPageHelpLink('global-settings');
    }

    //########################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'      => 'config_edit_form',
                'action'  => $this->getUrl('M2ePro/adminhtml_configuration_settings/save'),
                'method'  => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Configuration/Settings.js')
            ->addJs('M2ePro/Template/Edit.js');
    }

    protected function _beforeToHtml()
    {
        $this->setData('configuration_helper', Mage::helper('M2ePro/Module_Configuration'));

        /** @var Mage_Adminhtml_Block_Widget_Button $restoreBlockNoticesButton */
        $restoreBlockNoticesButton = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(
            array(
                'label'   => Mage::helper('M2ePro')->__('Restore All Helps & Remembered Choices'),
                'onclick' => 'SettingsObj.restoreAllHelpsAndRememberedChoices()',
                'class'   => 'restore_block_notices'
            )
        );
        $this->setChild('restore_block_notices', $restoreBlockNoticesButton);

        return parent::_beforeToHtml();
    }

    //########################################
}
