<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Tabs_Module extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentModule');
        //------------------------------

        $this->setTemplate('M2ePro/development/tabs/module.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $this->setChild('development_module_module',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_development_tabs_command_group',
                '',
                array('controller_name'=>Ess_M2ePro_Helper_View_Development_Command::CONTROLLER_MODULE_MODULE)
            )
        );

        $this->setChild('development_module_synchronization',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_development_tabs_command_group',
                '',
                array('controller_name'=>Ess_M2ePro_Helper_View_Development_Command::CONTROLLER_MODULE_SYNCHRONIZATION)
            )
        );

        $this->setChild('development_module_integration',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_development_tabs_command_group',
                '',
                array('controller_name'=>Ess_M2ePro_Helper_View_Development_Command::CONTROLLER_MODULE_INTEGRATION)
            )
        );

        return parent::_beforeToHtml();
    }

    // ########################################
}