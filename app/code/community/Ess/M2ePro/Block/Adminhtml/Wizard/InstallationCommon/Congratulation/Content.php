<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationCommon_Congratulation_Content extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardCongratulationContent');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/installationCommon/congratulation/content.phtml');
    }

    // ########################################
}