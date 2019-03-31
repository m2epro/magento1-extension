<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Configuration_General
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonConfigurationGeneral');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_configuration';
        $this->_mode = 'general';
        // ---------------------------------------

        $this->_headerText = '';

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        // ---------------------------------------
        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'editForm.submit();',
            'class'     => 'save save_configuration_general'
        ));
        // ---------------------------------------
    }

    //########################################
}