<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Adminhtml_Wizard_RemovedPlayController
    extends Ess_M2ePro_Controller_Adminhtml_Common_WizardController
{
    //#############################################

    protected function getNick()
    {
        return 'removedPlay';
    }

    //#############################################

    public function installationAction()
    {
        Mage::helper('M2ePro/Magento')->clearMenuCache();

        $this->setStatus(Ess_M2ePro_Helper_Module_Wizard::STATUS_COMPLETED);

        return $this->_redirect('*/adminhtml_common_listing/index/');
    }

    public function congratulationAction()
    {
        return $this->_redirect('*/adminhtml_common_listing/index/');
    }

    //#############################################
}
