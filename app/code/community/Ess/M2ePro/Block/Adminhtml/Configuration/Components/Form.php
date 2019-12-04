<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Configuration_Components_Form extends Ess_M2ePro_Block_Adminhtml_Configuration_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('configurationComponentsForm');
        $this->setTemplate('M2ePro/configuration/components.phtml');

        $this->setPageHelpLink("x/CwAJAQ");
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
        $this->component_ebay_mode    = Mage::helper('M2ePro/Component_Ebay')->isEnabled();
        $this->component_amazon_mode  = Mage::helper('M2ePro/Component_Amazon')->isEnabled();
        $this->component_walmart_mode = Mage::helper('M2ePro/Component_Walmart')->isEnabled();

        return parent::_beforeToHtml();
    }

    //########################################
}
