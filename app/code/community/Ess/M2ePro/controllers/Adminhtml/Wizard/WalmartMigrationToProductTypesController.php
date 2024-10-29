<?php

class Ess_M2ePro_Adminhtml_Wizard_WalmartMigrationToProductTypesController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_WizardController
{
    protected function getMenuRootNodeLabel()
    {
        return 'M2E Pro Walmart Integration Updates';
    }

    protected function getNick()
    {
        return Ess_M2ePro_Model_Wizard_WalmartMigrationToProductTypes::NICK;
    }

    public function indexAction()
    {
        if ($this->isSkipped()) {
            return $this->_redirect('*/adminhtml_walmart_listing/index');
        }

        $this->loadLayout();

        return $this->_addContent(
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_wizard_walmartMigrationToProductTypes_content'
            )
        )->renderLayout();
    }

    public function syncAction()
    {
        try {
            /** @var Ess_M2ePro_Model_Walmart_Marketplace_WithProductType_ForceAllSynchronization $forceAllSync */
            $forceAllSync = Mage::getModel('M2ePro/Walmart_Marketplace_WithProductType_ForceAllSynchronization');
            $forceAllSync->process();
        } catch (\Exception $exception) {
            /** @var Ess_M2ePro_Helper_Module_Exception $exceptionHelper */
            $exceptionHelper = Mage::helper('M2ePro/Module_Exception');
            $exceptionHelper->process($exception);
        }

        Mage::helper('M2ePro/Magento')->clearMenuCache();
        $this->setStatus(Ess_M2ePro_Helper_Module_Wizard::STATUS_SKIPPED);

        return $this->_redirect('*/adminhtml_walmart_listing/index');
    }
}