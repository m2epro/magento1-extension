<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Configuration_Abstract extends Mage_Adminhtml_Block_System_Config_Form
{
    //########################################

    protected function _toHtml()
    {
        // ---------------------------------------
        $url = Mage::helper('M2ePro/View_Development')->getPageUrl();
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Control Panel'),
            'onclick' => 'window.open(\'' . $url . '\')',
            'class'   => 'development button_link',
            'style'   => 'display: none;'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        // ---------------------------------------

        $generalBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_general')
                                          ->setData('page_help_link', $this->getPageHelpLink());

        return $generalBlock->toHtml().
            '<div id="development_button_container" style="text-align: right; margin: -10px 0 8px 0; display:none;">'.
                 $buttonBlock->toHtml().
            '</div>'.
            parent::_toHtml();
    }

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('head')->addJs('M2ePro/General/PhpFunctions.js');
        $this->getLayout()->getBlock('head')->addJs('M2ePro/General/PrototypeSimulate.js');
        $this->getLayout()->getBlock('head')->addJs('M2ePro/General/CommonHandler.js');

        $this->getLayout()->getBlock('head')->addJs('M2ePro/General/TranslatorHandler.js');
        $this->getLayout()->getBlock('head')->addJs('M2ePro/General/PhpHandler.js');
        $this->getLayout()->getBlock('head')->addJs('M2ePro/General/UrlHandler.js');

        $this->getLayout()->getBlock('head')->addJs('M2ePro/Plugin/Magento/Message.js');
        $this->getLayout()->getBlock('head')->addJs('M2ePro/Plugin/Magento/Block.js');
        $this->getLayout()->getBlock('head')->addJs('M2ePro/Plugin/Magento/FieldTip.js');
        $this->getLayout()->getBlock('head')->addJs('M2ePro/Plugin/BlockNotice.js');

        $this->getLayout()->getBlock('head')->addJs('M2ePro/Initialization.js');

        $this->getLayout()->getBlock('head')->addJs('M2ePro/Development/ControlPanelHandler.js');

        $this->getLayout()->getBlock('head')->addCss('M2ePro/css/main.css');
        $this->getLayout()->getBlock('head')->addCss('M2ePro/css/Plugin/BlockNotice.css');

        parent::_prepareLayout();
    }

    //########################################

    protected function setPageHelpLink($article = NULL)
    {
        $components = Mage::helper('M2ePro/Component')->getActiveComponents();

        $component = Ess_M2ePro_Helper_Component_Ebay::NICK;
        if (count($components) == 1) {
            $component = array_shift($components);
        }

        $this->setData('page_help_link',
            Mage::helper('M2ePro/Module_Support')->getDocumentationUrl($component, $article));

        return $this;
    }

    public function getPageHelpLink()
    {
        if (is_null($this->getData('page_help_link'))) {
            return Mage::helper('M2ePro/Module_Support')->getDocumentationUrl();
        }

        return $this->getData('page_help_link');
    }

    //########################################
}