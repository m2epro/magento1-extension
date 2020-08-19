<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * Class Ess_M2ePro_Block_Adminhtml_Amazon_Configuration_General_Form
 *
 * @method Ess_M2ePro_Helper_Component_Amazon_Configuration getConfigurationHelper()
 */
class Ess_M2ePro_Block_Adminhtml_Amazon_Configuration_General_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonConfigurationGeneralForm');
        $this->setContainerId('magento_block_amazon_configuration_general');
        $this->setTemplate('M2ePro/amazon/configuration/general/form.phtml');
        // ---------------------------------------
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'      => 'edit_form',
                'action'  => $this->getUrl('*/adminhtml_amazon_configuration/save'),
                'method'  => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        $this->setData('configuration_helper', Mage::helper('M2ePro/Component_Amazon_Configuration'));

        return parent::_beforeToHtml();
    }

    //########################################
}
