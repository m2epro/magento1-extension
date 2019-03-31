<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Development_Tabs_Cron extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('developmentCron');
        // ---------------------------------------

        $this->setTemplate('M2ePro/development/tabs/cron.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->setChild('development_cron_system',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_development_tabs_command_group',
                '',
                array('controller_name'=>Ess_M2ePro_Helper_View_Development_Command::CONTROLLER_CRON_SYSTEM)
            )
        );

        $this->setChild('development_cron_amazon',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_development_tabs_command_group',
                '',
                array('controller_name'=>Ess_M2ePro_Helper_View_Development_Command::CONTROLLER_CRON_AMAZON)
            )
        );

        $this->setChild('development_cron_ebay',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_development_tabs_command_group',
                '',
                array('controller_name'=>Ess_M2ePro_Helper_View_Development_Command::CONTROLLER_CRON_EBAY)
            )
        );

        $this->setChild('development_cron_walmart',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_development_tabs_command_group',
                '',
                array('controller_name'=>Ess_M2ePro_Helper_View_Development_Command::CONTROLLER_CRON_WALMART)
            )
        );

        return parent::_beforeToHtml();
    }

    //########################################
}