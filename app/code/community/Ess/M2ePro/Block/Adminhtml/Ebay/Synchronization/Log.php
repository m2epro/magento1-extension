<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Synchronization_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ##########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('synchronizationLog');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_synchronization_log';
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

        // Set template
        //------------------------------
        $this->setTemplate('M2ePro/widget/grid/container/only_content.phtml');
        //------------------------------
    }

    // ##########################################
}