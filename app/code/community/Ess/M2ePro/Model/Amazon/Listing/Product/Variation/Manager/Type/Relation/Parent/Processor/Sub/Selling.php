<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2EPro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Selling
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Abstract
{
    //########################################

    protected function check() {}

    protected function execute()
    {
        $qty = null;
        $price = null;

        $isAfn = Ess_M2ePro_Model_Amazon_Listing_Product::IS_AFN_CHANNEL_NO;
        $isRepricing = Ess_M2ePro_Model_Amazon_Listing_Product::IS_REPRICING_NO;
        $repricingEnabled = $repricingDisabled = 0;

        foreach ($this->getProcessor()->getTypeModel()->getChildListingsProducts() as $listingProduct) {

            if ($listingProduct->isNotListed()) {
                continue;
            }

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            if ($amazonListingProduct->isRepricingUsed()) {

                $isRepricing = Ess_M2ePro_Model_Amazon_Listing_Product::IS_REPRICING_YES;

                $amazonListingProduct->isRepricingDisabled() && $repricingDisabled++;
                $amazonListingProduct->isRepricingEnabled()  && $repricingEnabled++;
            }

            if ($amazonListingProduct->isAfnChannel()) {
                $isAfn = Ess_M2ePro_Model_Amazon_Listing_Product::IS_AFN_CHANNEL_YES;
                continue;
            }

            $qty = (int)$qty + (int)$amazonListingProduct->getOnlineQty();

            $actualOnlinePrice = (float)$amazonListingProduct->getOnlinePrice();

            $salePrice = (float)$amazonListingProduct->getOnlineSalePrice();

            if ($salePrice > 0) {

                $startDateTimestamp = strtotime($amazonListingProduct->getOnlineSalePriceStartDate());
                $endDateTimestamp   = strtotime($amazonListingProduct->getOnlineSalePriceEndDate());

                $currentTimestamp = strtotime(Mage::helper('M2ePro')->getCurrentGmtDate(false,'Y-m-d 00:00:00'));

                if ($currentTimestamp >= $startDateTimestamp &&
                    $currentTimestamp <= $endDateTimestamp &&
                    $salePrice < $actualOnlinePrice
                ) {
                    $actualOnlinePrice = $salePrice;
                }
            }

            if (is_null($price) || $price > $actualOnlinePrice) {
                $price = $actualOnlinePrice;
            }
        }

        $this->getProcessor()->getListingProduct()->addData(array(
            'online_qty'        => $qty,
            'online_price'      => $price,
            'is_afn_channel'    => $isAfn,
            'is_repricing'      => $isRepricing
        ));

        $this->getProcessor()->getListingProduct()->setSetting(
            'additional_data', 'repricing_enabled_count', $repricingEnabled
        );
        $this->getProcessor()->getListingProduct()->setSetting(
            'additional_data', 'repricing_disabled_count', $repricingDisabled
        );
    }

    //########################################
}