<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Ebay_PickupStore_ScheduleForUpdate extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'ebay/pickup_store/schedule_for_update';

    const MAX_AFFECTED_ITEMS_COUNT = 10000;

    //########################################

    public function performActions()
    {
        $account = Mage::helper('M2ePro/Component_Ebay_PickupStore')->getEnabledAccount();
        if (!$account) {
            return;
        }

        $this->getOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');

        $this->getOperationHistory()->addTimePoint(
            __METHOD__.'process'.$account->getId(),
            'Process Account '.$account->getTitle()
        );

        $this->processAccount($account);

        $this->getOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());

        $this->getLockItemManager()->activate();
    }

    //########################################

    private function processAccount(Ess_M2ePro_Model_Account $account)
    {
        $collection = Mage::getResourceModel('M2ePro/Ebay_Listing_Product_PickupStore_Collection');
        $collection->addFieldToFilter('is_process_required', 1);
        $collection->getSelect()->limit(self::MAX_AFFECTED_ITEMS_COUNT);

        $collection->getSelect()->joinLeft(
            array('eaps' => Mage::getResourceModel('M2ePro/Ebay_Account_PickupStore')->getMainTable()),
            'eaps.id = main_table.account_pickup_store_id',
            array('account_id')
        );

        $collection->addFieldToFilter('eaps.account_id', $account->getId());

        $listingProductIds = $collection->getColumnValues('listing_product_id');
        if (empty($listingProductIds)) {
            return;
        }

        $listingProductIds = array_unique($listingProductIds);

        $affectedItemsCount = 0;

        foreach ($listingProductIds as $listingProductId) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product', $listingProductId);

            $pickupStoreStateUpdater = Mage::getModel('M2ePro/Ebay_Listing_Product_PickupStore_State_Updater');
            $pickupStoreStateUpdater->setListingProduct($listingProduct);

            $affectedItemsCount += $pickupStoreStateUpdater->process();

            if ($affectedItemsCount >= self::MAX_AFFECTED_ITEMS_COUNT) {
                break;
            }
        }
    }

    //########################################
}