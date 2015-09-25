<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Templates_Relist
    extends Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Templates_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/relist/';
    }

    protected function getTitle()
    {
        return 'Relist';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 60;
    }

    protected function getPercentsEnd()
    {
        return 70;
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

        $changedListingsOthers = $this->getChangedInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        /** @var $listingOther Ess_M2ePro_Model_Listing_Other */
        foreach ($changedListingsOthers as $listingOther) {

            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Other_Action_Configurator');

            /** @var Ess_M2ePro_Model_Ebay_Listing_Other $ebayListingOther */
            $ebayListingOther = $listingOther->getChildObject();

            if (!$ebayListingOther->getSynchronizationModel()->isRelistSendData()) {
                $configurator->setEmptyMode();
            }

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingOther, Ess_M2ePro_Model_Listing_Product::ACTION_RELIST, $configurator
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetRelistRequirements($listingOther)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingOther, Ess_M2ePro_Model_Listing_Product::ACTION_RELIST, $configurator
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //####################################
}