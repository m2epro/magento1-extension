<?php

class Ess_M2ePro_Adminhtml_Amazon_MarketplaceController
    extends Ess_M2ePro_Controller_Adminhtml_Amazon_MainController
{
    public function saveAction()
    {
        /** @var Ess_M2ePro_Model_Resource_Marketplace_Collection $marketplaceCollection */
        $marketplaceCollection = Mage::getModel('M2ePro/Marketplace')->getCollection();
        /** @var Ess_M2ePro_Model_Marketplace[] $marketplaces */
        $marketplaces = $marketplaceCollection->getItems();

        foreach ($marketplaces as $marketplace) {
            /** @var Ess_M2ePro_Model_Amazon_Dictionary_MarketplaceService $amazonDictionaryMarketPlaceService */
            $amazonDictionaryMarketPlaceService = Mage::getModel('M2ePro/Amazon_Dictionary_MarketplaceService');
            $newStatus = $this->getRequest()->getParam('status_' . $marketplace->getId());

            if (
                $newStatus === null
                || $marketplace->getStatus() == $newStatus
            ) {
                continue;
            }

            $marketplace->setStatus($newStatus)
                        ->save();

            if (!$amazonDictionaryMarketPlaceService->isExistForMarketplace($marketplace)) {
                $amazonDictionaryMarketPlaceService->update($marketplace);
            }
        }
    }

    public function getMarketplaceListAction()
    {
        /** @var Ess_M2ePro_Model_Amazon_Marketplace_Repository $amazonMarketplaceRepository */
        $amazonMarketplaceRepository = Mage::getModel('M2ePro/Amazon_Marketplace_Repository');

        $result = array();
        foreach ($amazonMarketplaceRepository->findWithAccounts() as $marketplace) {
            $result[] = array(
                'id' => (int)$marketplace->getId(),
                'title' => $marketplace->getTitle(),
            );
        }

        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array('list' => $result)
            )
        );
    }

    public function getProductTypeListAction()
    {
        /** @var Ess_M2ePro_Model_Amazon_Marketplace_Sync_MarketplaceLoader $amazonMarketplaceLoader */
        $amazonMarketplaceLoader = Mage::getModel('M2ePro/Amazon_Marketplace_Sync_MarketplaceLoader');
        /** @var Ess_M2ePro_Model_Amazon_Dictionary_ProductType_Repository $dictionaryProductTypeRepository */
        $dictionaryProductTypeRepository = Mage::getModel('M2ePro/Amazon_Dictionary_ProductType_Repository');

        $marketplace = $amazonMarketplaceLoader->load($this->getRequest()->getParam('marketplace_id'));
        $productTypes = $dictionaryProductTypeRepository->findValidByMarketplace($marketplace);

        $result = array();
        foreach ($productTypes as $productType) {
            $result[] = array(
                'id' => (int)$productType->getId(),
                'title' => $productType->getTitle(),
            );
        }

        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array('list' => $result)
            )
        );
    }

    public function updateDetailsAction()
    {
        /** @var Ess_M2ePro_Model_Amazon_Marketplace_Sync_MarketplaceLoader $amazonMarketplaceLoader */
        $amazonMarketplaceLoader = Mage::getModel('M2ePro/Amazon_Marketplace_Sync_MarketplaceLoader');
        /** @var Ess_M2ePro_Model_Amazon_Dictionary_MarketplaceService $dictionaryMarketplaceService */
        $dictionaryMarketplaceService = Mage::getModel('M2ePro/Amazon_Dictionary_MarketplaceService');

        $marketplace = $amazonMarketplaceLoader->load($this->getRequest()->getParam('marketplace_id'));
        $dictionaryMarketplaceService->update($marketplace);

        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(array())
        );
    }

    public function updateProductTypeAction()
    {
        $productTypeId = $this->getRequest()->getParam('id');
        if ($productTypeId === null) {
            throw new \RuntimeException('Missing Product Type ID');
        }

        /** @var Ess_M2ePro_Model_Amazon_Dictionary_ProductType_Repository $dictionaryProductTypeRepository */
        $dictionaryProductTypeRepository = Mage::getModel('M2ePro/Amazon_Dictionary_ProductType_Repository');
        /** @var Ess_M2ePro_Model_Amazon_Dictionary_ProductTypeService $dictionaryProductTypeService */
        $dictionaryProductTypeService = Mage::getModel('M2ePro/Amazon_Dictionary_ProductTypeService');

        $productType = $dictionaryProductTypeRepository->get((int)$productTypeId);
        $dictionaryProductTypeService->update($productType);

        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(array())
        );
    }
}
