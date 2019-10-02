<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Selling
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Abstract
{
    //########################################

    protected function check()
    {
        return null;
    }

    protected function execute()
    {
        $qty   = null;
        $price = null;

        foreach ($this->getProcessor()->getTypeModel()->getChildListingsProducts() as $listingProduct) {

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
            $walmartListingProduct = $listingProduct->getChildObject();

            if ($listingProduct->isNotListed() ||
                ($listingProduct->isBlocked() && !$walmartListingProduct->isOnlinePriceInvalid())) {
                continue;
            }

            $qty = (int)$qty + (int)$walmartListingProduct->getOnlineQty();

            $actualOnlinePrice = $walmartListingProduct->getOnlinePrice();

            if ($actualOnlinePrice !== null && ($price === null || $price > $actualOnlinePrice)) {
                $price = $actualOnlinePrice;
            }
        }

        $this->getProcessor()->getListingProduct()->addData(
            array(
            'online_qty'   => $qty,
            'online_price' => $price,
            )
        );
    }

    //########################################
}