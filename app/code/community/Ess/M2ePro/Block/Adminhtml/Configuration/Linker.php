<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Configuration_Linker extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('configurationLinker');
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Global Settings');
        // ---------------------------------------

        $this->setTemplate('M2ePro/configuration/linker.phtml');
    }

    //########################################
}