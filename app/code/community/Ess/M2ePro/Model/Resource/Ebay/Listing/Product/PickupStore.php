<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Ebay_Listing_Product_PickupStore
    extends Ess_M2ePro_Model_Resource_Abstract
{
    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Listing_Product_PickupStore', 'id');
    }

    //########################################

    public function processDeletedProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();
        $onlineSku = $ebayListingProduct->getOnlineSku();

        if (!empty($onlineSku)) {
            /** @var Ess_M2ePro_Model_Resource_Ebay_Listing_Product_PickupStore_Collection $pickupStoreCollection */
            $pickupStoreCollection = Mage::getResourceModel('M2ePro/Ebay_Listing_Product_PickupStore_Collection');
            $pickupStoreCollection->addFieldToFilter('listing_product_id', $listingProduct->getId());

            $usedPickupStoresIds = $pickupStoreCollection->getColumnValues('account_pickup_store_id');
            if (empty($usedPickupStoresIds)) {
                return;
            }

            $this->_getWriteAdapter()->update(
                Mage::getResourceModel('M2ePro/Ebay_Account_PickupStore_State')->getMainTable(),
                array('is_deleted' => 1),
                array('sku = ?' => $onlineSku, 'account_pickup_store_id IN (?)' => $usedPickupStoresIds)
            );
        }

        $this->_getWriteAdapter()->delete(
            Mage::getResourceModel('M2ePro/Ebay_Listing_Product_PickupStore')->getMainTable(),
            array('listing_product_id = ?' => $listingProduct->getId())
        );
    }

    public function processDeletedVariation(Ess_M2ePro_Model_Listing_Product_Variation $variation)
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation $ebayVariation */
        $ebayVariation = $variation->getChildObject();
        $onlineSku = $ebayVariation->getOnlineSku();

        if (empty($onlineSku)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Resource_Ebay_Listing_Product_PickupStore_Collection $pickupStoreCollection */
        $pickupStoreCollection = Mage::getResourceModel('M2ePro/Ebay_Listing_Product_PickupStore_Collection');
        $pickupStoreCollection->addFieldToFilter('listing_product_id', $variation->getListingProductId());

        $usedPickupStoresIds = $pickupStoreCollection->getColumnValues('account_pickup_store_id');
        if (empty($usedPickupStoresIds)) {
            return;
        }

        $this->_getWriteAdapter()->update(
            Mage::getResourceModel('M2ePro/Ebay_Account_PickupStore_State')->getMainTable(),
            array('is_deleted' => 1),
            array('sku = ?' => $onlineSku, 'account_pickup_store_id IN (?)' => $usedPickupStoresIds)
        );
    }

    //########################################
}
