<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Tabs_Tools extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentTools');
        //------------------------------

        $this->setTemplate('M2ePro/development/tabs/tools.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $this->setChild('development_tools_m2epro_general',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_development_tabs_command_group',
                '',
                array('controller_name' => Ess_M2ePro_Helper_View_Development_Command::CONTROLLER_TOOLS_M2EPRO_GENERAL)
            )
        );

        $this->setChild('development_tools_m2epro_install',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_development_tabs_command_group',
                '',
                array('controller_name' => Ess_M2ePro_Helper_View_Development_Command::CONTROLLER_TOOLS_M2EPRO_INSTALL)
            )
        );

        $this->setChild('development_tools_magento',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_development_tabs_command_group',
                '',
                array('controller_name' => Ess_M2ePro_Helper_View_Development_Command::CONTROLLER_TOOLS_MAGENTO)
            )
        );

        $this->setChild('development_tools_additional',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_development_tabs_command_group',
                '',
                array('controller_name' => Ess_M2ePro_Helper_View_Development_Command::CONTROLLER_TOOLS_ADDITIONAL)
            )
        );

        return parent::_beforeToHtml();
    }

    // ########################################
}