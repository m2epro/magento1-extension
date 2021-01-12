<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationAmazon_Installation
    extends Ess_M2ePro_Block_Adminhtml_Wizard_Installation
{
    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $this->updateButton('continue', 'onclick', 'InstallationAmazonWizardObj.continueStep();');
    }

    protected function getHeaderTextHtml()
    {
        return Mage::helper('M2ePro')->__('Configuration of Amazon Integration');
    }

    protected function _prepareLayout()
    {
        Mage::helper('M2ePro/View')->getJsRenderer()->addOnReadyJs(<<<JS
    InstallationAmazonWizardObj = new WizardInstallationAmazon();
JS
        );

        return parent::_prepareLayout();
    }

    //########################################
}
