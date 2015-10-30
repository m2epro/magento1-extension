<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Templates_Revise
    extends Ess_M2ePro_Model_Ebay_Synchronization_Templates_Abstract
{
    //########################################

    /**
     * @return string
     */
    protected function getNick()
    {
        return '/revise/';
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return 'Revise';
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getPercentsStart()
    {
        return 5;
    }

    /**
     * @return int
     */
    protected function getPercentsEnd()
    {
        return 20;
    }

    //########################################

    protected function performActions()
    {
        $this->executeQtyChanged();
        $this->executePriceChanged();

        $this->executeTitleChanged();
        $this->executeSubTitleChanged();
        $this->executeDescriptionChanged();
        $this->executeImagesChanged();

        $this->executeNeedSynchronize();
        $this->executeTotal();
    }

    //########################################

    private function executeQtyChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update Quantity');

        /** @var Ess_M2ePro_Model_Listing_Product[] $changedListingsProducts */
        $changedListingsProducts = $this->getChangesHelper()->getInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        foreach ($changedListingsProducts as $listingProduct) {

            /** @var $configurator Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator */
            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
            $configurator->setPartialMode();
            $configurator->allowQty()->allowVariations();

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetReviseQtyRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function executePriceChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update Price');

        /** @var Ess_M2ePro_Model_Listing_Product[] $changedListingsProducts */
        $changedListingsProducts = $this->getChangesHelper()->getInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        foreach ($changedListingsProducts as $listingProduct) {

            /** @var $configurator Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator */
            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
            $configurator->setPartialMode();
            $configurator->allowPrice()->allowVariations();

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetRevisePriceRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //########################################

    private function executeTitleChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update Title');

        $attributesForProductChange = array();

        /** @var Ess_M2ePro_Model_Ebay_Template_Description $template */
        foreach (Mage::getModel('M2ePro/Ebay_Template_Description')->getCollection()->getItems() as $template) {
            $attributesForProductChange = array_merge($attributesForProductChange,$template->getTitleAttributes());
        }

        /** @var Ess_M2ePro_Model_Listing_Product[] $changedListingsProducts */
        $changedListingsProducts = $this->getChangesHelper()->getInstancesByListingProduct(
            array_unique($attributesForProductChange), true
        );

        foreach ($changedListingsProducts as $listingProduct) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
            $ebayListingProduct = $listingProduct->getChildObject();

            $titleAttributes = $ebayListingProduct->getEbayDescriptionTemplate()->getTitleAttributes();

            if (!in_array($listingProduct->getData('changed_attribute'), $titleAttributes)) {
                continue;
            }

            /** @var $configurator Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator */
            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
            $configurator->setPartialMode();
            $configurator->allowTitle();

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetReviseTitleRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );
        }
    }

    private function executeSubTitleChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update Subtitle');

        $attributesForProductChange = array();

        /** @var Ess_M2ePro_Model_Ebay_Template_Description $template */
        foreach (Mage::getModel('M2ePro/Ebay_Template_Description')->getCollection()->getItems() as $template) {
            $attributesForProductChange = array_merge($attributesForProductChange, $template->getSubTitleAttributes());
        }

        /** @var Ess_M2ePro_Model_Listing_Product[] $changedListingsProducts */
        $changedListingsProducts = $this->getChangesHelper()->getInstancesByListingProduct(
            array_unique($attributesForProductChange), true
        );

        foreach ($changedListingsProducts as $listingProduct) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
            $ebayListingProduct = $listingProduct->getChildObject();

            $subTitleAttributes = $ebayListingProduct->getEbayDescriptionTemplate()->getSubTitleAttributes();

            if (!in_array($listingProduct->getData('changed_attribute'), $subTitleAttributes)) {
                continue;
            }

            /** @var $configurator Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator */
            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
            $configurator->setPartialMode();
            $configurator->allowSubtitle();

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetReviseSubTitleRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function executeDescriptionChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update Description');

        $attributesForProductChange = array();

        /** @var Ess_M2ePro_Model_Ebay_Template_Description $template */
        foreach (Mage::getModel('M2ePro/Ebay_Template_Description')->getCollection()->getItems() as $template) {
            $attributesForProductChange = array_merge(
                $attributesForProductChange,
                $template->getDescriptionAttributes()
            );
        }

        /** @var Ess_M2ePro_Model_Listing_Product[] $changedListingsProducts */
        $changedListingsProducts = $this->getChangesHelper()->getInstancesByListingProduct(
            array_unique($attributesForProductChange), true
        );

        foreach ($changedListingsProducts as $listingProduct) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
            $ebayListingProduct = $listingProduct->getChildObject();

            $descriptionAttributes = $ebayListingProduct->getEbayDescriptionTemplate()->getDescriptionAttributes();

            if (!in_array($listingProduct->getData('changed_attribute'), $descriptionAttributes)) {
                continue;
            }

            /** @var $configurator Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator */
            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
            $configurator->setPartialMode();
            $configurator->allowDescription();

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetReviseDescriptionRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function executeImagesChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update images');

        $attributesForProductChange = array();

        /** @var Ess_M2ePro_Model_Ebay_Template_Description $template */
        foreach (Mage::getModel('M2ePro/Ebay_Template_Description')->getCollection()->getItems() as $template) {
            $attributesForProductChange = array_merge(
                $attributesForProductChange,
                $template->getImageMainAttributes(),
                $template->getGalleryImagesAttributes(),
                $template->getVariationImagesAttributes()
            );
        }

        $attributesForProductChange = array_unique($attributesForProductChange);

        /** @var Ess_M2ePro_Model_Listing_Product[] $changedListingsProducts */
        $changedListingsProducts = $this->getChangesHelper()->getInstancesByListingProduct(
            $attributesForProductChange, true
        );

        foreach ($changedListingsProducts as $listingProduct) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
            $ebayListingProduct = $listingProduct->getChildObject();

            $imagesAttributes = array_merge(
                $ebayListingProduct->getEbayDescriptionTemplate()->getImageMainAttributes(),
                $ebayListingProduct->getEbayDescriptionTemplate()->getGalleryImagesAttributes()
            );

            if (!in_array($listingProduct->getData('changed_attribute'), $imagesAttributes)) {
                continue;
            }

            /** @var $configurator Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator */
            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
            $configurator->setPartialMode();
            $configurator->allowImages();

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetReviseImagesRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );
        }

        /** @var Ess_M2ePro_Model_Listing_Product[] $changedListingsProductsByVariationOption */
        $changedListingsProductsByVariationOption = $this->getChangesHelper()->getInstancesByVariationOption(
            $attributesForProductChange, true
        );

        foreach ($changedListingsProductsByVariationOption as $listingProduct) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
            $ebayListingProduct = $listingProduct->getChildObject();

            $imagesAttributes = $ebayListingProduct->getEbayDescriptionTemplate()->getVariationImagesAttributes();

            if (!in_array($listingProduct->getData('changed_attribute'), $imagesAttributes)) {
                continue;
            }

            /** @var $configurator Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator */
            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
            $configurator->setPartialMode();
            $configurator->allowVariations();

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetReviseImagesRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //########################################

    private function executeNeedSynchronize()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Execute is need synchronize');

        $listingProductCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_LISTED);
        $listingProductCollection->addFieldToFilter('synch_status',Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_NEED);

        $listingProductCollection->getSelect()->limit(100);

        foreach ($listingProductCollection->getItems() as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $listingProduct->setData('synch_status',Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_SKIP)->save();

            /** @var $configurator Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator */
            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetReviseSynchReasonsRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function executeTotal()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Execute Revise all');

        $lastListingProductProcessed = $this->getConfigValue(
            $this->getFullSettingsPath().'total/','last_listing_product_id'
        );

        if (is_null($lastListingProductProcessed)) {
            return;
        }

        $itemsPerCycle = 100;

        /* @var $collection Varien_Data_Collection_Db */
        $collection = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Listing_Product')
            ->addFieldToFilter('id',array('gt' => $lastListingProductProcessed))
            ->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_LISTED);

        $collection->getSelect()->limit($itemsPerCycle);
        $collection->getSelect()->order('id ASC');

        /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($collection->getItems() as $listingProduct) {

            /** @var $configurator Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator */
            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetReviseGeneralRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );
        }

        $lastListingProduct = $collection->getLastItem()->getId();

        if ($collection->count() < $itemsPerCycle) {

            $this->setConfigValue(
                $this->getFullSettingsPath().'total/', 'end_date',
                Mage::helper('M2ePro')->getCurrentGmtDate()
            );

            $lastListingProduct = NULL;
        }

        $this->setConfigValue(
            $this->getFullSettingsPath().'total/', 'last_listing_product_id',
            $lastListingProduct
        );

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //########################################
}