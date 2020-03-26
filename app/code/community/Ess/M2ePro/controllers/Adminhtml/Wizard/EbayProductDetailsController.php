<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Wizard_EbayProductDetailsController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_WizardController
{
    //########################################

    protected function _initAction()
    {
        parent::_initAction();
        $this->getLayout()->getBlock('head')
                          ->addJs('M2ePro/Wizard/EbayProductDetails.js');

        return $this;
    }

    //########################################

    protected function getNick()
    {
        return 'ebayProductDetails';
    }

    //########################################

    public function welcomeAction()
    {
        $this->setStatus(Ess_M2ePro_Helper_Module_Wizard::STATUS_ACTIVE);

        return $this->_redirect('*/adminhtml_ebay_listing/index/');
    }

    public function installationAction()
    {
        if ($this->isFinished()) {
            return $this->_redirect('*/*/congratulation');
        }

        if (!$this->getCurrentStep() || !in_array($this->getCurrentStep(), $this->getSteps())) {
            $this->setStep($this->getFirstStep());
        }

        return $this->_initAction()
                    ->_addContent($this->getWizardHelper()->createBlock('installation', $this->getNick()))
                    ->renderLayout();
    }

    public function congratulationAction()
    {
        return $this->_redirect('*/adminhtml_ebay_listing/index/');
    }

    //########################################

    public function marketplacesSynchronizationAction()
    {
        $marketplaceId = (int)$this->getRequest()->getParam('id');

        if (!$marketplaceId) {
            return $this->getResponse()->setBody('error');
        }

        /** @var Ess_M2ePro_Model_Marketplace $marketplace */
        $marketplace = Mage::helper('M2ePro/Component')
            ->getUnknownObject('Marketplace', $marketplaceId);

        $lockItemManager = Mage::getModel(
            'M2ePro/Lock_Item_Manager', array(
            'nick' => Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_SYNCHRONIZATION_LOCK_ITEM_NICK,
            )
        );

        if ($lockItemManager->isExist()) {
            return $this->getResponse()->setBody('error');
        }

        $lockItemManager->create();

        $progressManager = Mage::getModel(
            'M2ePro/Lock_Item_Progress', array(
            'lock_item_manager' => $lockItemManager,
            'progress_nick'     => $marketplace->getTitle() . ' Marketplace',
            )
        );

        $synchronization = Mage::getModel('M2ePro/Ebay_Marketplace_Synchronization');
        $synchronization->setMarketplace($marketplace);
        $synchronization->setProgressManager($progressManager);

        $synchronization->process();

        $lockItemManager->remove();

        return $this->getResponse()->setBody('success');
    }

    //########################################
}
