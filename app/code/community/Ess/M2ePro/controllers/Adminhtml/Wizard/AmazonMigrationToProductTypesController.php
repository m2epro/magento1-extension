<?php


class Ess_M2ePro_Adminhtml_Wizard_AmazonMigrationToProductTypesController
    extends Ess_M2ePro_Controller_Adminhtml_Amazon_WizardController
{
    private $errorMessage;

    protected function _initAction()
    {
        parent::_initAction();

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Wizard/AmazonMigrationToProductTypes.js');

        return $this;
    }

    protected function getMenuRootNodeLabel()
    {
        return 'M2E Pro Walmart Integration Updates';
    }

    protected function getNick()
    {
        return Ess_M2ePro_Model_Wizard_AmazonMigrationToProductTypes::NICK;
    }

    public function indexAction()
    {
        if ($this->isSkipped()) {
            return $this->_redirect('*/adminhtml_amazon_listing/index');
        }

        $this->loadLayout();

        return $this->_initAction()->addContent(
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_wizard_amazonMigrationToProductTypes_content'
            )
        )->renderLayout();
    }

    public function acceptAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'success' => false,
                        'message' => 'Incorrect request type.',
                    )
                )
            );
        }

        if ($this->isNotStarted() || $this->isActive()) {
            if (!$this->updateMarketplacesDictionaries()) {
                return $this->getResponse()->setBody(
                    Mage::helper('M2ePro')->jsonEncode(
                        array(
                            'success' => false,
                            'message' => $this->errorMessage,
                        )
                    )
                );
            }

            $this->setStatus(Ess_M2ePro_Helper_Module_Wizard::STATUS_COMPLETED);
        }

        Mage::helper('M2ePro/Magento')->clearMenuCache();

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                    'success' => true,
                    'url' => $this->getUrl('*/adminhtml_amazon_listing/index'),
                )
            )
        );
    }

    private function updateMarketplacesDictionaries()
    {
        session_write_close();

        $marketplaceCollection = Mage::getResourceModel('M2ePro/Marketplace_Collection');
        $marketplaceCollection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Amazon::NICK)
            ->addFieldToFilter('status', 1);

        /** @var Ess_M2ePro_Model_Marketplace[] $marketplaces */
        $marketplaces = $marketplaceCollection->getItems();
        foreach ($marketplaces as $marketplace) {
            if (!$this->updateMarketplaceBuild($marketplace)) {
                return false;
            }
        }

        return true;
    }

    private function updateMarketplaceBuild(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        try {
            /** @var Ess_M2ePro_Model_Amazon_Dictionary_MarketplaceService $amazonDictionaryMarketplaceService */
            $amazonDictionaryMarketplaceService = Mage::getModel(
                'M2ePro/Amazon_Dictionary_MarketplaceService'
            );
            $amazonDictionaryMarketplaceService->update($marketplace);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();

            $servicingDispatcher = Mage::getModel(
                'M2ePro/Servicing_Dispatcher'
            );
            $servicingDispatcher->processTask(
                Ess_M2ePro_Model_Servicing_Task_License::NAME
            );

            return false;
        }

        return true;
    }
}