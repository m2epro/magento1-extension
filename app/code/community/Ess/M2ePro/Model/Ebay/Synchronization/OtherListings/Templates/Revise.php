<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Templates_Revise
    extends Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Templates_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/revise/';
    }

    protected function getTitle()
    {
        return 'Revise';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 50;
    }

    protected function getPercentsEnd()
    {
        return 60;
    }

    //####################################

    protected function performActions()
    {
        $this->executeQtyChanged();
        $this->executePriceChanged();

        $this->executeTitleChanged();
        $this->executeSubTitleChanged();
        $this->executeDescriptionChanged();
    }

    //####################################

    private function executeQtyChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update Quantity');

        $changedListingsOthers = $this->getChangedInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        /** @var $listingOther Ess_M2ePro_Model_Listing_Other */
        foreach ($changedListingsOthers as $listingOther) {

            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Other_Action_Configurator');
            $configurator->setPartialMode();
            $configurator->allowQty();

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingOther, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetReviseQtyRequirements($listingOther)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingOther, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function executePriceChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update Price');

        $changedListingsOthers = $this->getChangedInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        /** @var $listingOther Ess_M2ePro_Model_Listing_Other */
        foreach ($changedListingsOthers as $listingOther) {
            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Other_Action_Configurator');
            $configurator->setPartialMode();
            $configurator->allowPrice();

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingOther, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetRevisePriceRequirements($listingOther)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingOther, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function executeTitleChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update Title');

        /** @var $tempModel Ess_M2ePro_Model_Ebay_Listing_Other_Source */
        $tempModel = Mage::getModel('M2ePro/Ebay_Listing_Other_Source');

        $attributesForProductChange = array();
        if ($tempModel->isTitleSourceProduct()) {
            $attributesForProductChange[] = 'name';
        } else if ($tempModel->isTitleSourceAttribute() && !is_null($tempModel->getTitleAttribute())) {
            $attributesForProductChange[] = $tempModel->getTitleAttribute();
        }

        $changedListingsOthers = $this->getChangedInstances(
            $attributesForProductChange, true
        );

        /** @var $listingOther Ess_M2ePro_Model_Listing_Other */
        foreach ($changedListingsOthers as $listingOther) {

            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Other_Action_Configurator');
            $configurator->setPartialMode();
            $configurator->allowTitle();

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingOther, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetReviseTitleRequirements($listingOther)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingOther, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function executeSubTitleChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update Subtitle');

        /** @var $tempModel Ess_M2ePro_Model_Ebay_Listing_Other_Source */
        $tempModel = Mage::getModel('M2ePro/Ebay_Listing_Other_Source');

        $attributesForProductChange = array();
        if ($tempModel->isSubTitleSourceAttribute() && !is_null($tempModel->getSubTitleAttribute())) {
            $attributesForProductChange[] = $tempModel->getSubTitleAttribute();
        }

        $changedListingsOthers = $this->getChangedInstances(
            $attributesForProductChange, true
        );

        /** @var $listingOther Ess_M2ePro_Model_Listing_Other */
        foreach ($changedListingsOthers as $listingOther) {

            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Other_Action_Configurator');
            $configurator->setPartialMode();
            $configurator->allowSubtitle();

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingOther, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetReviseSubtitleRequirements($listingOther)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingOther, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function executeDescriptionChanged()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Update Description');

        /** @var $tempModel Ess_M2ePro_Model_Ebay_Listing_Other_Source */
        $tempModel = Mage::getModel('M2ePro/Ebay_Listing_Other_Source');

        $attributesForProductChange = array();
        if ($tempModel->isDescriptionSourceProductMain()) {
            $attributesForProductChange[] = 'description';
        } else if ($tempModel->isDescriptionSourceProductShort()) {
            $attributesForProductChange[] = 'short_description';
        } else if ($tempModel->isDescriptionSourceAttribute() && !is_null($tempModel->getDescriptionAttribute())) {
            $attributesForProductChange[] = $tempModel->getDescriptionAttribute();
        }

        $changedListingsOthers = $this->getChangedInstances(
            $attributesForProductChange, true
        );

        /** @var $listingOther Ess_M2ePro_Model_Listing_Other */
        foreach ($changedListingsOthers as $listingOther) {

            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Other_Action_Configurator');
            $configurator->setPartialMode();
            $configurator->allowDescription();

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingOther, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetReviseDescriptionRequirements($listingOther)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingOther, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //####################################
}