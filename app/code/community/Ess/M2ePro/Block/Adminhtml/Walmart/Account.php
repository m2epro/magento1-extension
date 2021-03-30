<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Account extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('account');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_walmart_account';
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

        // ---------------------------------------
        $this->_addButton(
            'add', array(
            'label'     => Mage::helper('M2ePro')->__('Add Account'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_walmart_account/new').'\');',
            'class'     => 'add',
            'id'        => 'add'
            )
        );
        // ---------------------------------------
    }

    //########################################
}
