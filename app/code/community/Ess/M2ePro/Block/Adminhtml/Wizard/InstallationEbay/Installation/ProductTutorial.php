<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationEbay_Installation_ProductTutorial
    extends Ess_M2ePro_Block_Adminhtml_Wizard_InstallationEbay_Installation
{
    //########################################

    protected function getStep()
    {
        return 'productTutorial';
    }

    //########################################

    protected function _beforeToHtml()
    {
        // Set header text
        // ---------------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('What are M2E Pro Products and How Do They Appear on eBay');
        // ---------------------------------------

        // ---------------------------------------
        return parent::_beforeToHtml();
    }

    //########################################
}