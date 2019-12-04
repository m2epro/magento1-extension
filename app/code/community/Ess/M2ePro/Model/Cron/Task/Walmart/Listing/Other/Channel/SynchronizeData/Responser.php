<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Other_Channel_SynchronizeData_Responser
    extends Ess_M2ePro_Model_Walmart_Connector_Inventory_Get_ItemsResponser
{
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
        $receivedItems = $this->getReceivedOnlyOtherListings();

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

        while ($existingItem = $stmtTemp->fetch()) {
            if (!isset($receivedItems[$existingItem['wpid']])) {
                continue;
            }

            $receivedItem = $receivedItems[$existingItem['wpid']];

            $isOnlinePriceInvalid = in_array(
                Ess_M2ePro_Helper_Component_Walmart::PRODUCT_STATUS_CHANGE_REASON_INVALID_PRICE,
                $receivedItem['status_change_reason']
            );

            $newData = array(
                'upc'                   => !empty($receivedItem['upc']) ? (string)$receivedItem['upc'] : null,
                'gtin'                  => !empty($receivedItem['gtin']) ? (string)$receivedItem['gtin'] : null,
                'wpid'                  => (string)$receivedItem['wpid'],
                'item_id'               => (string)$receivedItem['item_id'],
                'sku'                   => (string)$receivedItem['sku'],
                'title'                 => (string)$receivedItem['title'],
                'online_price'          => (float)$receivedItem['price'],
                'online_qty'            => (int)$receivedItem['qty'],
                'channel_url'           => (string)$receivedItem['item_page_url'],
                'publish_status'        => (string)$receivedItem['publish_status'],
                'lifecycle_status'      => (string)$receivedItem['lifecycle_status'],
                'status_change_reasons' => Mage::helper('M2ePro')->jsonEncode($receivedItem['status_change_reason']),
                'is_online_price_invalid' => $isOnlinePriceInvalid,
            );

            $newData['status'] = Mage::helper('M2ePro/Component_Walmart')->getResultProductStatus(
                $receivedItem['publish_status'], $receivedItem['lifecycle_status'], $newData['online_qty']
            );

            $existingData = array(
                'upc'                   => !empty($existingItem['upc']) ? (string)$existingItem['upc'] : null,
                'gtin'                  => !empty($existingItem['gtin']) ? (string)$existingItem['gtin'] : null,
                'wpid'                  => (string)$existingItem['wpid'],
                'item_id'               => (string)$existingItem['item_id'],
                'sku'                   => (string)$existingItem['sku'],
                'title'                 => (string)$existingItem['title'],
                'online_price'          => (float)$existingItem['online_price'],
                'online_qty'            => (int)$existingItem['online_qty'],
                'channel_url'           => (string)$existingItem['channel_url'],
                'publish_status'        => (string)$existingItem['publish_status'],
                'lifecycle_status'      => (string)$existingItem['lifecycle_status'],
                'status_change_reasons' => (string)$existingItem['status_change_reasons'],
                'status'                => (int)$existingItem['status'],
                'is_online_price_invalid' => (bool)$existingItem['is_online_price_invalid'],
            );

            if ($newData == $existingData) {
                continue;
            }

            if ($newData['status'] != $existingData['status']) {
                $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;
            }

            $listingOtherObj = Mage::helper('M2ePro/Component_Walmart')
                                ->getObject('Listing_Other', (int)$existingItem['listing_other_id']);

            $listingOtherObj->addData($newData)->save();
        }
    }

    protected function createNotExistedOtherListings($receivedItems)
    {
        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $this->getPdoStatementExistingListings(false);

        while ($existingItem = $stmtTemp->fetch()) {
            if (!isset($receivedItems[$existingItem['wpid']])) {
                continue;
            }

            $receivedItems[$existingItem['wpid']]['founded'] = true;
        }

        /** @var $mappingModel Ess_M2ePro_Model_Walmart_Listing_Other_Mapping */
        $mappingModel = Mage::getModel('M2ePro/Walmart_Listing_Other_Mapping');

        foreach ($receivedItems as $receivedItem) {
            if (isset($receivedItem['founded'])) {
                continue;
            }

            $isOnlinePriceInvalid = in_array(
                Ess_M2ePro_Helper_Component_Walmart::PRODUCT_STATUS_CHANGE_REASON_INVALID_PRICE,
                $receivedItem['status_change_reason']
            );

            $newData = array(
                'account_id'     => $this->getAccount()->getId(),
                'marketplace_id' => $this->getMarketplace()->getId(),
                'product_id'     => null,

                'upc'     => !empty($receivedItem['upc']) ? (string)$receivedItem['upc'] : null,
                'gtin'    => !empty($receivedItem['gtin']) ? (string)$receivedItem['gtin'] : null,
                'wpid'    => (string)$receivedItem['wpid'],
                'item_id' => (string)$receivedItem['item_id'],

                'sku'   => (string)$receivedItem['sku'],
                'title' => $receivedItem['title'],

                'online_price' => (float)$receivedItem['price'],
                'online_qty'   => (int)$receivedItem['qty'],

                'channel_url'           => (string)$receivedItem['item_page_url'],
                'publish_status'        => (string)$receivedItem['publish_status'],
                'lifecycle_status'      => (string)$receivedItem['lifecycle_status'],
                'status_change_reasons' => Mage::helper('M2ePro')->jsonEncode($receivedItem['status_change_reason']),
                'is_online_price_invalid' => $isOnlinePriceInvalid,
            );

            $newData['status'] = Mage::helper('M2ePro/Component_Walmart')->getResultProductStatus(
                $receivedItem['publish_status'], $receivedItem['lifecycle_status'], $newData['online_qty']
            );

            $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

            $listingOtherModel = Mage::helper('M2ePro/Component_Walmart')->getModel('Listing_Other');
            $listingOtherModel->setData($newData)->save();

            if (!$this->getAccount()->getChildObject()->isOtherListingsMappingEnabled()) {
                continue;
            }

            $mappingModel->initialize($this->getAccount());
            $mappingModel->autoMapOtherListingProduct($listingOtherModel);
        }
    }

    //########################################

    protected function getReceivedOnlyOtherListings()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        /** @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns(array('second_table.wpid'));

        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();

        $collection->getSelect()->join(array('l' => $listingTable), 'main_table.listing_id = l.id', array());
        $collection->getSelect()->where('l.account_id = ?', (int)$this->getAccount()->getId());

        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $connRead->query($collection->getSelect()->__toString());

        $responseData = $this->getPreparedResponseData();
        $receivedItems = $responseData['data'];

        while ($existListingProduct = $stmtTemp->fetch()) {
            if (empty($existListingProduct['wpid'])) {
                continue;
            }

            if (isset($receivedItems[$existListingProduct['wpid']])) {
                unset($receivedItems[$existListingProduct['wpid']]);
            }
        }

        return $receivedItems;
    }

    protected function getPdoStatementExistingListings($withData = false)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        /** @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Other');
        $collection->getSelect()->where('`main_table`.`account_id` = ?', (int)$this->_params['account_id']);

        $tempColumns = array('second_table.wpid');

        if ($withData) {
            $tempColumns = array('main_table.status',
                                 'second_table.sku','second_table.title',
                                 'second_table.online_price','second_table.online_qty',
                                 'second_table.publish_status', 'second_table.lifecycle_status',
                                 'second_table.status_change_reasons', 'second_table.channel_url',
                                 'second_table.upc', 'second_table.gtin', 'second_table.ean', 'second_table.wpid',
                                 'second_table.item_id', 'second_table.listing_other_id',
                                 'second_table.is_online_price_invalid');
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

    protected function getSynchronizationLog()
    {
        if ($this->_synchronizationLog !== null) {
            return $this->_synchronizationLog;
        }

        $this->_synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->_synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Walmart::NICK);
        $this->_synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER_LISTINGS);

        return $this->_synchronizationLog;
    }

    //########################################
}
