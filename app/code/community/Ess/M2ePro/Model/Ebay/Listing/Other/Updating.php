<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Updating
{
    const EBAY_STATUS_ACTIVE = 'Active';
    const EBAY_STATUS_ENDED = 'Ended';
    const EBAY_STATUS_COMPLETED = 'Completed';

    const EBAY_DURATION_GTC         = 'GTC';
    const EBAY_DURATION_DAYS_PREFIX = 'Days_';

    /**
     * @var Ess_M2ePro_Model_Account|null
     */
    protected $account = NULL;

    protected $logsActionId = NULL;

    //########################################

    public function initialize(Ess_M2ePro_Model_Account $account = NULL)
    {
        $this->account = $account;
    }

    //########################################

    public function processResponseData($responseData)
    {
        $this->updateToTimeLastSynchronization($responseData);

        if (!isset($responseData['items']) || !is_array($responseData['items']) ||
            count($responseData['items']) <= 0) {
            return;
        }

        $responseData['items'] = $this->filterReceivedOnlyOtherListings($responseData['items']);

        /** @var $logModel Ess_M2ePro_Model_Listing_Other_Log */
        $logModel = Mage::getModel('M2ePro/Listing_Other_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

        /** @var $mappingModel Ess_M2ePro_Model_Ebay_Listing_Other_Mapping */
        $mappingModel = Mage::getModel('M2ePro/Ebay_Listing_Other_Mapping');

        foreach ($responseData['items'] as $receivedItem) {

            /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
            $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Other')
                ->addFieldToFilter('item_id', $receivedItem['id'])
                ->addFieldToFilter('account_id', $this->getAccount()->getId());

            /** @var Ess_M2ePro_Model_Listing_Other $existObject */
            $existObject = $collection->getFirstItem();
            $existsId = $existObject->getId();

            if ($existsId && $existObject->isBlocked()) {
                continue;
            }

            $newData = array(
                'title'           => (string)$receivedItem['title'],
                'currency'        => (string)$receivedItem['currency'],
                'online_price'    => (float)$receivedItem['currentPrice'],
                'online_qty'      => (int)$receivedItem['quantity'],
                'online_qty_sold' => (int)$receivedItem['quantitySold'],
                'online_bids'     => (int)$receivedItem['bidCount'],
                'start_date'      => (string)Mage::helper('M2ePro')->getDate($receivedItem['startTime']),
                'end_date'        => (string)Mage::helper('M2ePro')->getDate($receivedItem['endTime'])
            );

            if (isset($receivedItem['listingDuration'])) {

                $duration = str_replace(self::EBAY_DURATION_DAYS_PREFIX, '', $receivedItem['listingDuration']);
                if ($duration == self::EBAY_DURATION_GTC) {
                    $duration = Ess_M2ePro_Helper_Component_Ebay::LISTING_DURATION_GTC;
                }
                $newData['online_duration'] = $duration;
            }

            if (isset($receivedItem['sku'])) {
                $newData['sku'] = (string)$receivedItem['sku'];
            }

            if ($existsId) {
                $newData['id'] = $existsId;
            } else {
                $newData['item_id'] = (double)$receivedItem['id'];
                $newData['account_id'] = (int)$this->getAccount()->getId();
                $newData['marketplace_id'] = (int)Mage::helper('M2ePro/Component_Ebay')
                    ->getCachedObject('Marketplace',
                                      $receivedItem['marketplace'],
                                      'code')
                    ->getId();
            }

            $tempListingType = Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_General::LISTING_TYPE_AUCTION;
            if ($receivedItem['listingType'] == $tempListingType) {
                $newData['online_qty'] = 1;
            }

            if (($receivedItem['listingStatus'] == self::EBAY_STATUS_COMPLETED ||
                 $receivedItem['listingStatus'] == self::EBAY_STATUS_ENDED) &&
                 $newData['online_qty'] == $newData['online_qty_sold']) {

                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_SOLD;

            } else if ($receivedItem['listingStatus'] == self::EBAY_STATUS_COMPLETED) {

                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;

            } else if ($receivedItem['listingStatus'] == self::EBAY_STATUS_ENDED) {

                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED;

            } else if ($receivedItem['listingStatus'] == self::EBAY_STATUS_ACTIVE &&
                       $receivedItem['quantity'] - $receivedItem['quantitySold'] <= 0) {

                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN;

            } else if ($receivedItem['listingStatus'] == self::EBAY_STATUS_ACTIVE) {

                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
            }

            $accountOutOfStockControl = $this->getAccount()->getChildObject()->getOutOfStockControl(true);

            if (isset($receivedItem['out_of_stock'])) {

                $newData['additional_data'] = array('out_of_stock_control' => (bool)$receivedItem['out_of_stock']);
                $newData['additional_data'] = Mage::helper('M2ePro')->jsonEncode($newData['additional_data']);

            } elseif ($newData['status'] == Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN &&
                      !is_null($accountOutOfStockControl) && !$accountOutOfStockControl) {

                // Listed Hidden Status can be only for GTC items
                if (!$existsId || is_null($existObject->getChildObject()->getOnlineDuration())) {
                    $newData['online_duration'] = Ess_M2ePro_Helper_Component_Ebay::LISTING_DURATION_GTC;
                }

                if ($existsId) {
                    $additionalData = $existObject->getAdditionalData();
                    empty($additionalData['out_of_stock_control']) && $additionalData['out_of_stock_control'] = true;
                } else {
                    $additionalData = array('out_of_stock_control' => true);
                }

                $newData['additional_data'] = Mage::helper('M2ePro')->jsonEncode($additionalData);
            }

            if ($existsId) {

                $tempLogMessages = array();

                if ($newData['online_price'] != $existObject->getOnlinePrice()) {
                    // M2ePro_TRANSLATIONS
                    // Item Price was successfully changed from %from% to %to% .
                    $tempLogMessages[] = Mage::helper('M2ePro')->__(
                        'Item Price was successfully changed from %from% to %to% .',
                        $existObject->getOnlinePrice(),
                        $newData['online_price']
                    );
                }

                if ($existObject->getOnlineQty() != $newData['online_qty'] ||
                    $existObject->getOnlineQtySold() != $newData['online_qty_sold']) {
                    // M2ePro_TRANSLATIONS
                    // Item QTY was successfully changed from %from% to %to% .
                    $tempLogMessages[] = Mage::helper('M2ePro')->__(
                        'Item QTY was successfully changed from %from% to %to% .',
                        ($existObject->getOnlineQty() - $existObject->getOnlineQtySold()),
                        ($newData['online_qty'] - $newData['online_qty_sold'])
                    );
                }

                if ($newData['status'] != $existObject->getStatus()) {
                    $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

                    $statusChangedFrom = Mage::helper('M2ePro/Component_Ebay')
                        ->getHumanTitleByListingProductStatus($existObject->getStatus());
                    $statusChangedTo = Mage::helper('M2ePro/Component_Ebay')
                        ->getHumanTitleByListingProductStatus($newData['status']);

                    if (!empty($statusChangedFrom) && !empty($statusChangedTo)) {
                        // M2ePro_TRANSLATIONS
                        // Item Status was successfully changed from "%from%" to "%to%" .
                        $tempLogMessages[] = Mage::helper('M2ePro')->__(
                            'Item Status was successfully changed from "%from%" to "%to%" .',
                            $statusChangedFrom,
                            $statusChangedTo
                        );
                    }
                }

                foreach ($tempLogMessages as $tempLogMessage) {
                    $logModel->addProductMessage(
                        (int)$newData['id'],
                        Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                        $this->getLogsActionId(),
                        Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANNEL_CHANGE,
                        $tempLogMessage,
                        Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                        Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                    );
                }
            } else {
                $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;
            }

            $listingOtherModel = Mage::helper('M2ePro/Component_Ebay')->getModel('Listing_Other');
            $listingOtherModel->setData($newData)->save();

            if (!$existsId) {

                $logModel->addProductMessage($listingOtherModel->getId(),
                     Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                     NULL,
                     Ess_M2ePro_Model_Listing_Other_Log::ACTION_ADD_LISTING,
                    // M2ePro_TRANSLATIONS
                    // Item was successfully Added
                     'Item was successfully Added',
                     Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                     Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW);

                if (!$this->getAccount()->getChildObject()->isOtherListingsMappingEnabled()) {
                    continue;
                }

                $mappingModel->initialize($this->getAccount());
                $mappingModel->autoMapOtherListingProduct($listingOtherModel);
            }
        }
        // ---------------------------------------
    }

    //########################################

    protected function updateToTimeLastSynchronization($responseData)
    {
        $tempToTime = Mage::helper('M2ePro')->getCurrentGmtDate();

        if (isset($responseData['to_time'])) {
            if (is_array($responseData['to_time'])) {
                $tempToTime = array();
                foreach ($responseData['to_time'] as $tempToTime2) {
                    $tempToTime[] = strtotime($tempToTime2);
                }
                sort($tempToTime,SORT_NUMERIC);
                $tempToTime = array_pop($tempToTime);
                $tempToTime = date('Y-m-d H:i:s',$tempToTime);
            } else {
                $tempToTime = $responseData['to_time'];
            }
        }

        if (!is_string($tempToTime) || empty($tempToTime)) {
            $tempToTime = Mage::helper('M2ePro')->getCurrentGmtDate();
        }

        $childAccountObject = $this->getAccount()->getChildObject();
        $childAccountObject->setData('other_listings_last_synchronization', $tempToTime)->save();
    }

    // ---------------------------------------

    protected function filterReceivedOnlyOtherListings(array $receivedItems)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $receivedItemsByItemId = array();
        $receivedItemsIds      = array();

        foreach ($receivedItems as $receivedItem) {
            $receivedItemsIds[] = (string)$receivedItem['id'];
            $receivedItemsByItemId[(string)$receivedItem['id']] = $receivedItem;
        }

        foreach (array_chunk($receivedItemsIds,500,true) as $partReceivedItemsIds) {

            if (count($partReceivedItemsIds) <= 0) {
                continue;
            }

            /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
            $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
            $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);

            $collection->getSelect()->join(
                array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
                'main_table.listing_id = l.id', array()
            );
            $collection->getSelect()->where('l.account_id = ?', (int)$this->getAccount()->getId());

            $collection->getSelect()->join(
                array('eit' => Mage::getResourceModel('M2ePro/Ebay_Item')->getMainTable()),
                'main_table.product_id = eit.product_id AND eit.account_id = '.(int)$this->getAccount()->getId(),
                array('item_id')
            );
            $collection->getSelect()->where('eit.item_id IN (?)', $partReceivedItemsIds);

            /** @var $stmtTemp Zend_Db_Statement_Pdo */
            $queryStmt = $connRead->query($collection->getSelect()->__toString());

            while (($itemId = $queryStmt->fetchColumn()) !== false) {
                unset($receivedItemsByItemId[$itemId]);
            }
        }

        return array_values($receivedItemsByItemId);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->account;
    }

    protected function getLogsActionId()
    {
        if (!is_null($this->logsActionId)) {
            return $this->logsActionId;
        }

        return $this->logsActionId = Mage::getModel('M2ePro/Listing_Other_Log')->getResource()->getNextActionId();
    }

    //########################################
}