<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Account_DeleteManager
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

        /** @var Ess_M2ePro_Model_Ebay_Account $ebayAccount */
        $ebayAccount = $account->getChildObject();

        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->delete(
                Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('m2epro_ebay_account_store_category'),
                array('account_id = ?' => $ebayAccount->getId())
            );

        $storeCategoryTemplates = $this->_activeRecordFactory->getObjectCollection('Ebay_Template_StoreCategory');
        $storeCategoryTemplates->addFieldToFilter('account_id', $ebayAccount->getId());
        /** @var Ess_M2ePro_Model_Ebay_Template_StoreCategory $storeCategoryTemplate */
        foreach ($storeCategoryTemplates->getItems() as $storeCategoryTemplate) {
            $storeCategoryTemplate->deleteProcessings();
            $storeCategoryTemplate->deleteProcessingLocks();

            $this->assertSuccess($storeCategoryTemplate->deleteInstance(), 'Store Category Template');
        }

        $feedbacks = $this->_activeRecordFactory->getObjectCollection('Ebay_Feedback');
        $feedbacks->addFieldToFilter('account_id', $ebayAccount->getId());
        /** @var Ess_M2ePro_Model_Ebay_Feedback $feedback */
        foreach ($feedbacks->getItems() as $feedback) {
            $feedback->deleteProcessings();
            $feedback->deleteProcessingLocks();

            $this->assertSuccess($feedback->deleteInstance(), 'Feedback');
        }

        $itemCollection = $this->_activeRecordFactory->getObjectCollection('Ebay_Item');
        $itemCollection->addFieldToFilter('account_id', $ebayAccount->getId());
        /** @var Ess_M2ePro_Model_Ebay_Item $item */
        foreach ($itemCollection->getItems() as $item) {
            $item->deleteProcessings();
            $item->deleteProcessingLocks();

            $this->assertSuccess($item->deleteInstance(), 'Item');
        }

        $shippingTemplateCollection = $this->_activeRecordFactory->getObjectCollection('Ebay_Template_Shipping')
            ->applyLinkedAccountFilter($ebayAccount->getId());
        /** @var Ess_M2ePro_Model_Ebay_Template_Shipping $item */
        foreach ($shippingTemplateCollection->getItems() as $item) {
            $item->deleteShippingRateTables($account);
            $item->save();
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
