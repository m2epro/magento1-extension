<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Synchronization_OtherListings_Update_Responser
    extends Ess_M2ePro_Model_Connector_Buy_Inventory_Get_ItemsResponser
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

        $tempNick = Ess_M2ePro_Model_Buy_Synchronization_OtherListings_Update::LOCK_ITEM_PREFIX;
        $tempNick .= '_'.$this->params['account_id'];

        $lockItem->setNick($tempNick);
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);

        $lockItem->remove();

        $this->getAccount()->deleteObjectLocks(NULL, $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks('synchronization', $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks('synchronization_buy', $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks(
            Ess_M2ePro_Model_Buy_Synchronization_OtherListings_Update::LOCK_ITEM_PREFIX, $processingRequest->getHash()
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

    //########################################

    protected function processResponseData($response)
    {
        $receivedItems = $this->filterReceivedOnlyOtherListings($response['data']);

        try {

            $this->updateReceivedOtherListings($receivedItems);
            $this->createNotExistedOtherListings($receivedItems);

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

    protected function updateReceivedOtherListings($receivedItems)
    {
        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $this->getPdoStatementExistingListings(true);

        $tempLog = Mage::getModel('M2ePro/Listing_Other_Log');
        $tempLog->setComponentMode(Ess_M2ePro_Helper_Component_Buy::NICK);

        while ($existingItem = $stmtTemp->fetch()) {

            if (!isset($receivedItems[$existingItem['sku']])) {
                continue;
            }

            $receivedItem = $receivedItems[$existingItem['sku']];

            $newData = array(
                'general_id' => (int)$receivedItem['general_id'],
                'online_price' => (float)$receivedItem['price'],
                'online_qty' => (int)$receivedItem['qty'],
                'condition' => (int)$receivedItem['condition'],
                'condition_note' => (string)$receivedItem['condition_note'],
                'shipping_standard_rate' => (float)$receivedItem['shipping_standard_rate'],
                'shipping_expedited_mode' => (int)$receivedItem['shipping_expedited_mode'],
                'shipping_expedited_rate' => (float)$receivedItem['shipping_expedited_rate']
            );

            if ($newData['online_qty'] > 0) {
                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
            } else {
                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
            }

            $existingData = array(
                'general_id' => (int)$existingItem['general_id'],
                'online_price' => (float)$existingItem['online_price'],
                'online_qty' => (int)$existingItem['online_qty'],
                'condition' => (int)$existingItem['condition'],
                'condition_note' => (string)$existingItem['condition_note'],
                'shipping_standard_rate' => (float)$existingItem['shipping_standard_rate'],
                'shipping_expedited_mode' => (int)$existingItem['shipping_expedited_mode'],
                'shipping_expedited_rate' => (float)$existingItem['shipping_expedited_rate'],
                'status' => (int)$existingItem['status']
            );

            if ($newData == $existingData) {
                continue;
            }

            $tempLogMessages = array();

            if ($newData['online_price'] != $existingData['online_price']) {
                // M2ePro_TRANSLATIONS
                // Item Price was successfully changed from %from% to %to% .
                $tempLogMessages[] = Mage::helper('M2ePro')->__(
                    'Item Price was successfully changed from %from% to %to% .',
                    $existingItem['online_price'],
                    $newData['online_price']
                );
            }

            if ($newData['online_qty'] != $existingData['online_qty']) {
                // M2ePro_TRANSLATIONS
                // Item QTY was successfully changed from %from% to %to% .
                $tempLogMessages[] = Mage::helper('M2ePro')->__(
                    'Item QTY was successfully changed from %from% to %to% .',
                    $existingItem['online_qty'],
                    $newData['online_qty']
                );
            }

            if ($newData['status'] != $existingData['status']) {
                $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

                $statusChangedFrom = Mage::helper('M2ePro/Component_Buy')
                    ->getHumanTitleByListingProductStatus($existingData['status']);
                $statusChangedTo = Mage::helper('M2ePro/Component_Buy')
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
                    (int)$existingItem['listing_other_id'],
                    Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                    $this->getLogsActionId(),
                    Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANNEL_CHANGE,
                    $tempLogMessage,
                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                );
            }

            $listingOtherObj = Mage::helper('M2ePro/Component_Buy')
                                ->getObject('Listing_Other',(int)$existingItem['listing_other_id']);

            $listingOtherObj->addData($newData)->save();
        }
    }

    protected function createNotExistedOtherListings($receivedItems)
    {
        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $this->getPdoStatementExistingListings(false);

        while ($existingItem = $stmtTemp->fetch()) {

            if (!isset($receivedItems[$existingItem['sku']])) {
                continue;
            }

            $receivedItems[$existingItem['sku']]['founded'] = true;
        }

        /** @var $logModel Ess_M2ePro_Model_Listing_Other_Log */
        $logModel = Mage::getModel('M2ePro/Listing_Other_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Buy::NICK);

        /** @var $mappingModel Ess_M2ePro_Model_Buy_Listing_Other_Mapping */
        $mappingModel = Mage::getModel('M2ePro/Buy_Listing_Other_Mapping');

        /** @var $movingModel Ess_M2ePro_Model_Buy_Listing_Other_Moving */
        $movingModel = Mage::getModel('M2ePro/Buy_Listing_Other_Moving');

        foreach ($receivedItems as $receivedItem) {

            if (isset($receivedItem['founded'])) {
                continue;
            }

            $newData = array(
                'account_id' => (int)$this->params['account_id'],
                'marketplace_id' => Ess_M2ePro_Helper_Component_Buy::MARKETPLACE_ID,
                'product_id' => NULL,

                'general_id' => (int)$receivedItem['general_id'],
                'sku' => (string)$receivedItem['sku'],

                'title' => 'Unknown [Product Title cannot be received]',

                'online_price' => (float)$receivedItem['price'],
                'online_qty' => (int)$receivedItem['qty'],

                'condition' => (int)$receivedItem['condition'],
                'condition_note' => (string)$receivedItem['condition_note'],

                'shipping_standard_rate' => (float)$receivedItem['shipping_standard_rate'],
                'shipping_expedited_mode' => (int)$receivedItem['shipping_expedited_mode'],
                'shipping_expedited_rate' => (float)$receivedItem['shipping_expedited_rate']
            );

            if ((int)$newData['online_qty'] > 0) {
                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
            } else {
                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
            }

            $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

            $listingOtherModel = Mage::helper('M2ePro/Component_Buy')->getModel('Listing_Other');
            $listingOtherModel->setData($newData)->save();

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
            $mappingResult = $mappingModel->autoMapOtherListingProduct($listingOtherModel);

            if ($mappingResult) {

                if (!$this->getAccount()->getChildObject()->isOtherListingsMoveToListingsEnabled()) {
                    continue;
                }

                $movingModel->initialize($this->getAccount());
                $movingModel->autoMoveOtherListingProduct($listingOtherModel);
            }
        }
    }

    //########################################

    protected function filterReceivedOnlyOtherListings(array $receivedItems)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Product');
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns(array('second_table.sku'));

        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();

        $collection->getSelect()->join(array('l' => $listingTable), 'main_table.listing_id = l.id', array());
        $collection->getSelect()->where('l.account_id = ?',(int)$this->getAccount()->getId());

        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $connRead->query($collection->getSelect()->__toString());

        while ($existListingProduct = $stmtTemp->fetch()) {

            if (empty($existListingProduct['sku'])) {
                continue;
            }

            if (isset($receivedItems[$existListingProduct['sku']])) {
                unset($receivedItems[$existListingProduct['sku']]);
            }
        }

        return $receivedItems;
    }

    protected function getPdoStatementExistingListings($withData = false)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Other');
        $collection->getSelect()->where('`main_table`.`account_id` = ?',(int)$this->params['account_id']);

        $tempColumns = array('second_table.sku');

        if ($withData) {
            $tempColumns = array('main_table.status',
                                 'second_table.sku','second_table.general_id',
                                 'second_table.online_price','second_table.online_qty',
                                 'second_table.condition','second_table.condition_note',
                                 'second_table.shipping_standard_rate',
                                 'second_table.shipping_expedited_mode',
                                 'second_table.shipping_expedited_rate',
                                 'second_table.listing_other_id');
        }

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns($tempColumns);

        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $connRead->query($collection->getSelect()->__toString());

        return $stmtTemp;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account','account_id');
    }

    // ---------------------------------------

    protected function getLogsActionId()
    {
        if (!is_null($this->logsActionId)) {
            return $this->logsActionId;
        }

        return $this->logsActionId = Mage::getModel('M2ePro/Listing_Other_Log')->getNextActionId();
    }

    protected function getSynchronizationLog()
    {
        if (!is_null($this->synchronizationLog)) {
            return $this->synchronizationLog;
        }

        $this->synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Buy::NICK);
        $this->synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER_LISTINGS);

        return $this->synchronizationLog;
    }

    //########################################
}