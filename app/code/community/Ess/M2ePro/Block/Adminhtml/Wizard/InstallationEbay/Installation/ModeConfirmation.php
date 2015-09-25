<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationEbay_Installation_ModeConfirmation
    extends Ess_M2ePro_Block_Adminhtml_Wizard_InstallationEbay_Installation
{
    // ########################################

    protected function getStep()
    {
        return 'modeConfirmation';
    }

    // ########################################

    protected function _beforeToHtml()
    {
        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('M2E Pro Mode');
        //------------------------------

        //------------------------------
        return parent::_beforeToHtml();
    }

    // ########################################
}