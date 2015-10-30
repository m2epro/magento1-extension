<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Defaults_RemoveDuplicates
    extends Ess_M2ePro_Model_Ebay_Synchronization_Defaults_Abstract
{
    const BLOCKED_PRODUCTS_PER_SYNCH = 10;
    const MAX_ALLOWED_BLOCKED_PRODUCTS = 100;
    const MIN_SECONDS_FROM_FAILED_REQUEST = 300;

    private $duplicatedItems = array();

    //########################################

    protected function getNick()
    {
        return '/remove_duplicates/';
    }

    protected function getTitle()
    {
        return 'Remove Duplicated Products';
    }

    // ---------------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 10;
    }

    //########################################

    protected function performActions()
    {
        $this->checkTooManyBlockedListingProducts();
        $this->checkTooManyBlockedListingOthers();

        $this->processListingProducts();
        $this->processListingOther();

        $this->stopDuplicatedItems();
    }

    //########################################

    private function checkTooManyBlockedListingProducts()
    {
        $collection = $this->getBlockedListingProductCollection();
        $blockedCount = $collection->getSize();

        if ($blockedCount <= self::MAX_ALLOWED_BLOCKED_PRODUCTS) {
            return;
        }

        $collection->getSelect()->limit($blockedCount - self::MAX_ALLOWED_BLOCKED_PRODUCTS);

        foreach ($collection->getItems() as $product) {

            /** @var $product Ess_M2ePro_Model_Listing_Product */

            $productStatus = Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;

            $additionalData = $product->getAdditionalData();
            if (!empty($additionalData['last_failed_action_data']['previous_status'])) {
                $productStatus = $additionalData['last_failed_action_data']['previous_status'];
            }

            $this->modifyAndLogListingProduct($product, $productStatus);
        }
    }

    private function checkTooManyBlockedListingOthers()
    {
        $collection = $this->getBlockedListingOtherCollection();
        $blockedCount = $collection->getSize();

        if ($blockedCount <= self::MAX_ALLOWED_BLOCKED_PRODUCTS) {
            return;
        }

        $collection->getSelect()->limit($blockedCount - self::MAX_ALLOWED_BLOCKED_PRODUCTS);

        foreach ($collection->getItems() as $product) {

            /** @var $product Ess_M2ePro_Model_Listing_Other */

            $productStatus = Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;

            $additionalData = $product->getAdditionalData();
            if (!empty($additionalData['last_failed_action_data']['previous_status'])) {
                $productStatus = $additionalData['last_failed_action_data']['previous_status'];
            }

            $this->modifyAndLogListingOther($product, $productStatus);
        }
    }

    //########################################

    private function processListingProducts()
    {
        $collection = $this->getBlockedListingProductCollection();
        $collection->getSelect()->limit(self::BLOCKED_PRODUCTS_PER_SYNCH);
        $products = $collection->getItems();

        if (empty($products)) {
            return;
        }

        foreach ($products as $product) {

            /** @var $product Ess_M2ePro_Model_Listing_Product */

            $productStatus = Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;

            try {

                $additionalData = $product->getAdditionalData();
                if (empty($additionalData['last_failed_action_data'])) {
                    throw new Ess_M2ePro_Model_Exception('last_failed_action_data is empty');
                }

                $lastFailedActionData = $additionalData['last_failed_action_data'];

                $requestTime = new DateTime($lastFailedActionData['request_time']);
                $currentTime = new DateTime(Mage::helper('M2ePro')->getCurrentGmtDate());
                if ($currentTime->format('U') - $requestTime->format('U') < self::MIN_SECONDS_FROM_FAILED_REQUEST) {
                    continue;
                }

                $productStatus = (int)$lastFailedActionData['previous_status'];
                $action = (int)$lastFailedActionData['action'];
                $accountId = (int)$product->getData('account_id');
                $marketplaceId = (int)$product->getData('marketplace_id');

                if (!isset($this->duplicatedItems[$accountId])) {
                    $this->duplicatedItems[$accountId] = array();
                }
                if (!isset($this->duplicatedItems[$accountId][$marketplaceId])) {
                    $this->duplicatedItems[$accountId][$marketplaceId] = array();
                }

                if ($action == Ess_M2ePro_Model_Listing_Product::ACTION_RELIST) {

                    $itemInfo = $this->getEbayItemInfo(
                        $lastFailedActionData['native_request_data']['item_id'],
                        $accountId
                    );

                    if (empty($itemInfo['relisted_item_id'])) {
                        throw new Ess_M2ePro_Model_Exception('Duplicate was not found');
                    }

                    $this->duplicatedItems[$accountId][$marketplaceId][] = $itemInfo['relisted_item_id'];
                    $this->modifyAndLogListingProduct($product, $productStatus, $itemInfo['relisted_item_id']);

                    continue;
                }

                $timeFrom = new DateTime($lastFailedActionData['request_time']);
                $timeTo = new DateTime($lastFailedActionData['request_time']);

                $timeFrom->modify('-1 minute');
                $timeTo->modify('+1 minute');

                $possibleDuplicates = $this->getEbayItemsByStartTimeInterval($timeFrom, $timeTo, $accountId);

                $marketplaceCode = Mage::getModel('M2ePro/Marketplace')
                    ->loadInstance($marketplaceId)
                    ->getCode();

                $duplicatedItem = $this->getDuplicateItemFromPossible($possibleDuplicates, array(
                        'title' => $lastFailedActionData['native_request_data']['title'],
                        'sku' => $lastFailedActionData['native_request_data']['sku'],
                        'marketplace' => $marketplaceCode,
                ));

                if (empty($duplicatedItem)) {
                    throw new Ess_M2ePro_Model_Exception('Duplicate was not found');
                }

                $this->duplicatedItems[$accountId][$marketplaceId][] = $duplicatedItem['id'];
                $this->modifyAndLogListingProduct($product, $productStatus, $duplicatedItem['id']);

            } catch(Exception $e) {
                $this->modifyAndLogListingProduct($product, $productStatus);
            }
        }
    }

    private function processListingOther()
    {
        $collection = $this->getBlockedListingOtherCollection();
        $collection->getSelect()->limit(self::BLOCKED_PRODUCTS_PER_SYNCH);
        $products = $collection->getItems();

        if (empty($products)) {
            return;
        }

        foreach ($products as $product) {

            /** @var $product Ess_M2ePro_Model_Listing_Other */

            $productStatus = Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;

            try {

                $additionalData = $product->getAdditionalData();
                if (empty($additionalData['last_failed_action_data'])) {
                    throw new Ess_M2ePro_Model_Exception('last_failed_action_data is empty');
                }

                $lastFailedActionData = $additionalData['last_failed_action_data'];

                $requestTime = new DateTime($lastFailedActionData['request_time']);
                $currentTime = new DateTime(Mage::helper('M2ePro')->getCurrentGmtDate());
                if ($currentTime->format('U') - $requestTime->format('U') < self::MIN_SECONDS_FROM_FAILED_REQUEST) {
                    continue;
                }

                $productStatus = (int)$lastFailedActionData['previous_status'];
                $accountId = (int)$product->getData('account_id');
                $marketplaceId = (int)$product->getData('marketplace_id');

                if (!isset($this->duplicatedItems[$accountId])) {
                    $this->duplicatedItems[$accountId] = array();
                }
                if (!isset($this->duplicatedItems[$accountId][$marketplaceId])) {
                    $this->duplicatedItems[$accountId][$marketplaceId] = array();
                }

                $itemInfo = $this->getEbayItemInfo(
                    $lastFailedActionData['native_request_data']['item_id'],
                    $accountId
                );

                if (empty($itemInfo['relisted_item_id'])) {
                    throw new Ess_M2ePro_Model_Exception('Duplicate was not found');
                }

                $this->duplicatedItems[$accountId][$marketplaceId][] = $itemInfo['relisted_item_id'];
                $this->modifyAndLogListingOther($product, $productStatus, $itemInfo['relisted_item_id']);

            } catch(Exception $e) {
                $this->modifyAndLogListingOther($product, $productStatus);
            }
        }
    }

    // ---------------------------------------

    private function stopDuplicatedItems()
    {
        if (empty($this->duplicatedItems)) {
            return;
        }

        foreach ($this->duplicatedItems as $accountId => $marketplaceItems) {

            foreach ($marketplaceItems as $marketplaceId => $itemIds) {

                if (empty($itemIds)) {
                    continue;
                }

                $itemsParts = array_chunk(array_unique($itemIds), 10);

                foreach ($itemsParts as $itemsPart) {
                    try {

                        $dispatcherObj = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');
                        $connectorObj = $dispatcherObj->getVirtualConnector('item','update','ends',
                                                                            array('items' => $itemsPart),NULL,
                                                                            $marketplaceId,$accountId,NULL);
                        $dispatcherObj->process($connectorObj);

                    } catch (Exception $e) {}
                }
            }
        }
    }

    //########################################

    private function getBlockedListingProductCollection()
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $collection */
        $collection = Mage::getModel('M2ePro/Listing_Product')->getCollection();

        $collection->addFieldToFilter('main_table.component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK)
            ->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED)
            ->join(
                'Listing', 'Listing.id=main_table.listing_id',
                array('Listing.account_id', 'Listing.marketplace_id')
            );

        return $collection;
    }

    private function getBlockedListingOtherCollection()
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $collection */
        $collection = Mage::getModel('M2ePro/Listing_Other')->getCollection();

        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK)
            ->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED);

        return $collection;
    }

    //########################################

    private function getEbayItemInfo($itemId, $accountId)
    {
        $dispatcherObj = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector('item','get','info',
                                                            array('item_id' => $itemId),NULL,
                                                            NULL,$accountId,NULL);

        $responseData = $dispatcherObj->process($connectorObj);
        return isset($responseData['result']) ? $responseData['result'] : array();
    }

    private function getEbayItemsByStartTimeInterval($timeFrom, $timeTo, $accountId)
    {
        is_object($timeFrom) && $timeFrom = $timeFrom->format('Y-m-d H:i:s');
        is_object($timeTo)   && $timeTo = $timeTo->format('Y-m-d H:i:s');

        $dispatcherObj = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector('item','get','all',
                                                            array('since_time'=>$timeFrom,
                                                                  'to_time'=>$timeTo),NULL,
                                                            NULL,$accountId,NULL);

        $responseData = $dispatcherObj->process($connectorObj);
        return isset($responseData['items']) ? $responseData['items'] : array();
    }

    // ---------------------------------------

    private function getDuplicateItemFromPossible(array $possibleDuplicates, array $searchParams)
    {
        if (empty($possibleDuplicates)) {
            return array();
        }

        foreach ($possibleDuplicates as $item) {

            $isFound = true;

            foreach ($searchParams as $key => $value) {

                if (trim($item[$key]) == trim($value)) {
                    continue;
                }

                $isFound = false;
                break;
            }

            if (!$isFound) {
                continue;
            }

            return $item;
        }

        return array();
    }

    //########################################

    private function modifyAndLogListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                                $status, $duplicateItemId = null)
    {
        /** @var Ess_M2ePro_Model_Listing_Log $logModel */
        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

        $logsActionId = $logModel->getNextActionId();

        $statusLogMessage = $this->getStatusLogMessage($listingProduct->getStatus(), $status);

        $logModel->addProductMessage(
            $listingProduct->getData('listing_id'),
            $listingProduct->getData('product_id'),
            $listingProduct->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
            $logsActionId,
            Ess_M2ePro_Model_Listing_Log::ACTION_CHANNEL_CHANGE,
            $statusLogMessage,
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
        );

        $additionalData = $listingProduct->getAdditionalData();
        unset($additionalData['last_failed_action_data']);

        $listingProduct->addData(array(
            'status' => $status,
            'additional_data' => json_encode($additionalData),
        ))->save();

        $listingProduct->getChildObject()->updateVariationsStatus();

        if (is_null($duplicateItemId)) {
            return;
        }

        // M2ePro_TRANSLATIONS
        // Duplicated Item %item_id% was found and Stopped on eBay.
        $textToTranslate = 'Duplicated Item %item_id% was found and stopped on eBay.';
        $duplicateDeletedMessage = Mage::helper('M2ePro')->__($textToTranslate, $duplicateItemId);

        $logModel->addProductMessage(
            $listingProduct->getData('listing_id'),
            $listingProduct->getData('product_id'),
            $listingProduct->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
            $logsActionId,
            Ess_M2ePro_Model_Listing_Log::ACTION_CHANNEL_CHANGE,
            $duplicateDeletedMessage,
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
        );
    }

    private function modifyAndLogListingOther(Ess_M2ePro_Model_Listing_Other $listingOther,
                                              $status, $duplicateItemId = null)
    {
        /** @var Ess_M2ePro_Model_Listing_Other_Log $logModel */
        $logModel = Mage::getModel('M2ePro/Listing_Other_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

        $logActionId = $logModel->getNextActionId();

        $statusLogMessage = $this->getStatusLogMessage($listingOther->getStatus(), $status);

        $logModel->addProductMessage(
            $listingOther->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
            $logActionId,
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANNEL_CHANGE,
            $statusLogMessage,
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
        );

        $additionalData = $listingOther->getAdditionalData();
        unset($additionalData['last_failed_action_data']);

        $listingOther->addData(array(
            'status' => $status,
            'additional_data' => json_encode($additionalData),
        ))->save();

        if (is_null($duplicateItemId)) {
            return;
        }

        // M2ePro_TRANSLATIONS
        // Duplicated Item %item_id% was found and Stopped on eBay.

        $textToTranslate = 'Duplicated Item %item_id% was found and stopped on eBay.';
        $duplicateDeletedMessage = Mage::helper('M2ePro')->__($textToTranslate, $duplicateItemId);

        $logModel->addProductMessage(
            $listingOther->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
            $logActionId,
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANNEL_CHANGE,
            $duplicateDeletedMessage,
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
        );
    }

    // ---------------------------------------

    private function getStatusLogMessage($statusFrom, $statusTo)
    {
        $message = '';

        $statusChangedFrom = Mage::helper('M2ePro/Component_Ebay')
            ->getHumanTitleByListingProductStatus($statusFrom);
        $statusChangedTo = Mage::helper('M2ePro/Component_Ebay')
            ->getHumanTitleByListingProductStatus($statusTo);

        if (!empty($statusChangedFrom) && !empty($statusChangedTo)) {
            // M2ePro_TRANSLATIONS
            // Item Status was successfully changed from "%from%" to "%to%" .
            $message = Mage::helper('M2ePro')->__(
                'Item Status was successfully changed from "%from%" to "%to%" .',
                $statusChangedFrom,
                $statusChangedTo
            );
        }

        return $message;
    }

    //########################################
}