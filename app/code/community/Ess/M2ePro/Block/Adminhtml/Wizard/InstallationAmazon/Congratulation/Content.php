<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationAmazon_Congratulation_Content extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('wizardCongratulationContent');
        // ---------------------------------------

        $this->setTemplate('M2ePro/wizard/installationAmazon/congratulation/content.phtml');
    }

    //########################################
}