<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Development_Tabs_Module extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('developmentModule');
        // ---------------------------------------

        $this->setTemplate('M2ePro/development/tabs/module.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->setChild('development_module_integration',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_development_tabs_command_group',
                '',
                array(
                    'controller_name'=>Ess_M2ePro_Helper_View_Development_Command::CONTROLLER_MODULE_INTEGRATION
                )
            )
        );

        $this->setChild('development_module_integration_ebay',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_development_tabs_command_group',
                '',
                array(
                    'controller_name'=>Ess_M2ePro_Helper_View_Development_Command::CONTROLLER_MODULE_INTEGRATION_EBAY
                )
            )
        );

        $this->setChild('development_module_integration_amazon',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_development_tabs_command_group',
                '',
                array(
                    'controller_name'=>Ess_M2ePro_Helper_View_Development_Command::CONTROLLER_MODULE_INTEGRATION_AMAZON
                )
            )
        );

        return parent::_beforeToHtml();
    }

    //########################################
}