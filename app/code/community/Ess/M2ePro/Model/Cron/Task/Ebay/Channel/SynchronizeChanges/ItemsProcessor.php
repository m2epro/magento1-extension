<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Ebay_Channel_SynchronizeChanges_ItemsProcessor
{
    const INSTRUCTION_INITIATOR = 'channel_changes_synchronization';

    const EBAY_STATUS_ACTIVE    = 'Active';
    const EBAY_STATUS_ENDED     = 'Ended';
    const EBAY_STATUS_COMPLETED = 'Completed';

    const INCREASE_SINCE_TIME_MAX_ATTEMPTS     = 10;
    const INCREASE_SINCE_TIME_BY               = 2;
    const INCREASE_SINCE_TIME_MIN_INTERVAL_SEC = 10;

    protected $_logsActionId = null;

    /** @var Ess_M2ePro_Model_Synchronization_Log */
    protected $_synchronizationLog = null;

    protected $_receiveChangesToDate = null;

    //####################################

    public function setSynchronizationLog(Ess_M2ePro_Model_Synchronization_Log $log)
    {
        $this->_synchronizationLog = $log;
        return $this;
    }

    public function setReceiveChangesToDate($toDate)
    {
        $this->_receiveChangesToDate = $toDate;
        return $this;
    }

    //####################################

    public function process()
    {
        $accounts = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account')->getItems();

        foreach ($accounts as $account) {
            try {
                $this->processAccount($account);
            } catch (Exception $exception) {
                $this->_synchronizationLog->addMessage(
                    Mage::helper('M2ePro')->__($exception->getMessage()),
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
                );

                Mage::helper('M2ePro/Module_Exception')->process($exception);
            }
        }
    }

    // ---------------------------------------

    protected function processAccount(Ess_M2ePro_Model_Account $account)
    {
        $changesByAccount = $this->getChangesByAccount($account);

        if (!isset($changesByAccount['items']) || !isset($changesByAccount['to_time'])) {
            return;
        }

        Mage::helper('M2ePro/Data_Cache_Runtime')->setValue(
            'item_get_changes_data_' . $account->getId(), $changesByAccount
        );

        foreach ($changesByAccount['items'] as $change) {
            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $listingProduct = Mage::helper('M2ePro/Component_Ebay')->getListingProductByEbayItem(
                $change['id'], $account->getId()
            );

            if ($listingProduct === null) {
                continue;
            }

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
            $ebayListingProduct = $listingProduct->getChildObject();

            $isVariationOnChannel = !empty($change['variations']);
            $isVariationInMagento = $ebayListingProduct->isVariationsReady();

            if ($isVariationOnChannel != $isVariationInMagento) {
                continue;
            }

            // Listing product isn't listed and it child must have another item_id
            if ($listingProduct->getStatus() != Ess_M2ePro_Model_Listing_Product::STATUS_LISTED &&
                $listingProduct->getStatus() != Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN) {
                continue;
            }

            $dataForUpdate = array_merge(
                $this->getProductDatesChanges($listingProduct, $change),
                $this->getProductStatusChanges($listingProduct, $change),
                $this->getProductQtyChanges($listingProduct, $change)
            );

            if (!$isVariationOnChannel || !$isVariationInMagento) {
                $dataForUpdate = array_merge(
                    $dataForUpdate,
                    $this->getSimpleProductPriceChanges($listingProduct, $change)
                );

                $listingProduct->addData($dataForUpdate)->save();
            } else {
                $listingProductVariations = $listingProduct->getVariations(true);

                $this->processVariationChanges($listingProduct, $listingProductVariations, $change['variations']);

                $dataForUpdate = array_merge(
                    $dataForUpdate,
                    $this->getVariationProductPriceChanges($listingProduct, $listingProductVariations)
                );

                $oldListingProductStatus = $listingProduct->getStatus();

                $listingProduct->addData($dataForUpdate)->save();

                if ($oldListingProductStatus != $listingProduct->getStatus()) {
                    $ebayListingProduct->updateVariationsStatus();
                }
            }
        }

        $account->getChildObject()->setData('defaults_last_synchronization', $changesByAccount['to_time'])->save();
    }

    //########################################

    protected function getChangesByAccount(Ess_M2ePro_Model_Account $account)
    {
        $now = new DateTime('now', new DateTimeZone('UTC'));

        $sinceTime = $this->prepareSinceTime($account->getData('defaults_last_synchronization'));
        $toTime    = clone $now;

        if ($this->_receiveChangesToDate !== null) {
            $toTime = $this->_receiveChangesToDate;
            $toTime = new DateTime($toTime, new DateTimeZone('UTC'));

            if ($sinceTime->getTimestamp() >= $toTime->getTimestamp()) {
                $sinceTime = clone $toTime;
                $sinceTime->modify('- 1 minute');
            }
        }

        $response = $this->receiveChangesFromEbay(
            $account,
            array(
                'since_time' => $sinceTime->format('Y-m-d H:i:s'),
                'to_time'    => $toTime->format('Y-m-d H:i:s')
            )
        );

        if ($response) {
            return (array)$response;
        }

        // -- to many changes are received. try to receive changes for the latest day
        $currentInterval = $toTime->diff($sinceTime);
        if ($currentInterval->days >= 1) {
            $sinceTime = clone $toTime;
            $sinceTime->modify('-1 day');

            $response = $this->receiveChangesFromEbay(
                $account,
                array(
                    'since_time' => $sinceTime->format('Y-m-d H:i:s'),
                    'to_time'    => $toTime->format('Y-m-d H:i:s')
                )
            );

            if ($response) {
                return (array)$response;
            }
        }

        // --

        // -- to many changes are received. increase the sinceData step by step by 2
        $iteration = 0;
        do {
            $iteration++;

            $offset = ceil(($toTime->getTimestamp() - $sinceTime->getTimestamp()) / self::INCREASE_SINCE_TIME_BY);
            $toTime->modify("-{$offset} seconds");

            $currentInterval = $toTime->getTimestamp() - $sinceTime->getTimestamp();

            if ($currentInterval < self::INCREASE_SINCE_TIME_MIN_INTERVAL_SEC ||
                $iteration > self::INCREASE_SINCE_TIME_MAX_ATTEMPTS)
            {
                $sinceTime = clone $now;
                $sinceTime->modify('-5 seconds');

                $toTime = clone $now;
            }

            $response = $this->receiveChangesFromEbay(
                $account,
                array(
                    'since_time' => $sinceTime->format('Y-m-d H:i:s'),
                    'to_time'    => $toTime->format('Y-m-d H:i:s')
                ),
                $iteration
            );

            if ($response) {
                return (array)$response;
            }
        } while ($iteration <= self::INCREASE_SINCE_TIME_MAX_ATTEMPTS);
        // --

        return array();
    }

    //########################################

    protected function receiveChangesFromEbay(
        Ess_M2ePro_Model_Account $account,
        array $paramsConnector = array(),
        $tryNumber = 0
    ) {
        $dispatcherObj = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector(
            'inventory', 'get', 'events',
            $paramsConnector, null,
            null, $account->getId()
        );

        $dispatcherObj->process($connectorObj);
        $this->processResponseMessages($connectorObj->getResponseMessages());

        $responseData = $connectorObj->getResponseData();

        if (!isset($responseData['items']) || !isset($responseData['to_time'])) {
            $logData = array(
                'params'            => $paramsConnector,
                'account_id'        => $account->getId(),
                'response_data'     => $responseData,
                'response_messages' => $connectorObj->getResponseMessages()
            );
            Mage::helper('M2ePro/Module_Logger')->process($logData, "ebay no changes received - #{$tryNumber} try");

            return null;
        }

        return $responseData;
    }

    protected function processResponseMessages(array $messages)
    {
        /** @var Ess_M2ePro_Model_Connector_Connection_Response_Message_Set $messagesSet */
        $messagesSet = Mage::getModel('M2ePro/Connector_Connection_Response_Message_Set');
        $messagesSet->init($messages);

        foreach ($messagesSet->getEntities() as $message) {
            if ($message->getCode() == 21917062) {
                continue;
            }

            if (!$message->isError() && !$message->isWarning()) {
                continue;
            }

            $logType = $message->isError() ? Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                : Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;

            $this->_synchronizationLog->addMessage(
                Mage::helper('M2ePro')->__($message->getText()),
                $logType,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }
    }

    //########################################

    protected function getProductDatesChanges(Ess_M2ePro_Model_Listing_Product $listingProduct, array $change)
    {
        return array(
            'start_date' => Mage::helper('M2ePro/Component_Ebay')->timeToString($change['startTime']),
            'end_date' => Mage::helper('M2ePro/Component_Ebay')->timeToString($change['endTime'])
        );
    }

    protected function getProductStatusChanges(Ess_M2ePro_Model_Listing_Product $listingProduct, array $change)
    {
        $data = array();

        $qty = (int)$change['quantity'] < 0 ? 0 : (int)$change['quantity'];
        $qtySold = (int)$change['quantitySold'] < 0 ? 0 : (int)$change['quantitySold'];

        if (($change['listingStatus'] == self::EBAY_STATUS_COMPLETED ||
             $change['listingStatus'] == self::EBAY_STATUS_ENDED) &&
             $listingProduct->getStatus() != Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN &&
             $qty == $qtySold
        ) {
            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_SOLD;
        } else if ($change['listingStatus'] == self::EBAY_STATUS_COMPLETED) {
            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
        } else if ($change['listingStatus'] == self::EBAY_STATUS_ENDED) {
            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED;
        } else if ($change['listingStatus'] == self::EBAY_STATUS_ACTIVE && $qty - $qtySold <= 0) {
            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN;
        } else if ($change['listingStatus'] == self::EBAY_STATUS_ACTIVE) {
            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
        }

        if ($data['status'] == Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN) {
            // Listed Hidden Status can be only for GTC items
            if ($listingProduct->getChildObject()->getOnlineDuration() === null) {
                $data['online_duration'] = Ess_M2ePro_Helper_Component_Ebay::LISTING_DURATION_GTC;
            }
        }

        if ($listingProduct->getStatus() == $data['status']) {
            return $data;
        }

        $data['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

        $statusChangedFrom = Mage::helper('M2ePro/Component_Ebay')
            ->getHumanTitleByListingProductStatus($listingProduct->getStatus());
        $statusChangedTo = Mage::helper('M2ePro/Component_Ebay')
            ->getHumanTitleByListingProductStatus($data['status']);

        if (!empty($statusChangedFrom) && !empty($statusChangedTo)) {
            $this->logReportChange(
                $listingProduct, Mage::helper('M2ePro')->__(
                    'Item Status was successfully changed from "%from%" to "%to%" .',
                    $statusChangedFrom,
                    $statusChangedTo
                )
            );
        }

        $this->addInstruction(
            $listingProduct, Ess_M2ePro_Model_Ebay_Listing_Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED, 80
        );

        return $data;
    }

    protected function getProductQtyChanges(Ess_M2ePro_Model_Listing_Product $listingProduct, array $change)
    {
        $data = array();

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $data['online_qty'] = (int)$change['quantity'] < 0 ? 0 : (int)$change['quantity'];
        $data['online_qty_sold'] = (int)$change['quantitySold'] < 0 ? 0 : (int)$change['quantitySold'];

        if ($ebayListingProduct->isVariationsReady()) {
            return $data;
        }

        $listingType = $this->getActualListingType($listingProduct, $change);

        if ($listingType == Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION) {
            $data['online_qty'] = 1;
            $data['online_bids'] = (int)$change['bidCount'] < 0 ? 0 : (int)$change['bidCount'];
        }

        if ($ebayListingProduct->getOnlineQty() != $data['online_qty'] ||
            $ebayListingProduct->getOnlineQtySold() != $data['online_qty_sold']) {
            $this->logReportChange(
                $listingProduct, Mage::helper('M2ePro')->__(
                    'Item QTY was successfully changed from %from% to %to% .',
                    ($ebayListingProduct->getOnlineQty() - $ebayListingProduct->getOnlineQtySold()),
                    ($data['online_qty'] - $data['online_qty_sold'])
                )
            );

            $this->addInstruction(
                $listingProduct, Ess_M2ePro_Model_Ebay_Listing_Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED, 80
            );
        }

        return $data;
    }

    // ---------------------------------------

    protected function getSimpleProductPriceChanges(Ess_M2ePro_Model_Listing_Product $listingProduct, array $change)
    {
        $data = array();

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        if ($ebayListingProduct->isVariationsReady()) {
            return $data;
        }

        $data['online_current_price'] = (float)$change['currentPrice'] < 0 ? 0 : (float)$change['currentPrice'];
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $listingType = $this->getActualListingType($listingProduct, $change);

        if ($listingType == Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_FIXED) {
            if ($ebayListingProduct->getOnlineCurrentPrice() != $data['online_current_price']) {
                $this->logReportChange(
                    $listingProduct, Mage::helper('M2ePro')->__(
                        'Item Price was successfully changed from %from% to %to% .',
                        $ebayListingProduct->getOnlineCurrentPrice(),
                        $data['online_current_price']
                    )
                );

                $this->addInstruction(
                    $listingProduct, Ess_M2ePro_Model_Ebay_Listing_Product::INSTRUCTION_TYPE_CHANNEL_PRICE_CHANGED, 60
                );
            }
        }

        return $data;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @param Ess_M2ePro_Model_Listing_Product_Variation[] $variations
     * @return array
     */
    protected function getVariationProductPriceChanges(
        Ess_M2ePro_Model_Listing_Product $listingProduct,
        array $variations
    ) {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $calculateWithEmptyQty = $ebayListingProduct->isOutOfStockControlEnabled();

        $onlineCurrentPrice  = null;

        foreach ($variations as $variation) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation $ebayVariation */
            $ebayVariation = $variation->getChildObject();

            if (!$calculateWithEmptyQty && $ebayVariation->getOnlineQty() <= 0) {
                continue;
            }

            if ($onlineCurrentPrice !== null && $ebayVariation->getOnlinePrice() >= $onlineCurrentPrice) {
                continue;
            }

            $onlineCurrentPrice = $ebayVariation->getOnlinePrice();
        }

        return array('online_current_price' => $onlineCurrentPrice);
    }

    //########################################

    protected function processVariationChanges(
        Ess_M2ePro_Model_Listing_Product $listingProduct,
        array $listingProductVariations,
        array $changeVariations
    ) {
        $variationsSnapshot = $this->getVariationsSnapshot($listingProductVariations);
        if (empty($variationsSnapshot)) {
            return;
        }

        $hasVariationPriceChanges = false;
        $hasVariationQtyChanges   = false;

        foreach ($changeVariations as $changeVariation) {
            foreach ($variationsSnapshot as $variationSnapshot) {
                if (!$this->isVariationEqualWithChange($listingProduct, $changeVariation, $variationSnapshot)) {
                    continue;
                }

                $updateData = array(
                    'online_price' => (float)$changeVariation['price'] < 0 ? 0 : (float)$changeVariation['price'],
                    'online_qty' => (int)$changeVariation['quantity'] < 0 ? 0 : (int)$changeVariation['quantity'],
                    'online_qty_sold' => (int)$changeVariation['quantitySold'] < 0 ?
                        0 : (int)$changeVariation['quantitySold']
                );

                /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation $ebayVariation */
                $ebayVariation = $variationSnapshot['variation']->getChildObject();

                $isVariationChanged = false;

                if ($ebayVariation->getOnlinePrice() != $updateData['online_price']) {
                    $hasVariationPriceChanges = true;
                    $isVariationChanged       = true;
                }

                if ($ebayVariation->getOnlineQty() != $updateData['online_qty'] ||
                    $ebayVariation->getOnlineQtySold() != $updateData['online_qty_sold']) {
                    $hasVariationQtyChanges = true;
                    $isVariationChanged     = true;
                }

                if ($isVariationChanged) {
                    $variationSnapshot['variation']->addData($updateData)->save();
                    $variationSnapshot['variation']->getChildObject()->setStatus($listingProduct->getStatus());
                }

                break;
            }
        }

        if ($hasVariationPriceChanges) {
            $this->logReportChange(
                $listingProduct, Mage::helper('M2ePro')->__(
                    'Price of some Variations was successfully changed.'
                )
            );

            $this->addInstruction(
                $listingProduct, Ess_M2ePro_Model_Ebay_Listing_Product::INSTRUCTION_TYPE_CHANNEL_PRICE_CHANGED, 60
            );
        }

        if ($hasVariationQtyChanges) {
            $this->logReportChange(
                $listingProduct, Mage::helper('M2ePro')->__(
                    'QTY of some Variations was successfully changed.'
                )
            );

            $this->addInstruction(
                $listingProduct, Ess_M2ePro_Model_Ebay_Listing_Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED, 80
            );
        }
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product_Variation[] $variations
     * @return array
     */
    protected function getVariationsSnapshot(array $variations)
    {
        $variationIds = array();
        foreach ($variations as $variation) {
            $variationIds[] = $variation->getId();
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Variation_Option_Collection $optionCollection */
        $optionCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product_Variation_Option');
        $optionCollection->addFieldToFilter('listing_product_variation_id', array('in' => $variationIds));

        $snapshot = array();

        foreach ($variations as $variation) {
            $options = $optionCollection->getItemsByColumnValue('listing_product_variation_id', $variation->getId());

            if (empty($options)) {
                continue;
            }

            $snapshot[] = array(
                'variation' => $variation,
                'options'   => $options
            );
        }

        return $snapshot;
    }

    protected function isVariationEqualWithChange(
        Ess_M2ePro_Model_Listing_Product $listingProduct,
        array $changeVariation,
        array $variationSnapshot
    ) {
        if (count($variationSnapshot['options']) != count($changeVariation['specifics'])) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();
        $specificsReplacements = $ebayListingProduct->getVariationSpecificsReplacements();

        foreach ($variationSnapshot['options'] as $variationSnapshotOption) {
            /** @var Ess_M2ePro_Model_Listing_Product_Variation_Option $variationSnapshotOption */

            $variationSnapshotOptionName  = trim($variationSnapshotOption->getData('attribute'));
            $variationSnapshotOptionValue = trim($variationSnapshotOption->getData('option'));

            if (array_key_exists($variationSnapshotOptionName, $specificsReplacements)) {
                $variationSnapshotOptionName = $specificsReplacements[$variationSnapshotOptionName];
            }

            $haveOption = false;

            foreach ($changeVariation['specifics'] as $changeVariationOption=>$changeVariationValue) {
                if ($variationSnapshotOptionName == trim($changeVariationOption) &&
                    $variationSnapshotOptionValue == trim($changeVariationValue))
                {
                    $haveOption = true;
                    break;
                }
            }

            if ($haveOption === false) {
                return false;
            }
        }

        return true;
    }

    //########################################

    protected function prepareSinceTime($sinceTime)
    {
        if (empty($sinceTime)) {
            $sinceTime = new DateTime('now', new DateTimeZone('UTC'));
            $sinceTime->modify('-5 seconds');

            return $sinceTime;
        }

        $minTime = new DateTime('now', new DateTimeZone('UTC'));
        $minTime->modify('-5 days');

        $sinceTime = new DateTime($sinceTime, new DateTimeZone('UTC'));

        if ($sinceTime->getTimestamp() < $minTime->getTimestamp()) {
            return $minTime;
        }

        $maxSinceTime = new DateTime('now', new DateTimeZone('UTC'));
        $maxSinceTime->modify('-1 minute');

        if ($sinceTime->getTimestamp() > $maxSinceTime->getTimestamp()) {
            return $maxSinceTime;
        }

        return $sinceTime;
    }

    // ---------------------------------------

    protected function getLogsActionId()
    {
        if ($this->_logsActionId === null) {
            $this->_logsActionId = Mage::getModel('M2ePro/Listing_Log')->getResource()->getNextActionId();
        }

        return $this->_logsActionId;
    }

    protected function getActualListingType(Ess_M2ePro_Model_Listing_Product $listingProduct, array $change)
    {
        $validEbayValues = array(
            Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_General::LISTING_TYPE_AUCTION,
            Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_General::LISTING_TYPE_FIXED
        );

        if (isset($change['listingType']) && in_array($change['listingType'], $validEbayValues)) {
            switch ($change['listingType']) {
                case Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_General::LISTING_TYPE_AUCTION:
                    $result = Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION;
                    break;
                case Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_General::LISTING_TYPE_FIXED:
                    $result = Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_FIXED;
                    break;
            }
        } else {
            $result = $listingProduct->getChildObject()->getListingType();
        }

        return $result;
    }

    //########################################

    protected function addInstruction(Ess_M2ePro_Model_Listing_Product $listingProduct, $type, $priority)
    {
        $instruction = Mage::getModel('M2ePro/Listing_Product_Instruction');
        $instruction->setData(
            array(
            'listing_product_id' => $listingProduct->getId(),
            'component'          => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'type'               => $type,
            'initiator'          => self::INSTRUCTION_INITIATOR,
            'priority'           => $priority,
            )
        );
        $instruction->save();
    }

    protected function logReportChange(Ess_M2ePro_Model_Listing_Product $listingProduct, $logMessage)
    {
        if (empty($logMessage)) {
            return;
        }

        $log = Mage::getModel('M2ePro/Listing_Log');
        $log->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

        $log->addProductMessage(
            $listingProduct->getListingId(),
            $listingProduct->getProductId(),
            $listingProduct->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
            $this->getLogsActionId(),
            Ess_M2ePro_Model_Listing_Log::ACTION_CHANNEL_CHANGE,
            $logMessage,
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
        );
    }

    //########################################
}
