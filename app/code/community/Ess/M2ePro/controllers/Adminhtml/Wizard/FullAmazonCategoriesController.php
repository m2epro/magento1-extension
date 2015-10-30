<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Wizard_FullAmazonCategoriesController
    extends Ess_M2ePro_Controller_Adminhtml_Common_WizardController
{
    //########################################

    protected function _initAction()
    {
        parent::_initAction();
        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Wizard/Amazon/CustomHandler.js')
            ->addJs('M2ePro/Wizard/FullAmazonCategories.js');

        return $this;
    }

    //########################################

    protected function getNick()
    {
        return 'fullAmazonCategories';
    }

    //########################################

    public function indexAction()
    {
        $this->getWizardHelper()->setStatus(
            'amazonShippingOverridePolicy', Ess_M2ePro_Helper_Module_Wizard::STATUS_SKIPPED
        );

        return parent::indexAction();
    }

    public function welcomeAction()
    {
        $this->setStatus(Ess_M2ePro_Helper_Module_Wizard::STATUS_ACTIVE);

        return $this->_redirect('*/*/index');
    }

    public function installationAction()
    {
        if ($this->isFinished()) {
            return $this->_redirect('*/*/congratulation');
        }

        if (!$this->getCurrentStep()) {
            $this->setStep($this->getFirstStep());
        }

        return $this->_initAction()
            ->_addContent($this->getWizardHelper()->createBlock('installation',$this->getNick()))
            ->renderLayout();
    }

    public function congratulationAction()
    {
        return $this->_redirect('*/adminhtml_common_listing/index/');
    }

    //########################################

    public function marketplacesSynchronizationAction()
    {
        $marketplaceId = (int)$this->getRequest()->getParam('id');

        if (!$marketplaceId) {
            return $this->getResponse()->setBody('error');
        }

        /** @var $dispatcher Ess_M2ePro_Model_Synchronization_Dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');

        $dispatcher->setAllowedComponents(array(Ess_M2ePro_Helper_Component_Amazon::NICK));
        $dispatcher->setAllowedTasksTypes(array(Ess_M2ePro_Model_Synchronization_Task::MARKETPLACES));

        $dispatcher->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);
        $dispatcher->setParams(array('marketplace_id' => $marketplaceId));

        $dispatcher->process();

        return $this->getResponse()->setBody('success');
    }

    //########################################
}