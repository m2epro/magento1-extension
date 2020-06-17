<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Synchronization extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonSynchronization');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_synchronization';

        $this->_headerText = '';

        $this->removeButton('save');
        $this->removeButton('reset');
        $this->removeButton('back');

        $this->_addButton(
            'save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'SynchronizationObj.saveSettings()',
            'class'     => 'save'
            )
        );
    }

    //########################################
}
