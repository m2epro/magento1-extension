<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Other_ResolveNonReceivedData extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'ebay/listing/other/resolve_nonReceived_data';

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

        foreach ($accountsCollection->getItems() as $account) {
            /** @var $account Ess_M2ePro_Model_Account * */

            $this->getOperationHistory()->addTimePoint(
                __METHOD__ . 'process' . $account->getId(),
                'Get and process SKUs for Account ' . $account->getTitle()
            );

            try {
                $this->updateItems($account);
            } catch (Exception $exception) {
                $message = Mage::helper('M2ePro')->__(
                    'The "Update SKUs" Action for eBay Account "%account%" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }

            $this->getOperationHistory()->saveTimePoint(__METHOD__ . 'process' . $account->getId());
        }
    }

    //########################################

    protected function updateItems(Ess_M2ePro_Model_Account $account)
    {
        /** @var $listingOtherCollection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $listingOtherCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Other');
        $listingOtherCollection->addFieldToFilter('main_table.account_id', (int)$account->getId());
        $listingOtherCollection->getSelect()->where('`second_table`.`sku` IS NULL');
        $listingOtherCollection->getSelect()->orWhere('`second_table`.`online_categories_data` IS NULL');
        $listingOtherCollection->getSelect()->orWhere('`second_table`.`online_main_category` IS NULL');
        $listingOtherCollection->getSelect()->order('second_table.start_date ASC');
        $listingOtherCollection->getSelect()->limit(200);

        if (!$listingOtherCollection->getSize()) {
            return;
        }

        $receivedData = $this->receiveFromEbay(
            $account,
            $listingOtherCollection->getFirstItem()->getData('start_date')
        );

        $listingOthers = array();
        foreach ($listingOtherCollection->getItems() as $item) {
            /** @var $item Ess_M2ePro_Model_Listing_Other */
            $listingOthers[(string)$item->getData('item_id')] = $item;
        }

        if (empty($receivedData['items'])) {
            $this->updateNotReceivedItems($listingOthers, null);

            return;
        }

        $this->updateReceivedItems($listingOthers, $account, $receivedData['items']);
        $this->updateNotReceivedItems($listingOthers, $receivedData['to_time']);
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Other[] $listingOthers
     * @param Ess_M2ePro_Model_Account $account
     * @param array $items
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function updateReceivedItems($listingOthers, Ess_M2ePro_Model_Account $account, array $items)
    {
        /** @var $mappingModel Ess_M2ePro_Model_Ebay_Listing_Other_Mapping */
        $mappingModel = Mage::getModel('M2ePro/Ebay_Listing_Other_Mapping');

        foreach ($items as $item) {
            if (!isset($listingOthers[$item['id']])) {
                continue;
            }

            /** @var $listingOther Ess_M2ePro_Model_Listing_Other */
            $listingOther = $listingOthers[$item['id']];

            $newData = array(
                'sku' => (string)$item['sku']
            );

            if (!empty($item['categories'])) {
                $categories = array(
                    'category_main_id'            => 0,
                    'category_secondary_id'       => 0,
                    'store_category_main_id'      => 0,
                    'store_category_secondary_id' => 0,
                );

                foreach ($categories as $categoryKey => &$categoryValue) {
                    if (!empty($item['categories'][$categoryKey])) {
                        $categoryValue = $item['categories'][$categoryKey];
                    }
                }

                unset($categoryValue);

                $categoryPath = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath(
                    $categories['category_main_id'],
                    $listingOther->getMarketplaceId()
                );

                $newData['online_main_category'] = $categoryPath . ' (' . $categories['category_main_id'] . ')';
                $newData['online_categories_data'] = Mage::helper('M2ePro')->jsonEncode($categories);
            }

            $listingOther->getChildObject()->addData($newData);
            $listingOther->getChildObject()->save();

            if ($account->getChildObject()->isOtherListingsMappingEnabled()) {
                $mappingModel->initialize($account);
                $mappingModel->autoMapOtherListingProduct($listingOther);
            }
        }
    }

    protected function updateNotReceivedItems($listingOthers, $toTimeReceived)
    {
        foreach ($listingOthers as $listingOther) {
            /** @var Ess_M2ePro_Model_Ebay_Listing_Other $ebayListingOther */
            $ebayListingOther = $listingOther->getChildObject();

            $sku = $ebayListingOther->getSku();
            $onlineMainCategory = $ebayListingOther->getOnlineMainCategory();
            $onlineCategoriesData = $ebayListingOther->getOnlineCategoriesData();

            if ($sku !== null && $onlineMainCategory !== null && $onlineCategoriesData !== null) {
                continue;
            }

            if ($toTimeReceived !== null &&
                strtotime($ebayListingOther->getStartDate()) >= strtotime($toTimeReceived)
            ) {
                continue;
            }

            $onlineMainCategory === null && $ebayListingOther->setData('online_main_category', '');
            $onlineCategoriesData === null && $ebayListingOther->setData('online_categories_data', '');
            $sku === null && $ebayListingOther->setData('sku', '');

            $ebayListingOther->save();
        }
    }

    //########################################

    protected function receiveFromEbay(Ess_M2ePro_Model_Account $account, $sinceTime)
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
            'inventory',
            'get',
            'items',
            $inputData,
            null,
            null,
            $account->getId()
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
