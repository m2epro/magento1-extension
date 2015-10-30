<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Observer_Buy_Order_Item extends Ess_M2ePro_Model_Observer_Abstract
{
    //########################################

    public function process()
    {
        /** @var Ess_M2ePro_Model_Order_Item $orderItem */
        $orderItem  = $this->getEvent()->getData('order_item');

        /** @var Ess_M2ePro_Model_Buy_Order_Item $buyOrderItem */
        $buyOrderItem = $orderItem->getChildObject();

        /** @var Mage_Catalog_Model_Product $product */
        $product = $this->getEvent()->getData('product');

        /** @var $listingOtherCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $listingOtherCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Other');
        $listingOtherCollection->addFieldToFilter('account_id', $orderItem->getOrder()->getAccountId());
        $listingOtherCollection->addFieldToFilter('second_table.sku', $buyOrderItem->getSku());

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
                'sku'            => $buyOrderItem->getSku(),
                'product_id'     => $product->getId(),
                'store_id'       => $buyOrderItem->getBuyOrder()->getAssociatedStoreId(),
            );

            Mage::getModel('M2ePro/Buy_Item')->setData($dataForAdd)->save();
        }
    }

    //########################################
}