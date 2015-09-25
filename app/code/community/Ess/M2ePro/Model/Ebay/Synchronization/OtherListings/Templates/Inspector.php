<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Templates_Inspector
{
    //####################################

    public function isMeetRelistRequirements(Ess_M2ePro_Model_Listing_Other $listingOther)
    {
        if (!$listingOther->isStopped() || !$listingOther->isRelistable()) {
            return false;
        }

        if (is_null($listingOther->getProductId())) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Other $ebayListingOther */
        $ebayListingOther = $listingOther->getChildObject();
        $ebaySynchronizationTemplate = $ebayListingOther->getSynchronizationModel();

        if (!$ebaySynchronizationTemplate->isMode()) {
            return false;
        }

        if(!$ebaySynchronizationTemplate->isRelistMode()) {
            return false;
        }

        if ($ebaySynchronizationTemplate->isRelistFilterUserLock() &&
            $listingOther->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            return false;
        }

        if ($ebaySynchronizationTemplate->isRelistStatusEnabled() &&
            !$listingOther->getMagentoProduct()->isStatusEnabled()
        ) {
            return false;
        }

        if ($ebaySynchronizationTemplate->isRelistIsInStock() &&
            !$listingOther->getMagentoProduct()->isStockAvailability()
        ) {
            return false;
        }

        if ($ebaySynchronizationTemplate->isRelistWhenQtyHasValue()) {

            $productQty = $ebayListingOther->getMappedQty();

            if (!is_null($productQty)) {

                $result = false;

                $typeQty = (int)$ebaySynchronizationTemplate->getRelistWhenQtyHasValueType();
                $minQty = (int)$ebaySynchronizationTemplate->getRelistWhenQtyHasValueMin();
                $maxQty = (int)$ebaySynchronizationTemplate->getRelistWhenQtyHasValueMax();

                if ($typeQty == Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::RELIST_QTY_LESS &&
                    $productQty <= $minQty) {
                    $result = true;
                }

                if ($typeQty == Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::RELIST_QTY_MORE &&
                    $productQty >= $minQty) {
                    $result = true;
                }

                if ($typeQty == Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::RELIST_QTY_BETWEEN &&
                    $productQty >= $minQty && $productQty <= $maxQty) {
                    $result = true;
                }

                if (!$result) {
                    return false;
                }
            }
        }

        return true;
    }

    public function isMeetStopRequirements(Ess_M2ePro_Model_Listing_Other $listingOther)
    {
        if (!$listingOther->isListed() || !$listingOther->isStoppable()) {
            return false;
        }

        if (is_null($listingOther->getProductId())) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Other $ebayListingOther */
        $ebayListingOther = $listingOther->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingOther->getSynchronizationModel();

        if (!$ebaySynchronizationTemplate->isMode()) {
            return false;
        }

        if ($ebaySynchronizationTemplate->isStopStatusDisabled() &&
            !$listingOther->getMagentoProduct()->isStatusEnabled()
        ) {
            return true;
        }

        if ($ebaySynchronizationTemplate->isStopOutOfStock() &&
            !$listingOther->getMagentoProduct()->isStockAvailability()
        ) {
            return true;
        }

        if ($ebaySynchronizationTemplate->isStopWhenQtyHasValue()) {

            $productQty = $ebayListingOther->getMappedQty();

            if (!is_null($productQty)) {

                $typeQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyHasValueType();
                $minQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyHasValueMin();
                $maxQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyHasValueMax();

                if ($typeQty == Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::STOP_QTY_LESS &&
                    $productQty <= $minQty) {
                    return true;
                }

                if ($typeQty == Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::STOP_QTY_MORE &&
                    $productQty >= $minQty) {
                    return true;
                }

                if ($typeQty == Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::STOP_QTY_BETWEEN &&
                    $productQty >= $minQty && $productQty <= $maxQty) {
                    return true;
                }
            }
        }

        return false;
    }

    //####################################

    protected function isMeetReviseGeneralRequirements(Ess_M2ePro_Model_Listing_Other $listingOther)
    {
        if (!$listingOther->isListed() || !$listingOther->isRevisable()) {
            return false;
        }

        if (is_null($listingOther->getProductId())) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Other $ebayListingOther */
        $ebayListingOther = $listingOther->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingOther->getSynchronizationModel();

        if (!$ebaySynchronizationTemplate->isMode()) {
            return false;
        }

        return true;
    }

    //------------------------------------

    public function isMeetReviseQtyRequirements(Ess_M2ePro_Model_Listing_Other $listingOther)
    {
        if (!$this->isMeetReviseGeneralRequirements($listingOther)) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Other $ebayListingOther */
        $ebayListingOther = $listingOther->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingOther->getSynchronizationModel();

        if (!$ebaySynchronizationTemplate->isReviseWhenChangeQty()) {
            return false;
        }

        $productQty = $ebayListingOther->getMappedQty();

        if (is_null($productQty)) {
            return false;
        }

        $channelQty = $ebayListingOther->getOnlineQty() - $ebayListingOther->getOnlineQtySold();

        if ($productQty > 0 && $productQty != $channelQty) {
            return true;
        }

        return false;
    }

    public function isMeetRevisePriceRequirements(Ess_M2ePro_Model_Listing_Other $listingOther)
    {
        if (!$this->isMeetReviseGeneralRequirements($listingOther)) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Other $ebayListingOther */
        $ebayListingOther = $listingOther->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingOther->getSynchronizationModel();

        if (!$ebaySynchronizationTemplate->isReviseWhenChangePrice()) {
            return false;
        }

        $ebayListingOther = $listingOther->getChildObject();

        $currentPrice = $ebayListingOther->getMappedPrice();

        if (is_null($currentPrice)) {
            return false;
        }

        $onlinePrice = $ebayListingOther->getOnlinePrice();

        if ($currentPrice != $onlinePrice) {
            return true;
        }

        return false;
    }

    public function isMeetReviseTitleRequirements(Ess_M2ePro_Model_Listing_Other $listingOther)
    {
        if (!$this->isMeetReviseGeneralRequirements($listingOther)) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Other $ebayListingOther */
        $ebayListingOther = $listingOther->getChildObject();

        if (!$ebayListingOther->getSynchronizationModel()->isReviseWhenChangeTitle()) {
            return false;
        }

        return true;
    }

    public function isMeetReviseSubtitleRequirements(Ess_M2ePro_Model_Listing_Other $listingOther)
    {
        if (!$this->isMeetReviseGeneralRequirements($listingOther)) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Other $ebayListingOther */
        $ebayListingOther = $listingOther->getChildObject();

        if (!$ebayListingOther->getSynchronizationModel()->isReviseWhenChangeSubTitle()) {
            return false;
        }

        return true;
    }

    public function isMeetReviseDescriptionRequirements(Ess_M2ePro_Model_Listing_Other $listingOther)
    {
        if (!$this->isMeetReviseGeneralRequirements($listingOther)) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Other $ebayListingOther */
        $ebayListingOther = $listingOther->getChildObject();

        if (!$ebayListingOther->getSynchronizationModel()->isReviseWhenChangeDescription()) {
            return false;
        }

        return true;
    }

    //####################################
}