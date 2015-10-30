<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Wizard_BuyController
    extends Ess_M2ePro_Controller_Adminhtml_Common_WizardController
{
    //########################################

    protected function _initAction()
    {
        parent::_initAction();

        $this->getLayout()->getBlock('head')
             ->addCss('M2ePro/css/Plugin/ProgressBar.css')
             ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
             ->addJs('M2ePro/Plugin/ProgressBar.js')
             ->addJs('M2ePro/Plugin/AreaWrapper.js')
             ->addJs('M2ePro/SynchProgressHandler.js')
             ->addJs('M2ePro/MarketplaceHandler.js')
             ->addJs('M2ePro/Wizard/Buy/MarketplaceHandler.js');

        return $this;
    }

    //########################################

    protected function getNick()
    {
        return 'buy';
    }

    //########################################

    public function welcomeAction()
    {
        if (!$this->isNotStarted()) {
            return $this->_redirect('*/*/index');
        }

        return $this->_initAction()
                    ->_addContent($this->getWizardHelper()->createBlock('welcome',$this->getNick()))
                    ->renderLayout();
    }

    public function installationAction()
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/component/buy/', 'mode', 1
        );

        Mage::helper('M2ePro/Magento')->clearMenuCache();

        parent::installationAction();
    }

    //########################################
}
