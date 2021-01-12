<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Listing_Product_StopQueue extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'listing/product/stop_queue';

    /**
     * @var int (in seconds)
     */
    protected $_interval = 3600;

    /**
     * @var int (30 days)
     */
    const MAX_PROCESSED_LIFETIME_HOURS_INTERVAL = 720;

    const EBAY_REQUEST_MAX_ITEMS_COUNT    = 10;
    const AMAZON_REQUEST_MAX_ITEMS_COUNT  = 10000;
    const WALMART_REQUEST_MAX_ITEMS_COUNT = 10000;

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
        $this->removeOldRecords();

        $this->processEbay();
        $this->processAmazon();
        $this->processWalmart();
    }

    //########################################

    public function removeOldRecords()
    {
        $minDateTime = new DateTime('now', new DateTimeZone('UTC'));
        $minDateTime->modify('- ' . self::MAX_PROCESSED_LIFETIME_HOURS_INTERVAL . ' hours');

        $collection = Mage::getResourceModel('M2ePro/StopQueue_Collection');
        $collection->addFieldToFilter('is_processed', 1);
        $collection->addFieldToFilter('update_date', array('lt' => $minDateTime->format('Y-m-d H:i:s')));

        foreach ($collection->getItems() as $item) {
            /** @var Ess_M2ePro_Model_StopQueue $item */
            $item->deleteInstance();
        }
    }

    //----------------------------------------

    protected function processEbay()
    {
        /** @var Ess_M2ePro_Model_StopQueue[] $items */
        $items = $this->getNotProcessedItems(Ess_M2ePro_Helper_Component_Ebay::NICK);
        if (empty($items)) {
            return;
        }

        $processedItemsIds = array();
        $accountsMarketplacesRequestData = array();

        foreach ($items as $item) {
            $processedItemsIds[] = $item->getId();

            $itemAdditionalData = $item->getAdditionalData();
            if (empty($itemAdditionalData['request_data'])) {
                continue;
            }

            $itemRequestData = $itemAdditionalData['request_data'];

            $accountMarketplaceActionType = $itemRequestData['account']
                . '_' .
                $itemRequestData['marketplace']
                . '_' .
                $itemRequestData['action_type'];

            $accountsMarketplacesRequestData[$accountMarketplaceActionType][] = array(
                'item_id' => $itemRequestData['item_id'],
            );
        }

        foreach ($accountsMarketplacesRequestData as $accountMarketplaceActionType => $accountMarketplaceRequestData) {
            list($account, $marketplace, $actionType) = explode('_', $accountMarketplaceActionType);

            if ((int)$actionType === Ess_M2ePro_Model_Listing_Product::ACTION_STOP) {
                $this->stopItemEbay($account, $marketplace, $accountMarketplaceRequestData);
            } else {
                $this->hideItemEbay($account, $marketplace, $accountMarketplaceRequestData);
            }
        }

        $this->markItemsAsProcessed($processedItemsIds);
    }

    //----------------------------------------

    protected function stopItemEbay($account, $marketplace, $accountMarketplaceRequestData)
    {
        $requestDataPacks = array_chunk($accountMarketplaceRequestData, self::EBAY_REQUEST_MAX_ITEMS_COUNT);

        foreach ($requestDataPacks as $requestDataPack) {
            $requestData = array(
                'account'     => $account,
                'marketplace' => $marketplace,
                'items'       => $requestDataPack,
            );

            $dispatcher = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
            $connector = $dispatcher->getVirtualConnector('item', 'update', 'ends', $requestData);
            $dispatcher->process($connector);
        }
    }

    protected function hideItemEbay($account, $marketplace, $accountMarketplaceRequestData)
    {
        foreach ($accountMarketplaceRequestData as $requestData) {
            $requestData = array(
                'account'     => $account,
                'marketplace' => $marketplace,
                'item_id'     => $requestData['item_id'],
                'qty'         => 0
            );

            $dispatcher = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
            $connector = $dispatcher->getVirtualConnector('item', 'update', 'revise', $requestData);
            $dispatcher->process($connector);
        }
    }

    //########################################

    protected function processAmazon()
    {
        /** @var Ess_M2ePro_Model_StopQueue[] $items */
        $items = $this->getNotProcessedItems(Ess_M2ePro_Helper_Component_Amazon::NICK);
        if (empty($items)) {
            return;
        }

        $processedItemsIds = array();
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

        /** @var Ess_M2ePro_Model_Resource_Account_Collection $accountsCollection */
        $accountsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');
        $accountsCollection->addFieldToFilter('server_hash', array_keys($accountsRequestData));

        $throttlingManager = Mage::getSingleton('M2ePro/Amazon_ThrottlingManager');

        foreach ($accountsRequestData as $account => $accountRequestData) {
            $requestDataPacks = array_chunk($accountRequestData, self::AMAZON_REQUEST_MAX_ITEMS_COUNT);

            $accountObject = $accountsCollection->getItemByColumnValue('server_hash', $account);

            if ($accountObject !== null &&
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
                $connector = $dispatcher->getVirtualConnector('product', 'update', 'entities', $requestData);
                $dispatcher->process($connector);

                if ($accountObject !== null) {
                    $throttlingManager->registerRequests(
                        $accountObject->getChildObject()->getMerchantId(),
                        Ess_M2ePro_Model_Amazon_ThrottlingManager::REQUEST_TYPE_FEED,
                        1
                    );
                }
            }
        }

        $this->markItemsAsProcessed($processedItemsIds);
    }

    //########################################

    public function processWalmart()
    {
        /** @var Ess_M2ePro_Model_StopQueue[] $items */
        $items = $this->getNotProcessedItems(Ess_M2ePro_Helper_Component_Walmart::NICK);
        if (empty($items)) {
            return;
        }

        $processedItemsIds = array();
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
                'id'       => $item->getId(),
                'sku'      => $itemRequestData['sku'],
                'wpid'     => $itemRequestData['wpid'],
                'qty'      => 0,
                'lag_time' => 0
            );
        }

        /** @var Ess_M2ePro_Model_Resource_Account_Collection $accountsCollection */
        $accountsCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Account');
        $accountsCollection->addFieldToFilter('server_hash', array_keys($accountsRequestData));

        /** @var Ess_M2ePro_Model_Walmart_ThrottlingManager $throttlingManager */
        $throttlingManager = Mage::getSingleton('M2ePro/Walmart_ThrottlingManager');

        foreach ($accountsRequestData as $account => $accountRequestData) {
            $requestDataPacks = array_chunk($accountRequestData, self::WALMART_REQUEST_MAX_ITEMS_COUNT);

            $accountObject = $accountsCollection->getItemByColumnValue('server_hash', $account);

            if ($accountObject !== null &&
                ($throttlingManager->getAvailableRequestsCount(
                        $accountObject->getId(),
                        Ess_M2ePro_Model_Walmart_ThrottlingManager::REQUEST_TYPE_UPDATE_LAG_TIME
                    ) <= 0 ||
                    $throttlingManager->getAvailableRequestsCount(
                        $accountObject->getId(),
                        Ess_M2ePro_Model_Walmart_ThrottlingManager::REQUEST_TYPE_UPDATE_QTY
                    ) <= 0)) {
                continue;
            }

            foreach ($requestDataPacks as $requestDataPack) {
                $requestData = array(
                    'account' => $account,
                    'items'   => $requestDataPack,
                );

                $dispatcher = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');
                $connector = $dispatcher->getVirtualConnector('product', 'update', 'entities', $requestData);
                $dispatcher->process($connector);

                if ($accountObject !== null) {
                    $throttlingManager->registerRequests(
                        $accountObject->getId(),
                        Ess_M2ePro_Model_Walmart_ThrottlingManager::REQUEST_TYPE_UPDATE_QTY,
                        1
                    );
                    $throttlingManager->registerRequests(
                        $accountObject->getId(),
                        Ess_M2ePro_Model_Walmart_ThrottlingManager::REQUEST_TYPE_UPDATE_LAG_TIME,
                        1
                    );
                }
            }
        }

        $this->markItemsAsProcessed($processedItemsIds);
    }

    //########################################

    protected function getNotProcessedItems($component)
    {
        /** @var Ess_M2ePro_Model_Resource_StopQueue_Collection $collection */
        $collection = Mage::getModel('M2ePro/StopQueue')->getCollection();
        $collection->addFieldToFilter('is_processed', 0);
        $collection->addFieldToFilter('component_mode', $component);

        return $collection->getItems();
    }

    protected function markItemsAsProcessed(array $itemsIds)
    {
        if (empty($itemsIds)) {
            return;
        }

        $resource = Mage::getSingleton('core/resource');
        $connWrite = $resource->getConnection('core_write');

        $connWrite->update(
            Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_stop_queue'),
            array('is_processed' => 1, 'update_date' => Mage::helper('M2ePro')->getCurrentGmtDate()),
            array('id IN (?)' => $itemsIds)
        );
    }

    //########################################
}
