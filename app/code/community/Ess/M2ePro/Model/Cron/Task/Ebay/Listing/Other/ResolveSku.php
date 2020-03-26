<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Other_ResolveSku extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'ebay/listing/other/resolve_sku';

    //####################################

    public function isPossibleToRun()
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            return false;
        }

        return parent::isPossibleToRun();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getSynchronizationLog()
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
        $synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER_LISTINGS);

        return $synchronizationLog;
    }

    //########################################

    protected function performActions()
    {
        /** @var $accountsCollection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
        $accountsCollection->addFieldToFilter('other_listings_synchronization', 1);

        $accounts = $accountsCollection->getItems();

        if (empty($accounts)) {
            return;
        }

        foreach ($accounts as $account) {

            /** @var $account Ess_M2ePro_Model_Account **/

            $this->getOperationHistory()->addTimePoint(
                __METHOD__.'process'.$account->getId(),
                'Get and process SKUs for Account '.$account->getTitle()
            );

            try {
                $this->updateSkus($account);
            } catch (Exception $exception) {
                $message = Mage::helper('M2ePro')->__(
                    'The "Update SKUs" Action for eBay Account "%account%" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }

            $this->getOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());
            $this->getLockItemManager()->activate();
        }
    }

    //########################################

    protected function updateSkus(Ess_M2ePro_Model_Account $account)
    {
        /** @var $listingOtherCollection Mage_Core_Model_Resource_Db_Collection_Abstract */

        $listingOtherCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Other');
        $listingOtherCollection->addFieldToFilter('main_table.account_id', (int)$account->getId());
        $listingOtherCollection->getSelect()->where('`second_table`.`sku` IS NULL');
        $listingOtherCollection->getSelect()->order('second_table.start_date ASC');
        $listingOtherCollection->getSelect()->limit(200);

        if (!$listingOtherCollection->getSize()) {
            return;
        }

        $firstItem = $listingOtherCollection->getFirstItem();

        $sinceTime = $firstItem->getData('start_date');
        $receivedData = $this->receiveSkusFromEbay($account, $sinceTime);

        if (empty($receivedData['items'])) {
            foreach ($listingOtherCollection->getItems() as $listingOther) {
                $listingOther->getChildObject()->setData('sku', '')->save();
            }

            return;
        }

        $this->updateSkusForReceivedItems($listingOtherCollection, $account, $receivedData['items']);
        $this->updateSkusForNotReceivedItems($listingOtherCollection, $receivedData['to_time']);
    }

    // ---------------------------------------

    protected function updateSkusForReceivedItems(
        $listingOtherCollection,
        Ess_M2ePro_Model_Account $account,
        array $items
    ) {
        /** @var $mappingModel Ess_M2ePro_Model_Ebay_Listing_Other_Mapping */
        $mappingModel = Mage::getModel('M2ePro/Ebay_Listing_Other_Mapping');

        foreach ($items as $item) {
            foreach ($listingOtherCollection->getItems() as $listingOther) {

                /** @var $listingOther Ess_M2ePro_Model_Listing_Other */

                if ((float)$listingOther->getData('item_id') != $item['id']) {
                    continue;
                }

                $listingOther->getChildObject()->setData('sku', (string)$item['sku'])->save();

                if ($account->getChildObject()->isOtherListingsMappingEnabled()) {
                    $mappingModel->initialize($account);
                    $mappingModel->autoMapOtherListingProduct($listingOther);
                }

                break;
            }
        }
    }

    // eBay item IDs which were removed can lead to the issue and getting SKU process freezes
    protected function updateSkusForNotReceivedItems($listingOtherCollection, $toTimeReceived)
    {
        foreach ($listingOtherCollection->getItems() as $listingOther) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Other $ebayListingOther */
            $ebayListingOther = $listingOther->getChildObject();

            if ($ebayListingOther->getSku() !== null) {
                continue;
            }

            if (strtotime($ebayListingOther->getStartDate()) >= strtotime($toTimeReceived)) {
                continue;
            }

            $ebayListingOther->setData('sku', '')->save();
        }
    }

    //########################################

    protected function receiveSkusFromEbay(Ess_M2ePro_Model_Account $account, $sinceTime)
    {
        $sinceTime = new DateTime($sinceTime, new DateTimeZone('UTC'));
        $sinceTime->modify('-1 minute');
        $sinceTime = $sinceTime->format('Y-m-d H:i:s');

        $inputData = array(
            'since_time'    => $sinceTime,
            'only_one_page' => true,
            'realtime'      => true
        );

        $dispatcherObj = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector(
            'inventory', 'get', 'items',
            $inputData, null,
            null, $account->getId()
        );

        $dispatcherObj->process($connectorObj);
        $responseData = $connectorObj->getResponseData();

        if (!isset($responseData['items']) || !is_array($responseData['items'])) {
            return array();
        }

        return $responseData;
    }

    //########################################
}
