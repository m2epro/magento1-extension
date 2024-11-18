<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Wizard_MigrationToInnodbController extends Ess_M2ePro_Controller_Adminhtml_WizardController
{
    //########################################

    protected function _initAction()
    {
        parent::_initAction();
        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/SynchProgress.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Marketplace.js')
            ->addJs('M2ePro/Wizard/MigrationToInnodb/MarketplaceSynchProgress.js')
            ->addJs('M2ePro/Wizard/MigrationToInnodb.js')
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css');

        return $this;
    }

    //########################################

    protected function getNick()
    {
        return 'migrationToInnodb';
    }

    protected function getCustomViewNick()
    {
        return null;
    }

    protected function getMenuRootNodeNick()
    {
        return Ess_M2ePro_Helper_Component::MENU_ROOT_NODE;
    }

    protected function getMenuRootNodeLabel()
    {
        return 'Marketplace Synchronization';
    }

    //########################################

    public function indexAction()
    {
        $enabledComponents = Mage::helper('M2ePro/Component')->getEnabledComponents();
        $component = array_shift($enabledComponents);

        $this->getRequest()->getParam('component') && $component = $this->getRequest()->getParam('component');

        /** @var Ess_M2ePro_Model_Wizard_MigrationToInnodb $wizard */
        $wizard = $this->getWizardHelper()->getWizard($this->getNick());
        $wizard->rememberRefererUrl($this->getUrl("*/adminhtml_{$component}_listing"));

        return parent::indexAction();
    }

    public function marketplacesSynchronizationAction()
    {
        return $this->_initAction()
            ->_addContent(
                $this->getWizardHelper()->createBlock('installation_marketplacesSynchronization', $this->getNick())
            )
            ->renderLayout();
    }

    public function congratulationAction()
    {
        if (!$this->isFinished()) {
            $this->_redirect('*/*/index');
            return;
        }

        Mage::helper('M2ePro/Magento')->clearMenuCache();

        /** @var Ess_M2ePro_Model_Wizard_MigrationToInnodb $wizard */
        $wizard = $this->getWizardHelper()->getWizard($this->getNick());
        $redirectUrl = $wizard->getRefererUrl();
        empty($redirectUrl) && $redirectUrl = $this->getUrl('*/adminhtml_support/index');

        $wizard->clearRefererUrl();
        return $this->_redirectUrl($redirectUrl);
    }

    //########################################

    public function runSynchNowAction()
    {
        $component = $this->getRequest()->getParam('component');
        /** @var Ess_M2ePro_Model_Marketplace $marketplace */
        $marketplace = Mage::helper('M2ePro/Component')->getUnknownObject(
            'Marketplace',
            (int)$this->getRequest()->getParam('marketplace_id')
        );

        if (strtolower($component) === 'amazon') {
            /** @var Ess_M2ePro_Model_Amazon_Dictionary_MarketplaceService $amazonDictionaryMarketplaceService */
            $amazonDictionaryMarketplaceService = Mage::getModel('M2ePro/Amazon_Dictionary_MarketplaceService');

            $result = 'success';
            try {
                $amazonDictionaryMarketplaceService->update($marketplace);
            } catch (\Exception $e) {
               $result = 'error';
            }

            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array('result' => $result)
                )
            );
        }

        // @codingStandardsIgnoreLine
        session_write_close();

        $component = ucfirst(strtolower($component));
        if ($component === 'Walmart') {
            $synchronization = $this->getWalmartSyncService($marketplace);
        } else {
            $synchronization = Mage::getModel('M2ePro/' . $component . '_Marketplace_Synchronization');
            $synchronization->setMarketplace($marketplace);
        }

        if ($synchronization->isLocked()) {
            $synchronization->getlog()->addMessage(
                Mage::helper('M2ePro')->__(
                    'Marketplaces cannot be updated now. '
                    . 'Please wait until another marketplace synchronization is completed, then try again.'
                ),
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
            );

            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => 'error')));
        }

        try {
            $synchronization->process();
        } catch (Exception $e) {
            $synchronization->getlog()->addMessage(
                Mage::helper('M2ePro')->__($e->getMessage()),
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
            );

            $synchronization->getLockItemManager()->remove();


            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => 'error')));
        }

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => 'success')));
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Marketplace_Synchronization|Ess_M2ePro_Model_Walmart_Marketplace_WithProductType_Synchronization
     */
    private function getWalmartSyncService(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        /** @var Ess_M2ePro_Model_Walmart_Marketplace_Synchronization $sync */
        $sync = Mage::getModel('M2ePro/Walmart_Marketplace_Synchronization');
        if ($sync->isMarketplaceAllowed($marketplace)) {
            $sync->setMarketplace($marketplace);

            return $sync;
        }

        /** @var Ess_M2ePro_Model_Walmart_Marketplace_WithProductType_Synchronization $syncWithPt */
        $syncWithPt = Mage::getModel('M2ePro/Walmart_Marketplace_WithProductType_Synchronization');
        $syncWithPt->setMarketplace($marketplace);

        return $syncWithPt;
    }
}
