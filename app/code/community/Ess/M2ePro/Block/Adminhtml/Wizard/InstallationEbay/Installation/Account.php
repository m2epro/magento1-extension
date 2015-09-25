<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationEbay_Installation_Account
    extends Ess_M2ePro_Block_Adminhtml_Wizard_InstallationEbay_Installation
{
    // ########################################

    protected function getStep()
    {
        return 'account';
    }

    // ########################################

    protected function _beforeToHtml()
    {
        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('M2E Pro Registration Complete');
        //------------------------------

        //------------------------------
        return parent::_beforeToHtml();
    }

    // ########################################
}