<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_General extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayConfigurationGeneral');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_configuration';
        $this->_mode = 'general';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = '';
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'editForm.submit();',
            'class'     => 'save'
        ));
        // ---------------------------------------
    }

    //########################################
}