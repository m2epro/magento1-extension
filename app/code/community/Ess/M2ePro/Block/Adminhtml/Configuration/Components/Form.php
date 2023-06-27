<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Configuration_Components_Form
    extends Ess_M2ePro_Block_Adminhtml_Configuration_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('configurationComponentsForm');
        $this->setTemplate('M2ePro/configuration/components.phtml');

        $this->setPageHelpLink("global-settings");
    }

    //########################################

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->getLayout()->getBlock('head')->addJs('M2ePro/Configuration/ModuleAndChannels.js');
        $this->initPopUp();
    }

    //########################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
            'id'      => 'config_edit_form',
            'action'  => $this->getUrl('M2ePro/adminhtml_configuration_components/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        $confirmBtnText = 'Confirm';
        $isModuleDisabled = Mage::helper('M2ePro/Module')->isDisabled();
        $popupTitle = $isModuleDisabled ? 'Enable Module' : 'Disable Module';
        $buttonText = $isModuleDisabled ? 'Enable' : 'Disable';

        if ($isModuleDisabled) {
            $confirmBtnText = 'Ok';
            $popupTitle = 'Confirmation';
        }

        $data = array(
            'label'   => Mage::helper('M2ePro')->__($buttonText),
            'onclick' => 'ModuleAndChannelsObj.moduleModePopup(\''.Mage::helper('M2ePro')->__($popupTitle).'\')',
            'class'   => 'proceed_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('proceed_module_mode_button', $buttonBlock);

        $data = array(
            'label'   => Mage::helper('M2ePro')->__($confirmBtnText),
            'onclick' => 'setLocation(\''.$this->getUrl(
                'M2ePro/adminhtml_configuration_components/changeModuleMode', array(
                    'module_mode' => (int)!$isModuleDisabled
                )
            ).'\')',
            'class'   => 'proceed_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('confirm_module_mode_button', $buttonBlock);

        $isCronEnabled = Mage::helper('M2ePro/Module_Cron')->isModeEnabled();
        $disableCronButtonText = $isCronEnabled ? 'Disable' : 'Enable';

        $data = array(
            'label'   => Mage::helper('M2ePro')->__($disableCronButtonText),
            'onclick' => 'setLocation(\''.$this->getUrl(
                'M2ePro/adminhtml_configuration_components/changeCronMode', array(
                    'cron_mode' => (int)!$isCronEnabled
                )
            ).'\')',
            'class'   => 'proceed_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('change_cron_mode_button', $buttonBlock);

        // ---------------------------------------

        $isEbayEnabled = Mage::helper('M2ePro/Component_Ebay')->isEnabled();
        $ebayButtonText = $isEbayEnabled ? 'Disable' : 'Enable';

        $data = array(
            'label'   => Mage::helper('M2ePro')->__($ebayButtonText),
            'onclick' => 'setLocation(\''.$this->getUrl(
                'M2ePro/adminhtml_configuration_components/changeChannelMode', array(
                    'mode'      => (int)!$isEbayEnabled,
                    'channel' => Ess_M2ePro_Helper_Component_Ebay::NICK
                )
            ).'\')',
            'class'   => 'proceed_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('change_ebay_mode_button', $buttonBlock);

        $isAmazonEnabled = Mage::helper('M2ePro/Component_Amazon')->isEnabled();
        $amazonButtonText = $isAmazonEnabled ? 'Disable' : 'Enable';

        $data = array(
            'label'   => Mage::helper('M2ePro')->__($amazonButtonText),
            'onclick' => 'setLocation(\''.$this->getUrl(
                'M2ePro/adminhtml_configuration_components/changeChannelMode', array(
                    'mode'      => (int)!$isAmazonEnabled,
                    'channel' => Ess_M2ePro_Helper_Component_Amazon::NICK
                )
            ).'\')',
            'class'   => 'proceed_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('change_amazon_mode_button', $buttonBlock);

        $isWalmartEnabled = Mage::helper('M2ePro/Component_Walmart')->isEnabled();
        $walmartButtonText = $isWalmartEnabled ? 'Disable' : 'Enable';

        $data = array(
            'label'   => Mage::helper('M2ePro')->__($walmartButtonText),
            'onclick' => 'setLocation(\''.$this->getUrl(
                'M2ePro/adminhtml_configuration_components/changeChannelMode', array(
                    'mode'    => (int)!$isWalmartEnabled,
                    'channel' => Ess_M2ePro_Helper_Component_Walmart::NICK
                )
            ).'\')',
            'class'   => 'proceed_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('change_walmart_mode_button', $buttonBlock);

        return parent::_beforeToHtml();
    }

    //########################################
}
