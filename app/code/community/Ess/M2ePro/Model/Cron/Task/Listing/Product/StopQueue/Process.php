<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Listing_Product_StopQueue_Process extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'listing/product/stop_queue/process';

    const EBAY_REQUEST_MAX_ITEMS_COUNT   = 10;
    const AMAZON_REQUEST_MAX_ITEMS_COUNT = 10000;

    //########################################

    public function isPossibleToRun()
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            return false;
        }

        return parent::isPossibleToRun();
    }

    //########################################

    protected function performActions()
    {
        $this->processEbay();
        $this->processAmazon();
    }

    //########################################

    private function processEbay()
    {
        /** @var Ess_M2ePro_Model_StopQueue[] $items */
        $items = $this->getNotProcessedItems(Ess_M2ePro_Helper_Component_Ebay::NICK);
        if (empty($items)) {
            return;
        }

        $processedItemsIds               = array();
        $accountsMarketplacesRequestData = array();

        foreach ($items as $item) {
            $processedItemsIds[] = $item->getId();

            $itemAdditionalData = $item->getAdditionalData();
            if (empty($itemAdditionalData['request_data'])) {
                continue;
            }

            $itemRequestData = $itemAdditionalData['request_data'];

            $accountMarketplace = $itemRequestData['account'].'_'.$itemRequestData['marketplace'];

            $accountsMarketplacesRequestData[$accountMarketplace][] = array(
                'item_id' => $itemRequestData['item_id'],
            );
        }

        foreach ($accountsMarketplacesRequestData as $accountMarketplace => $accountMarketplaceRequestData) {
            list($account, $marketplace) = explode('_', $accountMarketplace);

            $requestDataPacks = array_chunk($accountMarketplaceRequestData, self::EBAY_REQUEST_MAX_ITEMS_COUNT);

            foreach ($requestDataPacks as $requestDataPack) {
                $requestData = array(
                    'account'     => $account,
                    'marketplace' => $marketplace,
                    'items'       => $requestDataPack,
                );

                $dispatcher = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
                $connector  = $dispatcher->getVirtualConnector('item', 'update', 'ends', $requestData);
                $dispatcher->process($connector);
            }
        }

        $this->markItemsAsProcessed($processedItemsIds);
    }

    private function processAmazon()
    {
        /** @var Ess_M2ePro_Model_StopQueue[] $items */
        $items = $this->getNotProcessedItems(Ess_M2ePro_Helper_Component_Amazon::NICK);
        if (empty($items)) {
            return;
        }

        $processedItemsIds   = array();
        $accountsRequestData = array();

        foreach ($items as $item) {
            $processedItemsIds[] = $item->getId();

            $itemAdditionalData = $item->getAdditionalData();
            if (empty($itemAdditionalData['request_data'])) {
                continue;
            }

            $itemRequestData = $itemAdditionalData['request_data'];

            $account = $itemRequestData['account'];

            $accountsRequestData[$account][] = array(
                'id'  => $item->getId(),
                'sku' => $itemRequestData['sku'],
                'qty' => 0,
            );
        }

        /** @var Ess_M2ePro_Model_Mysql4_Account_Collection $accountsCollection */
        $accountsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');
        $accountsCollection->addFieldToFilter('server_hash', array_keys($accountsRequestData));

        $throttlingManager = Mage::getSingleton('M2ePro/Amazon_ThrottlingManager');

        foreach ($accountsRequestData as $account => $accountRequestData) {
            $requestDataPacks = array_chunk($accountRequestData, self::AMAZON_REQUEST_MAX_ITEMS_COUNT);

            $accountObject = $accountsCollection->getItemByColumnValue('server_hash', $account);

            if ($accountObject->getId() &&
                $throttlingManager->getAvailableRequestsCount(
                    $accountObject->getChildObject()->getMerchantId(),
                    Ess_M2ePro_Model_Amazon_ThrottlingManager::REQUEST_TYPE_FEED
                ) <= 0) {

                continue;
            }

            foreach ($requestDataPacks as $requestDataPack) {
                $requestData = array(
                    'account' => $account,
                    'items'   => $requestDataPack,
                );

                $dispatcher = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
                $connector  = $dispatcher->getVirtualConnector('product', 'update', 'entities', $requestData);
                $dispatcher->process($connector);

                if ($accountObject->getId()) {
                    $throttlingManager->registerRequests(
                        $accountObject->getChildObject()->getMerchantId(),
                        Ess_M2ePro_Model_Amazon_ThrottlingManager::REQUEST_TYPE_FEED, 1
                    );
                }
            }
        }

        $this->markItemsAsProcessed($processedItemsIds);
    }

    //########################################

    private function getNotProcessedItems($component)
    {
        /** @var Ess_M2ePro_Model_Mysql4_StopQueue_Collection $collection */
        $collection = Mage::getModel('M2ePro/StopQueue')->getCollection();
        $collection->addFieldToFilter('is_processed', 0);
        $collection->addFieldToFilter('component_mode', $component);

        return $collection->getItems();
    }

    private function markItemsAsProcessed(array $itemsIds)
    {
        if (empty($itemsIds)) {
            return;
        }

        $resource  = Mage::getSingleton('core/resource');
        $connWrite = $resource->getConnection('core_write');

        $connWrite->update(
            Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_stop_queue'),
            array('is_processed' => 1, 'update_date' => Mage::helper('M2ePro')->getCurrentGmtDate()),
            array('id IN (?)' => $itemsIds)
        );
    }

    //########################################
}