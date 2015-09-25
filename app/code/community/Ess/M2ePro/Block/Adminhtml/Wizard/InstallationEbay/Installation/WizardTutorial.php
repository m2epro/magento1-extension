<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationEbay_Installation_WizardTutorial
    extends Ess_M2ePro_Block_Adminhtml_Wizard_InstallationEbay_Installation
{
    // ########################################

    protected function getStep()
    {
        return 'wizardTutorial';
    }

    // ########################################

    protected function _beforeToHtml()
    {
        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Link Magento to eBay and increase your sales');
        //------------------------------

        //------------------------------
        return parent::_beforeToHtml();
    }

    // ########################################
}