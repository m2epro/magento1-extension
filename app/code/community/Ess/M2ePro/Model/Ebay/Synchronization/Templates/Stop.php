<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Templates_Stop
    extends Ess_M2ePro_Model_Ebay_Synchronization_Templates_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/stop/';
    }

    protected function getTitle()
    {
        return 'Stop';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 35;
    }

    protected function getPercentsEnd()
    {
        return 50;
    }

    //####################################

    protected function performActions()
    {
        $this->immediatelyChangedProducts();
    }

    //####################################

    private function immediatelyChangedProducts()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Immediately when Product was changed');

        /** @var Ess_M2ePro_Model_Listing_Product[] $changedListingsProducts */
        $changedListingsProducts = $this->getChangesHelper()->getInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        foreach ($changedListingsProducts as $listingProduct) {

            $action = $this->getAction($listingProduct);

            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');

            $this->prepareConfigurator($listingProduct, $configurator, $action);

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct, $action, $configurator
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetStopRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct, $action, $configurator
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //####################################

    private function getAction(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        if (!$ebayListingProduct->getEbaySellingFormatTemplate()->getOutOfStockControl()) {
            return Ess_M2ePro_Model_Listing_Product::ACTION_STOP;
        }

        return Ess_M2ePro_Model_Listing_Product::ACTION_REVISE;
    }

    private function prepareConfigurator(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                         Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator $configurator,
                                         $action)
    {
        if ($action != Ess_M2ePro_Model_Listing_Product::ACTION_STOP) {
            $configurator->setParams(array('replaced_action' => Ess_M2ePro_Model_Listing_Product::ACTION_STOP));
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        if (!$ebayListingProduct->getEbaySellingFormatTemplate()->getOutOfStockControl() &&
            $action == Ess_M2ePro_Model_Listing_Product::ACTION_STOP
        ) {
            return;
        }

        $configurator->setPartialMode();
        $configurator->allowQty()->allowVariations();
    }

    //####################################
}