<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Listing_Product as WalmartProduct;

class Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_Channel_SynchronizeData_Responser
    extends Ess_M2ePro_Model_Walmart_Connector_Inventory_Get_ItemsResponser
{
    const INSTRUCTION_INITIATOR = 'channel_changes_synchronization';

    protected $_logsActionId       = null;
    protected $_synchronizationLog = null;

    // ########################################

    protected function processResponseMessages()
    {
        parent::processResponseMessages();

        foreach ($this->getResponse()->getMessages()->getEntities() as $message) {
            if (!$message->isError() && !$message->isWarning()) {
                continue;
            }

            $logType = $message->isError() ? Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                : Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;

            $this->getSynchronizationLog()->addMessage(
                Mage::helper('M2ePro')->__($message->getText()),
                $logType,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }
    }

    protected function isNeedProcessResponse()
    {
        if (!parent::isNeedProcessResponse()) {
            return false;
        }

        if ($this->getResponse()->getMessages()->hasErrorEntities()) {
            return false;
        }

        return true;
    }

    // ########################################

    public function failDetected($messageText)
    {
        parent::failDetected($messageText);

        $this->getSynchronizationLog()->addMessage(
            Mage::helper('M2ePro')->__($messageText),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
        );
    }

    // ########################################

    protected function processResponseData()
    {
        try {
            $this->updateReceivedListingsProducts();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->getSynchronizationLog()->addMessage(
                Mage::helper('M2ePro')->__($exception->getMessage()),
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }
    }

    // ########################################

    protected function updateReceivedListingsProducts()
    {
        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $this->getPdoStatementExistingListings(true);

        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode(Ess_M2ePro_Helper_Component_Walmart::NICK);

        $responseData = $this->getPreparedResponseData();

        $parentIdsForProcessing = array();

        $instructionsData = array();

        while ($existingItem = $stmtTemp->fetch()) {
            if (!isset($responseData['data'][$existingItem['wpid']])) {
                continue;
            }

            $receivedItem = $responseData['data'][$existingItem['wpid']];

            $isOnlinePriceInvalid = in_array(
                Ess_M2ePro_Helper_Component_Walmart::PRODUCT_STATUS_CHANGE_REASON_INVALID_PRICE,
                $receivedItem['status_change_reason']
            );

            $newData = array(
                'upc'                     => !empty($receivedItem['upc']) ? (string)$receivedItem['upc'] : NULL,
                'gtin'                    => !empty($receivedItem['gtin']) ? (string)$receivedItem['gtin'] : NULL,
                'wpid'                    => (string)$receivedItem['wpid'],
                'item_id'                 => (string)$receivedItem['item_id'],
                'online_qty'              => (int)$receivedItem['qty'],
                'channel_url'             => (string)$receivedItem['item_page_url'],
                'publish_status'          => (string)$receivedItem['publish_status'],
                'lifecycle_status'        => (string)$receivedItem['lifecycle_status'],
                'status_change_reasons'   => Mage::helper('M2ePro')->jsonEncode($receivedItem['status_change_reason']),
                'is_online_price_invalid' => $isOnlinePriceInvalid,
                'is_missed_on_channel'    => false,
            );

            $newData['status'] = Mage::helper('M2ePro/Component_Walmart')->getResultProductStatus(
                $receivedItem['publish_status'], $receivedItem['lifecycle_status'], $newData['online_qty']
            );

            $existingData = array(
                'upc'                     => !empty($existingItem['upc']) ? (string)$existingItem['upc'] : NULL,
                'gtin'                    => !empty($existingItem['gtin']) ? (string)$existingItem['gtin'] : NULL,
                'wpid'                    => (string)$existingItem['wpid'],
                'item_id'                 => (string)$existingItem['item_id'],
                'online_qty'              => (int)$existingItem['online_qty'],
                'status'                  => (int)$existingItem['status'],
                'channel_url'             => (string)$existingItem['channel_url'],
                'publish_status'          => (string)$existingItem['publish_status'],
                'lifecycle_status'        => (string)$existingItem['lifecycle_status'],
                'status_change_reasons'   => (string)$existingItem['status_change_reasons'],
                'is_online_price_invalid' => (bool)$existingItem['is_online_price_invalid'],
                'is_missed_on_channel'    => (bool)$existingItem['is_missed_on_channel'],
            );

            $existingAdditionalData = Mage::helper('M2ePro')->jsonDecode($existingItem['additional_data']);

            if (!empty($existingAdditionalData['last_synchronization_dates']['qty']) &&
                !empty($receivedItem['actual_on_date'])
            ) {
                $lastQtySynchDate = $existingAdditionalData['last_synchronization_dates']['qty'];

                if ($this->isProductInfoOutdated($lastQtySynchDate, $receivedItem['actual_on_date'])) {
                    unset(
                        $newData['online_qty'], $newData['status'],
                        $newData['lifecycle_status'], $newData['publish_status']
                    );
                    unset(
                        $existingData['online_qty'], $existingData['status'],
                        $existingData['lifecycle_status'], $existingData['publish_status']
                    );
                }
            }

            if (!empty($existingAdditionalData['last_synchronization_dates']['price']) &&
                !empty($receivedItem['actual_on_date'])
            ) {
                $lastPriceSynchDate = $existingAdditionalData['last_synchronization_dates']['price'];

                if ($this->isProductInfoOutdated($lastPriceSynchDate, $receivedItem['actual_on_date'])) {
                    unset(
                        $newData['status'], $newData['lifecycle_status'],
                        $newData['publish_status'], $newData['is_online_price_invalid']
                    );
                    unset(
                        $existingData['status'], $existingData['lifecycle_status'],
                        $existingData['publish_status'], $existingData['is_online_price_invalid']
                    );
                }
            }

            if ($newData == $existingData) {
                continue;
            }

            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = Mage::helper('M2ePro/Component_Walmart')
                ->getObject('Listing_Product', (int)$existingItem['listing_product_id']);

            if ($this->isDataChanged($existingData, $newData, 'status')) {
                $instructionsData[] = array(
                    'listing_product_id' => $listingProduct->getId(),
                    'type'               => WalmartProduct::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
                    'initiator'          => self::INSTRUCTION_INITIATOR,
                    'priority'           => 80,
                );

                if (!empty($existingItem['is_variation_product']) && !empty($existingItem['variation_parent_id'])) {
                    $parentIdsForProcessing[] = (int)$existingItem['variation_parent_id'];
                }
            }

            if ($this->isDataChanged($existingData, $newData, 'online_qty')) {
                $instructionsData[] = array(
                    'listing_product_id' => $listingProduct->getId(),
                    'type'               => WalmartProduct::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
                    'initiator'          => self::INSTRUCTION_INITIATOR,
                    'priority'           => 80,
                );

                if (!empty($existingItem['is_variation_product']) && !empty($existingItem['variation_parent_id'])) {
                    $parentIdsForProcessing[] = (int)$existingItem['variation_parent_id'];
                }
            }

            $tempLogMessages = array();

            if (isset($newData['online_qty']) && $newData['online_qty'] != $existingData['online_qty']) {
                // M2ePro_TRANSLATIONS
                // Item QTY was successfully changed from %from% to %to% .
                $tempLogMessages[] = Mage::helper('M2ePro')->__(
                    'Item QTY was successfully changed from %from% to %to% .',
                    (int)$existingData['online_qty'],
                    (int)$newData['online_qty']
                );
            }

            if (isset($newData['status']) && $newData['status'] != $existingData['status']) {
                $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

                $statusChangedFrom = Mage::helper('M2ePro/Component_Walmart')
                    ->getHumanTitleByListingProductStatus($existingData['status']);
                $statusChangedTo = Mage::helper('M2ePro/Component_Walmart')
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

            $listingProduct->addData($newData)->save();
        }

        Mage::getResourceModel('M2ePro/Listing_Product_Instruction')->add($instructionsData);

        if (!empty($parentIdsForProcessing)) {
            $this->processParentProcessors($parentIdsForProcessing);
        }
    }

    // ########################################

    protected function getPdoStatementExistingListings($withData = false)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();

        /** @var $collection Varien_Data_Collection_Db */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $collection->getSelect()->join(array('l' => $listingTable), 'main_table.listing_id = l.id', array());

        $collection->getSelect()->where('l.account_id = ?', (int)$this->getAccount()->getId());
        $collection->getSelect()->where(
            '`main_table`.`status` != ?',
            (int)Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED
        );
        $collection->getSelect()->where("`second_table`.`wpid` is not null and `second_table`.`wpid` != ''");
        $collection->getSelect()->where("`second_table`.`is_variation_parent` != ?", 1);

        $tempColumns = array('second_table.wpid');

        if ($withData) {
            $tempColumns = array(
                'main_table.listing_id',
                'main_table.product_id',
                'main_table.status',
                'main_table.additional_data',
                'second_table.sku',
                'second_table.upc',
                'second_table.ean',
                'second_table.gtin',
                'second_table.wpid',
                'second_table.item_id',
                'second_table.online_qty',
                'second_table.listing_product_id',
                'second_table.is_variation_product',
                'second_table.variation_parent_id',
                'second_table.is_online_price_invalid',
                'second_table.channel_url',
                'second_table.publish_status',
                'second_table.lifecycle_status',
                'second_table.status_change_reasons',
                'second_table.is_missed_on_channel',
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

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $parentListingProductCollection */
        $parentListingProductCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $parentListingProductCollection->addFieldToFilter('id', array('in' => array_unique($parentIds)));

        $parentListingsProducts = $parentListingProductCollection->getItems();
        if (empty($parentListingsProducts)) {
            return;
        }

        $massProcessor = Mage::getModel(
            'M2ePro/Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Mass'
        );
        $massProcessor->setListingsProducts($parentListingsProducts);
        $massProcessor->setForceExecuting(false);

        $massProcessor->execute();
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account', 'account_id');
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getAccount()->getChildObject()->getMarketplace();
    }

    //-----------------------------------------

    protected function getLogsActionId()
    {
        if ($this->_logsActionId !== null) {
            return $this->_logsActionId;
        }

        return $this->_logsActionId = Mage::getModel('M2ePro/Listing_Log')->getResource()->getNextActionId();
    }

    protected function getSynchronizationLog()
    {
        if ($this->_synchronizationLog !== null) {
            return $this->_synchronizationLog;
        }

        $this->_synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->_synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
        $this->_synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_LISTINGS);

        return $this->_synchronizationLog;
    }

    //-----------------------------------------

    protected function isProductInfoOutdated($lastDate, $actualOnDate)
    {
        $lastDate = new DateTime($lastDate, new DateTimeZone('UTC'));
        $actualOnDate = new DateTime($actualOnDate, new DateTimeZone('UTC'));

        $lastDate->modify('+1 hour');

        return $lastDate > $actualOnDate;
    }

    protected function isDataChanged($existData, $newData, $key)
    {
        if (!isset($existData[$key]) || !isset($newData[$key])) {
            return false;
        }

        return $existData[$key] != $newData[$key];
    }

    // ########################################
}
