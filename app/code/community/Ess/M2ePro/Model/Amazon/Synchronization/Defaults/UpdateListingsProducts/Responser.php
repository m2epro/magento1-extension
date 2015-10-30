<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Synchronization_Defaults_UpdateListingsProducts_Responser
    extends Ess_M2ePro_Model_Connector_Amazon_Inventory_Get_ItemsResponser
{
    protected $logsActionId = NULL;
    protected $synchronizationLog = NULL;

    //########################################

    protected function processResponseMessages(array $messages = array())
    {
        parent::processResponseMessages($messages);

        foreach ($this->messages as $message) {

            if (!$this->isMessageError($message) && !$this->isMessageWarning($message)) {
                continue;
            }

            $logType = $this->isMessageError($message) ? Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                                                       : Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;

            $this->getSynchronizationLog()->addMessage(
                Mage::helper('M2ePro')->__($message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY]),
                $logType,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }
    }

    protected function isNeedToParseResponseData($responseBody)
    {
        if (!parent::isNeedToParseResponseData($responseBody)) {
            return false;
        }

        if ($this->hasErrorMessages()) {
            return false;
        }

        return true;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Processing_Request $processingRequest
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function unsetProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::unsetProcessingLocks($processingRequest);

        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');

        $tempNick = Ess_M2ePro_Model_Amazon_Synchronization_Defaults_UpdateListingsProducts::LOCK_ITEM_PREFIX;
        $tempNick .= '_'.$this->params['account_id'];

        $lockItem->setNick($tempNick);
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);

        $lockItem->remove();

        $this->getAccount()->deleteObjectLocks(NULL, $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks('synchronization', $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks('synchronization_amazon', $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks(
            Ess_M2ePro_Model_Amazon_Synchronization_Defaults_UpdateListingsProducts::LOCK_ITEM_PREFIX,
            $processingRequest->getHash()
        );
    }

    public function eventFailedExecuting($message)
    {
        parent::eventFailedExecuting($message);

        $this->getSynchronizationLog()->addMessage(
            Mage::helper('M2ePro')->__($message),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
        );
    }

    public function eventAfterExecuting()
    {
        parent::eventAfterExecuting();

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tempTable = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_processed_inventory');
        $connWrite->delete($tempTable, array('`hash` = ?' => (string)$this->params['processed_inventory_hash']));
    }

    //########################################

    protected function processResponseData($response)
    {
        try {

            $this->updateReceivedListingsProducts($response['data']);
            $this->updateNotReceivedListingsProducts($response['data'], $response['next_part']);

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->getSynchronizationLog()->addMessage(
                Mage::helper('M2ePro')->__($exception->getMessage()),
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }
    }

    //########################################

    protected function updateReceivedListingsProducts($receivedItems)
    {
        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $this->getPdoStatementExistingListings(true);

        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);

        $parentIdsForProcessing = array();

        while ($existingItem = $stmtTemp->fetch()) {

            if (!isset($receivedItems[$existingItem['sku']])) {
                continue;
            }

            $receivedItem = $receivedItems[$existingItem['sku']];

            $newData = array(
                'general_id'         => (string)$receivedItem['identifiers']['general_id'],
                'online_price'       => (float)$receivedItem['price'],
                'online_qty'         => (int)$receivedItem['qty'],
                'is_afn_channel'     => (bool)$receivedItem['channel']['is_afn'],
                'is_isbn_general_id' => (bool)$receivedItem['identifiers']['is_isbn']
            );

            if ($newData['is_afn_channel']) {
                $newData['online_qty'] = NULL;
                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN;
            } else {
                if ($newData['online_qty'] > 0) {
                    $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
                } else {
                    $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
                }
            }

            $existingData = array(
                'general_id'         => (string)$existingItem['general_id'],
                'online_price'       => (float)$existingItem['online_price'],
                'online_qty'         => (int)$existingItem['online_qty'],
                'is_afn_channel'     => (bool)$existingItem['is_afn_channel'],
                'is_isbn_general_id' => (bool)$existingItem['is_isbn_general_id'],
                'status'             => (int)$existingItem['status']
            );

            $existingAdditionalData = @json_decode($existingItem['additional_data'], true);

            if (!empty($existingAdditionalData['last_synchronization_dates']['qty']) &&
                !empty($this->params['request_date'])
            ) {
                $lastQtySynchDate = $existingAdditionalData['last_synchronization_dates']['qty'];

                if ($this->isProductInfoOutdated($lastQtySynchDate)) {
                    unset($newData['online_qty'], $newData['status'], $newData['is_afn_channel']);
                    unset($existingData['online_qty'], $existingData['status'], $existingData['is_afn_channel']);
                }
            }

            if (!empty($existingAdditionalData['last_synchronization_dates']['price']) &&
                !empty($this->params['request_date'])
            ) {
                $lastPriceSynchDate = $existingAdditionalData['last_synchronization_dates']['price'];

                if ($this->isProductInfoOutdated($lastPriceSynchDate)) {
                    unset($newData['online_price']);
                    unset($existingData['online_price']);
                }
            }

            if (!empty($existingAdditionalData['last_synchronization_dates']['fulfillment_switching']) &&
                !empty($this->params['request_date'])
            ) {
                $lastFulfilmentSwitchingDate =
                    $existingAdditionalData['last_synchronization_dates']['fulfillment_switching'];

                if ($this->isProductInfoOutdated($lastFulfilmentSwitchingDate)) {
                    unset($newData['online_qty'], $newData['status'], $newData['is_afn_channel']);
                    unset($existingData['online_qty'], $existingData['status'], $existingData['is_afn_channel']);
                }
            }

            if ($newData == $existingData) {
                continue;
            }

            if ((isset($newData['status']) && $newData['status'] != $existingItem['status']) ||
                (isset($newData['online_qty']) && $newData['online_qty'] != $existingItem['online_qty']) ||
                (isset($newData['online_price']) && $newData['online_price'] != $existingItem['online_price'])
            ) {
                Mage::getModel('M2ePro/ProductChange')->addUpdateAction(
                    $existingItem['product_id'], Ess_M2ePro_Model_ProductChange::INITIATOR_SYNCHRONIZATION
                );

                if (!empty($existingItem['is_variation_product']) && !empty($existingItem['variation_parent_id'])) {
                    $parentIdsForProcessing[] = (int)$existingItem['variation_parent_id'];
                }
            }

            $tempLogMessages = array();

            if (isset($newData['online_price']) && $newData['online_price'] != $existingData['online_price']) {
                // M2ePro_TRANSLATIONS
                // Item Price was successfully changed from %from% to %to% .
                $tempLogMessages[] = Mage::helper('M2ePro')->__(
                    'Item Price was successfully changed from %from% to %to% .',
                    $existingData['online_price'],
                    $newData['online_price']
                );
            }

            if (isset($newData['online_qty']) && $newData['online_qty'] != $existingData['online_qty']) {
                // M2ePro_TRANSLATIONS
                // Item QTY was successfully changed from %from% to %to% .
                $tempLogMessages[] = Mage::helper('M2ePro')->__(
                    'Item QTY was successfully changed from %from% to %to% .',
                    $existingData['online_qty'],
                    $newData['online_qty']
                );
            }

            if (isset($newData['status']) && $newData['status'] != $existingData['status']) {

                $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

                $statusChangedFrom = Mage::helper('M2ePro/Component_Amazon')
                    ->getHumanTitleByListingProductStatus($existingData['status']);
                $statusChangedTo = Mage::helper('M2ePro/Component_Amazon')
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
                $tempLog->addProductMessage(
                    $existingItem['listing_id'],
                    $existingItem['product_id'],
                    $existingItem['listing_product_id'],
                    Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                    $this->getLogsActionId(),
                    Ess_M2ePro_Model_Listing_Log::ACTION_CHANNEL_CHANGE,
                    $tempLogMessage,
                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                );
            }

            $listingProductObj = Mage::helper('M2ePro/Component_Amazon')
                                    ->getObject('Listing_Product',(int)$existingItem['listing_product_id']);

            $listingProductObj->addData($newData)->save();
        }

        if (!empty($parentIdsForProcessing)) {
            $this->processParentProcessors($parentIdsForProcessing);
        }
    }

    protected function updateNotReceivedListingsProducts($receivedItems,$nextPart)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tempTable = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_processed_inventory');

        // ---------------------------------------

        foreach (array_chunk($receivedItems,1000) as $partReceivedItems) {

            $inserts = array();
            foreach ($partReceivedItems as $partReceivedItem) {
                $inserts[] = array(
                    'sku'  => $partReceivedItem['identifiers']['sku'],
                    'hash' => $this->params['processed_inventory_hash']
                );
            }

            $connWrite->insertMultiple($tempTable, $inserts);
        }
        // ---------------------------------------

        if (!is_null($nextPart)) {
            return;
        }

        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();
        $listingProductMainTable = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();

        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->getSelect()->join(array('l' => $listingTable), 'main_table.listing_id = l.id', array());
        $collection->getSelect()->joinLeft(
            array('api' => $tempTable),
            '`second_table`.sku = `api`.sku AND `api`.`hash` = \''.$this->params['processed_inventory_hash'].'\'',
            array('sku')
        );
        $collection->getSelect()->where('l.account_id = ?',(int)$this->getAccount()->getId());
        $collection->getSelect()->where('second_table.is_variation_parent != ?',1);
        $collection->getSelect()->where('`main_table`.`status` != ?',
            (int)Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED);
        $collection->getSelect()->where('`main_table`.`status` != ?',
            (int)Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED);
        $collection->getSelect()->having('`api`.sku IS NULL');

        $tempColumns = array('main_table.id','main_table.status','main_table.listing_id',
                             'main_table.product_id','main_table.additional_data',
                             'second_table.is_variation_product','second_table.variation_parent_id','api.sku');
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns($tempColumns);

        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $connWrite->query($collection->getSelect()->__toString());

        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);

        $parentIdsForProcessing = array();

        $notReceivedIds = array();
        while ($notReceivedItem = $stmtTemp->fetch()) {

            $additionalData = @json_decode($notReceivedItem['additional_data'], true);
            if (is_array($additionalData) && !empty($additionalData['list_date']) &&
                $this->isProductInfoOutdated($additionalData['list_date'])
            ) {
                continue;
            }

            if (!in_array((int)$notReceivedItem['id'],$notReceivedIds)) {
                $statusChangedFrom = Mage::helper('M2ePro/Component_Amazon')
                    ->getHumanTitleByListingProductStatus($notReceivedItem['status']);
                $statusChangedTo = Mage::helper('M2ePro/Component_Amazon')
                    ->getHumanTitleByListingProductStatus(Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED);

                // M2ePro_TRANSLATIONS
                // Item Status was successfully changed from "%from%" to "%to%" .
                $tempLogMessage = Mage::helper('M2ePro')->__(
                    'Item Status was successfully changed from "%from%" to "%to%" .',
                    $statusChangedFrom,
                    $statusChangedTo
                );

                $tempLog->addProductMessage(
                    $notReceivedItem['listing_id'],
                    $notReceivedItem['product_id'],
                    $notReceivedItem['id'],
                    Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                    $this->getLogsActionId(),
                    Ess_M2ePro_Model_Listing_Log::ACTION_CHANNEL_CHANGE,
                    $tempLogMessage,
                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                );

                if (!empty($notReceivedItem['is_variation_product']) &&
                    !empty($notReceivedItem['variation_parent_id'])
                ) {
                    $parentIdsForProcessing[] = $notReceivedItem['variation_parent_id'];
                }
            }

            $notReceivedIds[] = (int)$notReceivedItem['id'];
        }
        $notReceivedIds = array_unique($notReceivedIds);

        $bind = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED,
            'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT
        );

        $chunkedIds = array_chunk($notReceivedIds,1000);
        foreach ($chunkedIds as $partIds) {
            $where = '`id` IN ('.implode(',',$partIds).')';
            $connWrite->update($listingProductMainTable,$bind,$where);
        }

        if (!empty($parentIdsForProcessing)) {
            $this->processParentProcessors($parentIdsForProcessing);
        }
    }

    //########################################

    protected function getPdoStatementExistingListings($withData = false)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();

        /** @var $collection Varien_Data_Collection_Db */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->getSelect()->join(array('l' => $listingTable), 'main_table.listing_id = l.id', array());
        $collection->getSelect()->where('l.account_id = ?',(int)$this->getAccount()->getId());
        $collection->getSelect()->where('`main_table`.`status` != ?',
                                        (int)Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED);
        $collection->getSelect()->where("`second_table`.`sku` is not null and `second_table`.`sku` != ''");
        $collection->getSelect()->where("`second_table`.`is_variation_parent` != ?", 1);

        $tempColumns = array('second_table.sku');

        if ($withData) {
            $tempColumns = array(
                'main_table.listing_id',
                'main_table.product_id','main_table.status',
                'main_table.additional_data',
                'second_table.sku','second_table.general_id',
                'second_table.online_price','second_table.online_qty',
                'second_table.is_afn_channel', 'second_table.is_isbn_general_id',
                'second_table.listing_product_id',
                'second_table.is_variation_product', 'second_table.variation_parent_id',
            );
        }

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns($tempColumns);

        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $connRead->query($collection->getSelect()->__toString());

        return $stmtTemp;
    }

    protected function processParentProcessors(array $parentIds)
    {
        if (empty($parentIds)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $parentListingProductCollection */
        $parentListingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $parentListingProductCollection->addFieldToFilter('id', array('in' => array_unique($parentIds)));

        $parentListingsProducts = $parentListingProductCollection->getItems();
        if (empty($parentListingsProducts)) {
            return;
        }

        $massProcessor = Mage::getModel(
            'M2ePro/Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Mass'
        );
        $massProcessor->setListingsProducts($parentListingsProducts);
        $massProcessor->setForceExecuting(false);

        $massProcessor->execute();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account','account_id');
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getAccount()->getChildObject()->getMarketplace();
    }

    // ---------------------------------------

    protected function getLogsActionId()
    {
        if (!is_null($this->logsActionId)) {
            return $this->logsActionId;
        }

        return $this->logsActionId = Mage::getModel('M2ePro/Listing_Log')->getNextActionId();
    }

    protected function getSynchronizationLog()
    {
        if (!is_null($this->synchronizationLog)) {
            return $this->synchronizationLog;
        }

        $this->synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
        $this->synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_DEFAULTS);

        return $this->synchronizationLog;
    }

    // ---------------------------------------

    private function isProductInfoOutdated($lastDate)
    {
        $lastDate = new DateTime($lastDate, new DateTimeZone('UTC'));
        $requestDate = new DateTime($this->params['request_date'], new DateTimeZone('UTC'));

        $lastDate->modify('+1 hour');

        return $lastDate > $requestDate;
    }

    //########################################
}