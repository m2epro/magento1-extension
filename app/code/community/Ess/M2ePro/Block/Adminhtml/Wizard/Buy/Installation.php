<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Buy_Installation extends Ess_M2ePro_Block_Adminhtml_Wizard_Installation
{
    //########################################

    protected function _beforeToHtml()
    {
        // Steps
        // ---------------------------------------
        $this->setChild(
            'step_marketplace',
            $this->helper('M2ePro/Module_Wizard')->createBlock('installation_marketplace',$this->getNick())
        );
        $this->setChild(
            'step_account',
            $this->helper('M2ePro/Module_Wizard')->createBlock('installation_account',$this->getNick())
        );
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################

    protected function getHeaderTextHtml()
    {
        return 'Configuration Wizard (Magento Rakuten.com Integration)';
    }

    protected function _toHtml()
    {
        return parent::_toHtml()
            . $this->getChildHtml('step_marketplace')
            . $this->getChildHtml('step_account');
    }

    //########################################
}