<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Configuration_Abstract extends Mage_Adminhtml_Block_System_Config_Form
{
    //########################################

    protected function _toHtml()
    {
        $generalBlock = Mage::helper('M2ePro/View')->getGeneralBlock();
        $generalBlock->setPageHelpLink($this->getPageHelpLink());

        return $generalBlock->toHtml() . parent::_toHtml();
    }

    //########################################

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('head')->addJs('M2ePro/General/PhpFunctions.js');
        $this->getLayout()->getBlock('head')->addJs('M2ePro/General/PrototypeSimulate.js');
        $this->getLayout()->getBlock('head')->addJs('M2ePro/General/Common.js');

        $this->getLayout()->getBlock('head')->addJs('M2ePro/General/Translator.js');
        $this->getLayout()->getBlock('head')->addJs('M2ePro/General/Php.js');
        $this->getLayout()->getBlock('head')->addJs('M2ePro/General/Url.js');

        $this->getLayout()->getBlock('head')->addJs('M2ePro/Plugin/Message.js');
        $this->getLayout()->getBlock('head')->addJs('M2ePro/Plugin/Magento/Block.js');
        $this->getLayout()->getBlock('head')->addJs('M2ePro/Plugin/Magento/FieldTip.js');
        $this->getLayout()->getBlock('head')->addJs('M2ePro/Plugin/BlockNotice.js');
        $this->getLayout()->getBlock('head')->addJs('M2ePro/Plugin/Storage.js');

        $this->getLayout()->getBlock('head')->addJs('M2ePro/Initialization.js');

        $this->getLayout()->getBlock('head')->addJs('M2ePro/ControlPanel.js');

        $this->getLayout()->getBlock('head')->addCss('M2ePro/css/main.css');
        $this->getLayout()->getBlock('head')->addCss('M2ePro/css/Plugin/BlockNotice.css');

        parent::_prepareLayout();
    }

    //########################################

    protected function setPageHelpLink($tinyLink = null)
    {
        $this->setData(
            'page_help_link',
            Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(null, null, $tinyLink)
        );

        return $this;
    }

    public function getPageHelpLink()
    {
        if ($this->getData('page_help_link') === null) {
            return Mage::helper('M2ePro/Module_Support')->getDocumentationUrl();
        }

        return $this->getData('page_help_link');
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
