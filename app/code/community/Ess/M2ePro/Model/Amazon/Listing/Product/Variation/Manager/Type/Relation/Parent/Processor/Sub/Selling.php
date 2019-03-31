<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Listing_Product as Product;

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Selling
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Abstract
{
    //########################################

    protected function check() {}

    protected function execute()
    {
        $qty           = null;
        $regularPrice  = null;
        $businessPrice = null;

        $afnState = NULL;
        $repricingState = NULL;
        $isAfn = Product::IS_AFN_CHANNEL_NO;
        $isRepricing = Product::IS_REPRICING_NO;
        $repricingManaged = $repricingNotManaged = $afnCount = $totalCount = 0;

        foreach ($this->getProcessor()->getTypeModel()->getChildListingsProducts() as $listingProduct) {

            if ($listingProduct->isNotListed() || $listingProduct->isBlocked()) {
                continue;
            }

            $totalCount++;

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            if ($amazonListingProduct->isRepricingUsed()) {

                $isRepricing = Product::IS_REPRICING_YES;

                if($amazonListingProduct->isRepricingManaged()) {
                    $repricingManaged++;
                } else {
                    $repricingNotManaged++;
                }
            }

            if ($amazonListingProduct->isAfnChannel()) {

                $isAfn = Product::IS_AFN_CHANNEL_YES;
                $afnCount++;

            } else {
                $qty = (int)$qty + (int)$amazonListingProduct->getOnlineQty();
            }

            $regularSalePrice = $amazonListingProduct->getOnlineRegularSalePrice();
            $actualOnlineRegularPrice = $amazonListingProduct->getOnlineRegularPrice();

            if ($regularSalePrice > 0) {

                $startDateTimestamp = strtotime($amazonListingProduct->getOnlineRegularSalePriceStartDate());
                $endDateTimestamp   = strtotime($amazonListingProduct->getOnlineRegularSalePriceEndDate());

                $currentTimestamp = strtotime(Mage::helper('M2ePro')->getCurrentGmtDate(false,'Y-m-d 00:00:00'));

                if ($currentTimestamp >= $startDateTimestamp &&
                    $currentTimestamp <= $endDateTimestamp &&
                    $regularSalePrice < $actualOnlineRegularPrice
                ) {
                    $actualOnlineRegularPrice = $regularSalePrice;
                }
            }

            if (is_null($regularPrice) || $regularPrice > $actualOnlineRegularPrice) {
                $regularPrice = $actualOnlineRegularPrice;
            }

            if (is_null($businessPrice) || $businessPrice > $amazonListingProduct->getOnlineBusinessPrice()) {
                $businessPrice = $amazonListingProduct->getOnlineBusinessPrice();
            }
        }

        ($afnCount == 0)           && $afnState = Product::VARIATION_PARENT_IS_AFN_STATE_ALL_NO;
        ($afnCount > 0)            && $afnState = Product::VARIATION_PARENT_IS_AFN_STATE_PARTIAL;
        ($afnCount == $totalCount) && $afnState = Product::VARIATION_PARENT_IS_AFN_STATE_ALL_YES;

        $totalOnRepricing = $repricingNotManaged + $repricingManaged;
        ($totalOnRepricing == 0)           && $repricingState = Product::VARIATION_PARENT_IS_REPRICING_STATE_ALL_NO;
        ($totalOnRepricing > 0)            && $repricingState = Product::VARIATION_PARENT_IS_REPRICING_STATE_PARTIAL;
        ($totalOnRepricing == $totalCount) && $repricingState = Product::VARIATION_PARENT_IS_REPRICING_STATE_ALL_YES;

        $this->getProcessor()->getListingProduct()->addData(array(
            'online_qty'                       => $qty,
            'online_regular_price'             => $regularPrice,
            'online_business_price'            => $businessPrice,
            'is_afn_channel'                   => $isAfn,
            'is_repricing'                     => $isRepricing,
            'variation_parent_afn_state'       => $afnState,
            'variation_parent_repricing_state' => $repricingState
        ));

        $this->getProcessor()->getListingProduct()->setSetting(
            'additional_data', 'repricing_managed_count', $repricingManaged
        );
        $this->getProcessor()->getListingProduct()->setSetting(
            'additional_data', 'repricing_not_managed_count', $repricingNotManaged
        );
        $this->getProcessor()->getListingProduct()->setSetting(
            'additional_data', 'afn_count', $afnCount
        );
    }

    //########################################
}