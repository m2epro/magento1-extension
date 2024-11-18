<?php

class Ess_M2ePro_Controller_Adminhtml_Amazon_MarketplaceSyncController
    extends Ess_M2ePro_Controller_Adminhtml_Amazon_MainController

{
    private function initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Configuration'))
            ->_title(Mage::helper('M2ePro')->__('Product Type'));

        $this->getLayout()
            ->getBlock('head')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Amazon/ProductType.css')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addJs('M2ePro/Amazon/Marketplace/Sync.js')
            ->addJs('M2ePro/Amazon/ProductType.js')
            ->addJs('M2ePro/Amazon/ProductType/Content.js')
            ->addJs('M2ePro/Amazon/ProductType/Search.js')
            ->addJs('M2ePro/Amazon/ProductType/Finder.js')
            ->addJs('M2ePro/Amazon/ProductType/Tabs.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "amazon-integration");

        return $this;
    }

    public function indexAction()
    {
        $this->initAction()
            ->_addContent(
                $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_amazon_configuration',
                    '',
                    array(
                        'active_tab' => Ess_M2ePro_Block_Adminhtml_Amazon_Configuration_Tabs::TAB_ID_PRODUCT_TYPE
                    )
                )
            )->renderLayout();
    }

    public function getMarketplaceListAction()
    {
        $amazonMarketplaceRepository = Mage::getModel('M2ePro/Amazon_Marketplace_Repository');
        $result = array();
        foreach ($amazonMarketplaceRepository->findWithAccounts() as $marketplace) {
            $result[] = array(
                'id' => (int)$marketplace->getId(),
                'title' => $marketplace->getTitle(),
            );
        }

        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(
            array('list' => $result)
        ));
    }

    public function getProductTypeListAction()
    {
        $amazonMarketplaceLoader = Mage::getModel('Amazon_Marketplace_Sync_MarketplaceLoader');
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

        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(
            array('list' => $result)
        ));
    }

    public function updateDetails()
    {
        $amazonMarketplaceLoader = Mage::getModel('Amazon_Marketplace_Sync_MarketplaceLoader');
        $dictionaryMarketplaceService = Mage::getModel('M2ePro/Amazon_Dictionary_MarketplaceService');
        $marketplace = $amazonMarketplaceLoader->load($this->getRequest()->getParam('marketplace_id'));
        $dictionaryMarketplaceService->update($marketplace);

        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(
            array()
        ));
    }

    public function updateProductTypeAction()
    {
        $productTypeId = $this->getRequest()->getParam('id');
        if ($productTypeId === null) {
            throw new \RuntimeException('Missing Product Type ID');
        }

        $dictionaryProductTypeRepository = Mage::getModel('M2ePro/Amazon_Dictionary_ProductType_Repository');
        $dictionaryProductTypeService = Mage::getModel('M2ePro/Amazon_Dictionary_ProductTypeService');

        $productType = $dictionaryProductTypeRepository->get((int)$productTypeId);
        $dictionaryProductTypeService->update($productType);

        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(
            array()
        ));

    }
}