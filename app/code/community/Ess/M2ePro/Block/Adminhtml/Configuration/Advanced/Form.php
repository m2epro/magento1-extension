<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Configuration_Advanced_Form extends Ess_M2ePro_Block_Adminhtml_Configuration_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('configurationAdvancedForm');
        // ---------------------------------------

        $this->setTemplate('M2ePro/configuration/advanced.phtml');

        // ---------------------------------------
    }

    //########################################

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->getLayout()->getBlock('head')->addJs('M2ePro/Configuration/Advanced.js');
        $this->initPopUp();
    }

    //########################################

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Proceed'),
            'onclick' => 'AdvancedObj.informationPopup()',
            'class'   => 'proceed_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('proceed_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => 'setLocation(\''.$this->getUrl('M2ePro/adminhtml_migrationToMagento2/disableModule').'\')',
            'class'   => 'proceed_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('confirm_button', $buttonBlock);
        // ---------------------------------------

        $confirmBtnText = 'Confirm';
        $popupTitle = 'Disable/Enable Module';
        if (Mage::helper('M2ePro/Module')->isDisabled()) {
            $confirmBtnText = 'Ok';
            $popupTitle = 'Confirmation';
        }

        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Proceed'),
            'onclick' => 'AdvancedObj.moduleModePopup(\''.Mage::helper('M2ePro')->__($popupTitle).'\')',
            'class'   => 'proceed_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('proceed_module_mode_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__($confirmBtnText),
            'onclick' => 'setLocation(\''.$this->getUrl(
                'M2ePro/adminhtml_configuration_advanced/changeModuleMode', array(
                    'module_mode' => (int)!Mage::helper('M2ePro/Module')->isDisabled()
                )
            ).'\')',
            'class'   => 'proceed_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('confirm_module_mode_button', $buttonBlock);
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################

    protected function initPopUp()
    {
        $themeFileName = 'prototype/windows/themes/magento.css';
        $themeLibFileName = 'lib/'.$themeFileName;
        $themeFileFound = false;
        $skinBaseDir = Mage::getDesign()->getSkinBaseDir(
            array(
                '_package' => Mage_Core_Model_Design_Package::DEFAULT_PACKAGE,
                '_theme' => Mage_Core_Model_Design_Package::DEFAULT_THEME,
            )
        );

        if (!$themeFileFound && is_file($skinBaseDir .'/'.$themeLibFileName)) {
            $themeFileFound = true;
            $this->getLayout()->getBlock('head')->addCss($themeLibFileName);
        }

        if (!$themeFileFound && is_file(Mage::getBaseDir().'/js/'.$themeFileName)) {
            $themeFileFound = true;
            $this->getLayout()->getBlock('head')->addItem('js_css', $themeFileName);
        }

        if (!$themeFileFound) {
            $this->getLayout()->getBlock('head')->addCss($themeLibFileName);
            $this->getLayout()->getBlock('head')->addItem('js_css', $themeFileName);
        }

        $this->getLayout()->getBlock('head')
            ->addJs('prototype/window.js')
            ->addItem('js_css', 'prototype/windows/themes/default.css');

        return $this;
    }

    //########################################
}