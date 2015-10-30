<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Configuration_Components_Form extends Ess_M2ePro_Block_Adminhtml_Configuration_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('configurationComponentsForm');
        // ---------------------------------------

        $this->setTemplate('M2ePro/configuration/components.phtml');

        // ---------------------------------------

        $this->setPageHelpLink('Global+Settings#GlobalSettings-Channels');
    }

    //########################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'config_edit_form',
            'action'  => $this->getUrl('M2ePro/adminhtml_configuration_components/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->getLayout()->getBlock('head')->addJs('M2ePro/Configuration/ComponentsHandler.js');
    }

    protected function _beforeToHtml()
    {
        // Set data for form
        // ---------------------------------------
        $this->component_ebay_mode = Mage::helper('M2ePro/Component_Ebay')->isActive();
        $this->component_amazon_mode = Mage::helper('M2ePro/Component_Amazon')->isActive();
        $this->component_buy_mode = Mage::helper('M2ePro/Component_Buy')->isActive();

        $this->component_ebay_allowed = Mage::helper('M2ePro/Component_Ebay')->isAllowed();
        $this->component_amazon_allowed = Mage::helper('M2ePro/Component_Amazon')->isAllowed();
        $this->component_buy_allowed = Mage::helper('M2ePro/Component_Buy')->isAllowed();

        $this->component_group_rakuten_allowed = Mage::helper('M2ePro/Component')->isRakutenAllowed();

        $this->view_common_component_default = Mage::helper('M2ePro/View_Common_Component')->getDefaultComponent();
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################

    public function getComponentsTitles()
    {
        return json_encode(Mage::helper('M2ePro/View_Common_Component')->getComponentsTitles());
    }

    //########################################
}