<?php

use Ess_M2ePro_Model_Issue_Object as Issue;
class Ess_M2ePro_Model_Amazon_Marketplace_Issue_ProductTypeOutOfDate
{
    public function getIssues()
    {
        if (!$this->isNeedProcess()) {
            return array();
        }

        if (!$this->isExistOutOfDateProductTypes()) {
            return array();
        }

        $tempTitle = Mage::helper('M2ePro')->__(
            'M2E Pro requires action: Amazon marketplace data needs to be synchronized.
            Please update Amazon marketplaces.'
        );

        $tempMessage = Mage::helper('M2ePro')->__(
            'Data for some Product Types was changed on Amazon.
 To avoid errors and have access to the latest updates,
please use the <b>Refresh Amazon Data</b> button in Amazon > <a href="%url" target="_blank">Product Types</a>
and re-save the Product Types you have configured.',
            Mage::helper('adminhtml')->getUrl('M2ePro/adminhtml_amazon_productTypes/index')
        );

        return array(
            Mage::getModel(
                'M2ePro/Issue_Object', array(
                    Issue::KEY_TYPE  => Mage_Core_Model_Message::NOTICE,
                    Issue::KEY_TITLE => $tempTitle,
                    Issue::KEY_TEXT  => $tempMessage,
                    Issue::KEY_URL   => ''
                )
            )
        );
    }

    private function isNeedProcess()
    {
        return Mage::helper('M2ePro/View_Amazon')->isInstallationWizardFinished() &&
            Mage::helper('M2ePro/Component_Amazon')->isEnabled();
    }

    private function isExistOutOfDateProductTypes()
    {
        /** @var Ess_M2ePro_Model_Amazon_Marketplace_Issue_ProductTypeOutOfDate_Cache $productOutOfDateCache */
        $productOutOfDateCache = Mage::getModel('M2ePro/Amazon_Marketplace_Issue_ProductTypeOutOfDate_Cache');
        $outdatedMarketplaces = $productOutOfDateCache->get();
        if ($outdatedMarketplaces !== false) {
            return $outdatedMarketplaces;
        }

        $activeMarketplaces = array();

        /** @var Ess_M2ePro_Model_Amazon_Marketplace_Repository $amazonMarketplaceRepository */
        $amazonMarketplaceRepository = Mage::getModel('M2ePro/Amazon_Marketplace_Repository');
        foreach ($amazonMarketplaceRepository->findWithAccounts() as $marketplace) {
            $activeMarketplaces[(int)$marketplace->getId()] = $marketplace;
        }

        $outdatedMarketplaces = array();
        /** @var Ess_M2ePro_Model_Amazon_Dictionary_ProductType_Repository $dictionaryProductTypeRepository */
        $dictionaryProductTypeRepository = Mage::getModel('M2ePro/Amazon_Dictionary_ProductType_Repository');
        foreach ($dictionaryProductTypeRepository->findValidOutOfDate() as $productType) {
            if (!isset($activeMarketplaces[$productType->getMarketplaceId()])) {
                continue;
            }

            if (isset($outdatedMarketplaces[$productType->getMarketplaceId()])) {
                continue;
            }

            $outdatedMarketplaces[$productType->getMarketplaceId()] = true;
        }

        $productOutOfDateCache->set($result = !empty($outdatedMarketplaces));


        return $result;
    }
}