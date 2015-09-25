<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Ebay_Order_Item extends Ess_M2ePro_Model_Observer_Abstract
{
    //####################################

    public function process()
    {
        /** @var Ess_M2ePro_Model_Order_Item $orderItem */
        $orderItem  = $this->getEvent()->getData('order_item');

        /** @var Ess_M2ePro_Model_Ebay_Order_Item $ebayOrderItem */
        $ebayOrderItem = $orderItem->getChildObject();

        /** @var Mage_Catalog_Model_Product $product */
        $product = $this->getEvent()->getData('product');

        /** @var $listingOtherCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $listingOtherCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Other');
        $listingOtherCollection->addFieldToFilter('account_id', $orderItem->getOrder()->getAccountId());
        $listingOtherCollection->addFieldToFilter('marketplace_id', $orderItem->getOrder()->getMarketplaceId());
        $listingOtherCollection->addFieldToFilter('second_table.item_id', $ebayOrderItem->getItemId());

        $otherListings = $listingOtherCollection->getItems();

        if (!empty($otherListings)) {
            /** @var Ess_M2ePro_Model_Listing_Other $otherListing */
            $otherListing = reset($otherListings);

            if (!is_null($otherListing->getProductId())) {
                return;
            }

            $otherListing->mapProduct($product->getId(), Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);
        } else {
            $dataForAdd = array(
                'account_id'     => $orderItem->getOrder()->getAccountId(),
                'marketplace_id' => $orderItem->getOrder()->getMarketplaceId(),
                'item_id'        => $ebayOrderItem->getItemId(),
                'product_id'     => $product->getId(),
                'store_id'       => $ebayOrderItem->getEbayOrder()->getAssociatedStoreId(),
            );

            Mage::getModel('M2ePro/Ebay_Item')->setData($dataForAdd)->save();
        }
    }

    //####################################
}