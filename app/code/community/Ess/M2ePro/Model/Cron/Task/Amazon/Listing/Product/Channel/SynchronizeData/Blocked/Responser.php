<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_Blocked_Responser
    extends Ess_M2ePro_Model_Amazon_Connector_Inventory_Get_Blocked_ItemsResponser
{
    protected $_logsActionId       = null;
    protected $_synchronizationLog = null;

    //########################################

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

    //########################################

    public function failDetected($messageText)
    {
        parent::failDetected($messageText);

        $this->getSynchronizationLog()->addMessage(
            Mage::helper('M2ePro')->__($messageText),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
        );
    }

    //########################################

    protected function processResponseData()
    {
        try {
            $this->updateBlockedListingProducts();
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

    protected function updateBlockedListingProducts()
    {
        $responseData = $this->getPreparedResponseData();

        if (empty($responseData['data'])) {
            return false;
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $connRead->query($this->getPdoStatementExistingListings());

        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);

        $notReceivedIds = array();
        while ($existingItem = $stmtTemp->fetch()) {
            if (in_array($existingItem['sku'], $responseData['data'])) {
                continue;
            }

            $notReceivedItem = $existingItem;

            $additionalData = @Mage::helper('M2ePro')->jsonDecode($notReceivedItem['additional_data']);
            if (is_array($additionalData) && !empty($additionalData['list_date']) &&
                $this->isProductInfoOutdated($additionalData['list_date'])
            ) {
                continue;
            }

            if (!in_array((int)$notReceivedItem['id'], $notReceivedIds)) {
                $statusChangedFrom = Mage::helper('M2ePro/Component_Amazon')
                    ->getHumanTitleByListingProductStatus($notReceivedItem['status']);
                $statusChangedTo = Mage::helper('M2ePro/Component_Amazon')
                    ->getHumanTitleByListingProductStatus(Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED);

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

        if (empty($notReceivedIds)) {
            $this->updateLastListingProductsSynchronization();
        }

        $bind = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED,
            'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT
        );

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $listingProductMainTable = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();

        $chunckedIds = array_chunk($notReceivedIds, 1000);
        foreach ($chunckedIds as $partIds) {
            $where = '`id` IN ('.implode(',', $partIds).')';
            $connWrite->update($listingProductMainTable, $bind, $where);
        }

        if (!empty($parentIdsForProcessing)) {
            $this->processParentProcessors($parentIdsForProcessing);
        }
    }

    protected function getPdoStatementExistingListings()
    {
        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();

        /** @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->getSelect()->join(array('l' => $listingTable), 'main_table.listing_id = l.id', array());
        $collection->getSelect()->where('l.account_id = ?', (int)$this->getAccount()->getId());
        $collection->getSelect()->where('second_table.is_variation_parent != ?', 1);
        $collection->getSelect()->where(
            '`main_table`.`status` != ?',
            (int)Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED
        );
        $collection->getSelect()->where(
            '`main_table`.`status` != ?',
            (int)Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED
        );

        $tempColumns = array('main_table.id','main_table.status','main_table.listing_id',
            'main_table.product_id','main_table.additional_data',
            'second_table.sku', 'second_table.is_variation_product','second_table.variation_parent_id');
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns($tempColumns);

        return $collection->getSelect()->__toString();
    }

    //########################################

    protected function processParentProcessors(array $parentIds)
    {
        if (empty($parentIds)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $parentListingProductCollection */
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

    protected function updateLastListingProductsSynchronization()
    {
        $additionalData = Mage::helper('M2ePro')->jsonDecode($this->getAccount()->getAdditionalData());
        $lastSynchData = array(
            'last_listing_products_synchronization' => Mage::helper('M2ePro')->getCurrentGmtDate()
        );

        if (!empty($additionalData)) {
            $additionalData = array_merge($additionalData, $lastSynchData);
        } else {
            $additionalData = $lastSynchData;
        }

        $this->getAccount()
             ->setAdditionalData(Mage::helper('M2ePro')->jsonEncode($additionalData))
             ->save();
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

    protected function isProductInfoOutdated($lastDate)
    {
        $lastDate = new DateTime($lastDate, new DateTimeZone('UTC'));
        $requestDate = new DateTime($this->_params['request_date'], new DateTimeZone('UTC'));

        $lastDate->modify('+1 hour');

        return $lastDate > $requestDate;
    }

    //########################################
}
