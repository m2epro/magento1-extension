<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Adminhtml_Wizard_AmazonController
    extends Ess_M2ePro_Controller_Adminhtml_Common_WizardController
{
    //#############################################

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
             ->addJs('M2ePro/Wizard/Amazon/MarketplaceHandler.js');

        return $this;
    }

    //#############################################

    protected function getNick()
    {
        return 'amazon';
    }

    //#############################################

    public function indexAction()
    {
        $this->getWizardHelper()->setStatus(
            'migrationNewAmazon', Ess_M2ePro_Helper_Module_Wizard::STATUS_SKIPPED
        );
        $this->getWizardHelper()->setStatus(
            'fullAmazonCategories', Ess_M2ePro_Helper_Module_Wizard::STATUS_SKIPPED
        );
        $this->getWizardHelper()->setStatus(
            'amazonShippingOverridePolicy', Ess_M2ePro_Helper_Module_Wizard::STATUS_SKIPPED
        );

        parent::indexAction();
    }

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
            '/component/amazon/', 'mode', 1
        );

        Mage::helper('M2ePro/Magento')->clearMenuCache();

        parent::installationAction();
    }

    //#############################################
}