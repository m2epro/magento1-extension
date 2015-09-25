<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayAccounts');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_account';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = '';
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton('add', array(
            'label'     => Mage::helper('M2ePro')->__('Add Account'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_ebay_account/new').'\');',
            'class'     => 'add'
        ));
        //------------------------------
    }

    // ########################################
}