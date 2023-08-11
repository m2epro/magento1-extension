<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Walmart_Order_ReceiveWithCancellationRequested
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'walmart/order/receive_with_cancellation_requested';

    const INTERVAL_TO_FIRST_CHECK_BUYER_CANCELLATION_REQUESTS = 259200; // 3 days
    const INTERVAL_TO_COMMON_CHECK_BUYER_CANCELLATION_REQUESTS = 86400; // 1 day
    const INTERVAL_FOR_ACCOUNT_SYNCHRONIZATION = 7200; // 2 hours

    const REGISTRY_PREFIX = '/walmart/order/receive_with_cancellation_requested/by_account/';
    const REGISTRY_SUFFIX = '/last_update/';

    /** @var Ess_M2ePro_Helper_Data */
    protected $_dataHelper;
    /** @var Ess_M2ePro_Helper_Server_Maintenance */
    protected $_serverMaintenanceHelper;
    /** @var Ess_M2ePro_Helper_Module_Logger */
    protected $_moduleLoggerHelper;
    /** @var Ess_M2ePro_Model_Registry_Manager */
    protected $_registryManager;
    /** @var Ess_M2ePro_Model_Synchronization_Log */
    protected $_synchronizationLog;
    /** @var bool */
    protected $_accountSynchronizationFail = false;

    public function __construct()
    {
        $this->_dataHelper = Mage::helper('M2ePro');
        $this->_serverMaintenanceHelper = Mage::helper('M2ePro/Server_Maintenance');
        $this->_moduleLoggerHelper = Mage::helper('M2ePro/Module_Logger');
        $this->_registryManager = Mage::getModel('M2ePro/Registry_Manager');
    }

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getSynchronizationLog()
    {
        if ($this->_synchronizationLog === null) {
            $this->_synchronizationLog = parent::getSynchronizationLog();

            $this->_synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Walmart::NICK);
            $this->_synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_ORDERS);
        }

        return $this->_synchronizationLog;
    }

    public function isPossibleToRun()
    {
        if ($this->_serverMaintenanceHelper->isNow()) {
            return false;
        }

        return parent::isPossibleToRun();
    }

    protected function performActions()
    {
        /** @var $accountsCollection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Account');

        /** @var $account Ess_M2ePro_Model_Account **/
        foreach ($accountsCollection->getItems() as $account) {
            try {
                $accountId = (int)$account->getId();
                if (!$this->isItemsReceiveRequired($accountId)) {
                    continue;
                }

                $this->_accountSynchronizationFail = false;
                $responseData = $this->receiveOrderItems($account, $this->getReceiveInterval($accountId));
                $this->processOrderItems($responseData);

                if (!$this->_accountSynchronizationFail) {
                    $this->updateLastReceiveDate($accountId);
                }
            } catch (Exception $exception) {
                $message = $this->_dataHelper->__(
                    'The "Receive Orders with Buyer Cancellation Requested" '
                    . 'Action for Walmart Account "%title%" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }
        }
    }

    /**
     * @param int $accountId
     *
     * @return string
     */
    protected function getRegistryKey($accountId)
    {
        return self::REGISTRY_PREFIX . $accountId . self::REGISTRY_SUFFIX;
    }

    /**
     * @param int $accountId
     * @return bool
     */
    protected function isItemsReceiveRequired($accountId)
    {
        $lastUpdate = $this->_registryManager->getValue($this->getRegistryKey($accountId));
        if (!$lastUpdate) {
            return true;
        }

        $now = $this->_dataHelper->createCurrentGmtDateTime();
        $lastUpdateDateTime = $this->_dataHelper->createGmtDateTime($lastUpdate);
        return $now->getTimestamp() - $lastUpdateDateTime->getTimestamp() > self::INTERVAL_FOR_ACCOUNT_SYNCHRONIZATION;
    }

    /**
     * @param int $accountId
     * @return void
     */
    protected function updateLastReceiveDate($accountId)
    {
        $now = $this->_dataHelper->createCurrentGmtDateTime();
        $this->_registryManager->setValue(
            $this->getRegistryKey($accountId),
            $now->format('Y-m-d H:i:s')
        );
    }

    /**
     * @param int $accountId
     *
     * @return int
     */
    protected function getReceiveInterval($accountId)
    {
        $lastUpdate = $this->_registryManager->getValue($this->getRegistryKey($accountId));

        return $lastUpdate ?
            self::INTERVAL_TO_COMMON_CHECK_BUYER_CANCELLATION_REQUESTS
            : self::INTERVAL_TO_FIRST_CHECK_BUYER_CANCELLATION_REQUESTS;
    }

    /**
     * @param Ess_M2ePro_Model_Account $account
     * @param int $interval
     * @return array|mixed
     * @throws Exception
     */
    protected function receiveOrderItems(Ess_M2ePro_Model_Account $account, $interval)
    {
        $fromDate = $this->_dataHelper->createCurrentGmtDateTime();
        $fromDate->modify("-$interval seconds");

        /** @var Ess_M2ePro_Model_Walmart_Connector_Dispatcher $dispatcherObject */
        $dispatcherObject = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getConnector(
            'orders',
            'get',
            'itemsCancellationRequested',
            array(
                'account' => $account->getChildObject()->getServerHash(),
                'from_create_date' => $fromDate->format('Y-m-d H:i:s'),
            )
        );
        $dispatcherObject->process($connectorObj);

        $this->processResponseMessages($connectorObj->getResponseMessages());

        return $connectorObj->getResponseData();
    }

    /**
     * @param array $items
     * @return void
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function processOrderItems(array $items)
    {
        foreach ($items as $item) {
            /** @var Ess_M2ePro_Model_Resource_Order_Item_Collection $collection */
            $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Order_Item');
            $collection
                ->addFieldToFilter('walmart_order_id', $item['walmart_order_id'])
                ->addFieldToFilter('sku', $item['sku']);
            $collection->getSelect()->join(
                array(
                    'wo' => Mage::getResourceModel('M2ePro/Walmart_Order')->getMainTable()
                ),
                'main_table.order_id=wo.order_id',
                array()
            );

            /** @var Ess_M2ePro_Model_Order_Item $existItem */
            $existItem = $collection->getFirstItem();

            if (!$existItem->getId()) {
                $this->_moduleLoggerHelper->process(
                    array(
                        'walmart_order_id' => $item['walmart_order_id'],
                        'sku' => $item['sku'],
                    ),
                    'Walmart orders receive with cancellation requested task - cannot find order item'
                );

                continue;
            }

            $cancellationRequestSavedPreviously = $existItem->getChildObject()
                ->isBuyerCancellationRequested();
            if ($cancellationRequestSavedPreviously) {
                continue;
            }

            $existItem->getChildObject()
                ->setData('buyer_cancellation_requested', 1)
                ->save();

            $this->notifyAboutBuyerCancellationRequested($existItem->getChildObject());
        }
    }

    /**
     * @param Ess_M2ePro_Model_Walmart_Order_Item $walmartOrderItem
     * @return void
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function notifyAboutBuyerCancellationRequested(Ess_M2ePro_Model_Walmart_Order_Item $walmartOrderItem)
    {
        $description = 'A buyer requested to cancel the item(s) "%item_name%" from the order #%order_number%.';

        $walmartOrder = $walmartOrderItem->getWalmartOrder();
        $walmartOrder->getParentObject()->addWarningLog(
            $description,
            array(
                '!order_number' => $walmartOrder->getWalmartOrderId(),
                '!item_name'  => $walmartOrderItem->getTitle()
            )
        );
    }

    protected function processResponseMessages(array $messages = array())
    {
        /** @var Ess_M2ePro_Model_Connector_Connection_Response_Message_Set $messagesSet */
        $messagesSet = Mage::getModel('M2ePro/Connector_Connection_Response_Message_Set');
        $messagesSet->init($messages);

        foreach ($messagesSet->getEntities() as $message) {
            if (!$message->isError() && !$message->isWarning()) {
                continue;
            }

            if ($message->isError()) {
                $this->_accountSynchronizationFail = true;
            }

            $logType = $message->isError() ? Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                : Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;

            $this->getSynchronizationLog()->addMessage(
                $this->_dataHelper->__($message->getText()),
                $logType
            );
        }
    }
}
