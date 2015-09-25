<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Configuration_Linker extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('configurationLinker');
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Global Settings');
        //------------------------------

        $this->setTemplate('M2ePro/configuration/linker.phtml');
    }

    // ########################################
}