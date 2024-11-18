<?php

class Ess_M2ePro_Model_Amazon_Account_DeleteManager
{
    /** @var Ess_M2ePro_Model_ActiveRecord_Factory */
    private $activeRecordFactory;
    /** @var Ess_M2ePro_Model_Amazon_Template_Shipping_Delete */
    private $templateShippingDeleteService;

    public function __construct()
    {
        $this->activeRecordFactory = Mage::getSingleton('M2ePro/ActiveRecord_Factory');
        $this->templateShippingDeleteService = Mage::getModel('M2ePro/Amazon_Template_Shipping_Delete');
    }

    /**
     * @param \Ess_M2ePro_Model_Account $account
     * @return void
     * @throws \Ess_M2ePro_Model_Exception_Logic|\Ess_M2ePro_Model_Exception
     */
    public function process(Ess_M2ePro_Model_Account $account)
    {
        $otherListings = $this->activeRecordFactory->getObjectCollection('Listing_Other');
        $otherListings->addFieldToFilter('account_id', $account->getId());
        /** @var Ess_M2ePro_Model_Listing_Other $otherListing */
        foreach ($otherListings->getItems() as $otherListing) {
            $otherListing->deleteProcessings();
            $otherListing->deleteProcessingLocks();

            $this->assertSuccess($otherListing->deleteInstance(), 'Listing Other');
        }

        $listings = $this->activeRecordFactory->getObjectCollection('Listing');
        $listings->addFieldToFilter('account_id', $account->getId());
        /** @var Ess_M2ePro_Model_Listing $listing */
        foreach ($listings->getItems() as $listing) {
            $listing->deleteProcessings();
            $listing->deleteProcessingLocks();

            $listing->deleteListingProductsForce();

            $this->assertSuccess($listing->deleteInstance(), 'Listing');
        }

        $orders = $this->activeRecordFactory->getObjectCollection('Order');
        $orders->addFieldToFilter('account_id', $account->getId());
        /** @var Ess_M2ePro_Model_Order $order */
        foreach ($orders->getItems() as $order) {
            $order->deleteProcessings();
            $order->deleteProcessingLocks();

            $this->assertSuccess($order->deleteInstance(), 'Order');
        }

        /** @var Ess_M2ePro_Model_Amazon_Account $amazonAccount */
        $amazonAccount = $account->getChildObject();

        $amazonAccount->deleteInventorySku();
        $amazonAccount->deleteProcessingListSku();

        $itemCollection = $this->activeRecordFactory->getObjectCollection('Amazon_Item');
        $itemCollection->addFieldToFilter('account_id', $amazonAccount->getId());
        /** @var Ess_M2ePro_Model_Amazon_Item $item */
        foreach ($itemCollection->getItems() as $item) {
            $item->deleteProcessings();
            $item->deleteProcessingLocks();

            $this->assertSuccess($item->deleteInstance(), 'Item');
        }

        if ($amazonAccount->isRepricing()) {
            $amazonAccountRepricing = $amazonAccount->getRepricing();

            $amazonAccountRepricing->deleteProcessings();
            $amazonAccountRepricing->deleteProcessingLocks();

            $this->assertSuccess($amazonAccountRepricing->$amazonAccountRepricing(), 'Account Repricing');
        }

        $this->templateShippingDeleteService->deleteByAccount($account);

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
