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

    protected function check() {}

    protected function execute()
    {
        $qty   = null;
        $price = null;

        $totalCount = 0;

        foreach ($this->getProcessor()->getTypeModel()->getChildListingsProducts() as $listingProduct) {

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
            $walmartListingProduct = $listingProduct->getChildObject();

            if ($listingProduct->isNotListed() ||
                ($listingProduct->isBlocked() && !$walmartListingProduct->isOnlinePriceInvalid())) {
                continue;
            }

            $totalCount++;

            $qty = (int)$qty + (int)$walmartListingProduct->getOnlineQty();

            $actualOnlinePrice = $walmartListingProduct->getOnlinePrice();

            if (!is_null($actualOnlinePrice) && (is_null($price) || $price > $actualOnlinePrice)) {
                $price = $actualOnlinePrice;
            }
        }

        $this->getProcessor()->getListingProduct()->addData(array(
            'online_qty'   => $qty,
            'online_price' => $price,
        ));
    }

    //########################################
}