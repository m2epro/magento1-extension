<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Account_DeleteManager
{
    /** @var Ess_M2ePro_Model_ActiveRecord_Factory */
    protected $_activeRecordFactory;

    //########################################

    public function __construct()
    {
        $this->_activeRecordFactory = Mage::getSingleton('M2ePro/ActiveRecord_Factory');
    }

    /**
     * @param \Ess_M2ePro_Model_Account $account
     * @return void
     * @throws \Ess_M2ePro_Model_Exception_Logic|\Ess_M2ePro_Model_Exception
     */
    public function process(Ess_M2ePro_Model_Account $account)
    {
        $otherListings = $this->_activeRecordFactory->getObjectCollection('Listing_Other');
        $otherListings->addFieldToFilter('account_id', $account->getId());
        /** @var Ess_M2ePro_Model_Listing_Other $otherListing */
        foreach ($otherListings->getItems() as $otherListing) {
            $otherListing->deleteProcessings();
            $otherListing->deleteProcessingLocks();

            $this->assertSuccess($otherListing->deleteInstance(), 'Listing Other');
        }

        $listings = $this->_activeRecordFactory->getObjectCollection('Listing');
        $listings->addFieldToFilter('account_id', $account->getId());
        /** @var Ess_M2ePro_Model_Listing $listing */
        foreach ($listings->getItems() as $listing) {
            $listing->deleteProcessings();
            $listing->deleteProcessingLocks();

            $listing->deleteListingProductsForce();

            $this->assertSuccess($listing->deleteInstance(), 'Listing');
        }

        $orders = $this->_activeRecordFactory->getObjectCollection('Order');
        $orders->addFieldToFilter('account_id', $account->getId());
        /** @var Ess_M2ePro_Model_Order $order */
        foreach ($orders->getItems() as $order) {
            $order->deleteProcessings();
            $order->deleteProcessingLocks();

            $this->assertSuccess($order->deleteInstance(), 'Order');
        }

        /** @var Ess_M2ePro_Model_Walmart_Account $walmartAccount */
        $walmartAccount = $account->getChildObject();

        $walmartAccount->deleteProcessingList();

        $itemCollection = $this->_activeRecordFactory->getObjectCollection('Walmart_Item');
        $itemCollection->addFieldToFilter('account_id', $walmartAccount->getId());
        /** @var Ess_M2ePro_Model_Walmart_Item $item */
        foreach ($itemCollection->getItems() as $item) {
            $item->deleteProcessings();
            $item->deleteProcessingLocks();

            $this->assertSuccess($item->deleteInstance(), 'Item');
        }

        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('account');

        $account->deleteProcessings();
        $account->deleteProcessingLocks();

        $this->assertSuccess($account->deleteInstance(), 'Account');
    }

    /**
     * @throws \Ess_M2ePro_Model_Exception
     */
    private function assertSuccess($value, $label)
    {
        if ($value === false) {
            throw new Ess_M2ePro_Model_Exception('Unable to delete ' . $label);
        }
    }
}
