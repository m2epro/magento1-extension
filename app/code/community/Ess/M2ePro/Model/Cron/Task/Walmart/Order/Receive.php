<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Walmart_Order_Receive
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'walmart/order/receive';

    //####################################

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getSynchronizationLog()
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Walmart::NICK);
        $synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_ORDERS);

        return $synchronizationLog;
    }

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
        /** @var $accountsCollection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Account');

        /** @var Ess_M2ePro_Model_Cron_Task_Walmart_Order_Creator $ordersCreator */
        $ordersCreator = Mage::getModel('M2ePro/Cron_Task_Walmart_Order_Creator');
        $ordersCreator->setSynchronizationLog($this->getSynchronizationLog());

        foreach ($accountsCollection->getItems() as $account) {
            /** @var $account Ess_M2ePro_Model_Account **/

            try {
                if ($this->isCanada($account)) {
                    $responseData = $this->receiveWalmartOrdersDataByCreateDate($account);
                    $lastSynchronizationDate = $responseData['to_create_date'];
                } else {
                    $responseData = $this->receiveWalmartOrdersDataByUpdateDate($account);
                    $lastSynchronizationDate = $responseData['to_update_date'];
                }
                if (empty($responseData)) {
                    continue;
                }

                $processedWalmartOrders = $ordersCreator->processWalmartOrders($account, $responseData['items']);
                $ordersCreator->processMagentoOrders($processedWalmartOrders);

                $account->getChildObject()->setData('orders_last_synchronization', $lastSynchronizationDate);
                $account->getChildObject()->save();
            } catch (Exception $exception) {
                $message = Mage::helper('M2ePro')->__(
                    'The "Receive" Action for Walmart Account "%title%" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }
        }
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Account $account
     * @return array|null
     * @throws Exception
     */
    protected function receiveWalmartOrdersDataByUpdateDate(Ess_M2ePro_Model_Account $account)
    {
        $lastFromDate = $account->getData('orders_last_synchronization');
        if (!empty($lastFromDate)) {
            $fromDate = new DateTime($lastFromDate, new DateTimeZone('UTC'));
        } else {
            $fromDate = new DateTime('now', new DateTimeZone('UTC'));
            $fromDate->modify('-1 day');
        }

        $toDate = new DateTime('now', new DateTimeZone('UTC'));

        // ----------------------------------------

        if ($fromDate >= $toDate) {
            $fromDate = clone $toDate;
            $fromDate->modify('-5 minutes');
        }

        // ----------------------------------------

        /** @var Ess_M2ePro_Model_Walmart_Connector_Dispatcher $dispatcherObject */
        $dispatcherObject = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');

        // -------------------------------------

        $connectorObj = $dispatcherObject->getVirtualConnector(
            'orders',
            'get',
            'items',
            array(
                'account'          => $account->getData('server_hash'),
                'from_update_date' => $fromDate->format('Y-m-d H:i:s'),
                'to_update_date'   => $toDate->format('Y-m-d H:i:s')
            )
        );
        $dispatcherObject->process($connectorObj);

        // ----------------------------------------

        $this->processResponseMessages($connectorObj->getResponseMessages());

        // ----------------------------------------

        $responseData = $connectorObj->getResponseData();
        if (!isset($responseData['items'])) {
            Mage::helper('M2ePro/Module_Logger')->process(
                array(
                    'from_update_date'  => $fromDate->format('Y-m-d H:i:s'),
                    'to_update_date'    => $toDate->format('Y-m-d H:i:s'),
                    'account_id'        => $account->getId(),
                    'response_data'     => $responseData,
                    'response_messages' => $connectorObj->getResponseMessages()
                ),
                'Walmart orders receive task - empty response'
            );

            return array();
        }

        // ----------------------------------------

        return array(
            'items'          => $responseData['items'],
            'to_create_date' => isset($responseData['to_create_date'])
                ? $responseData['to_create_date']
                : $toDate->format('Y-m-d H:i:s'),
            'to_update_date' => count($responseData['items']) > 0
                ? $responseData['to_update_date']
                : $toDate->format('Y-m-d H:i:s'),
        );
    }

    /**
     * @param Ess_M2ePro_Model_Account $account
     * @return array|null
     * @throws Exception
     */
    private function receiveWalmartOrdersDataByCreateDate(Ess_M2ePro_Model_Account $account)
    {
        $fromDate = $this->prepareFromDate($account->getData('orders_last_synchronization'));
        $toDate = new DateTime('now', new DateTimeZone('UTC'));

        // ----------------------------------------

        if ($fromDate >= $toDate) {
            $fromDate = clone $toDate;
            $fromDate->modify('-5 minutes');
        }

        // ----------------------------------------

        /** @var Ess_M2ePro_Model_Walmart_Connector_Dispatcher $dispatcherObject */
        $dispatcherObject = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');
        $orders = array(array());
        $breakDate = null;

        // -------------------------------------

        do {
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'orders',
                'get',
                'items',
                array(
                    'account'          => $account->getData('server_hash'),
                    'from_create_date' => $fromDate->format('Y-m-d H:i:s'),
                    'to_create_date'   => $toDate->format('Y-m-d H:i:s')
                )
            );
            $dispatcherObject->process($connectorObj);

            // ----------------------------------------

            $this->processResponseMessages($connectorObj->getResponseMessages());

            // ----------------------------------------

            $responseData = $connectorObj->getResponseData();
            if (!isset($responseData['items'])) {
                Mage::helper('M2ePro/Module_Logger')->process(
                    array(
                        'from_create_date'  => $fromDate->format('Y-m-d H:i:s'),
                        'to_create_date'    => $toDate->format('Y-m-d H:i:s'),
                        'account_id'        => $account->getId(),
                        'response_data'     => $responseData,
                        'response_messages' => $connectorObj->getResponseMessages()
                    ),
                    'Walmart orders receive task - empty response'
                );

                return array();
            }

            // ----------------------------------------

            $fromDate = new DateTime($responseData['to_create_date'], new DateTimeZone('UTC'));
            if ($breakDate !== null && $breakDate->getTimestamp() === $fromDate->getTimestamp()) {
                break;
            }

            $orders[] = $responseData['items'];
            $breakDate = $fromDate;

            if ($fromDate > $toDate) {
                break;
            }
        } while (!empty($responseData['items']));

        // ----------------------------------------

        return array(
            'items'          => call_user_func_array('array_merge', $orders),
            'to_create_date' => $responseData['to_create_date']
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

            $logType = $message->isError() ? Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                                           : Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;

            $this->getSynchronizationLog()->addMessage(
                Mage::helper('M2ePro')->__($message->getText()),
                $logType
            );
        }
    }

    //####################################

    /**
     * @param mixed $lastFromDate
     * @return DateTime
     * @throws Exception
     */
    protected function prepareFromDate($lastFromDate)
    {
        $nowDateTime = new DateTime('now', new DateTimeZone('UTC'));

        // ----------------------------------------

        if (!empty($lastFromDate)) {
            $lastFromDate = new DateTime($lastFromDate, new DateTimeZone('UTC'));
        }

        if (empty($lastFromDate)) {
            $lastFromDate = clone $nowDateTime;
        }

        // ----------------------------------------

        $minDateTime = clone $nowDateTime;
        $minDateTime->modify('-1 day');

        if ($lastFromDate > $minDateTime) {
            $minPurchaseDateTime = $this->getMinPurchaseDateTime($minDateTime);
            if ($minPurchaseDateTime !== null) {
                $lastFromDate = $minPurchaseDateTime;
            }
        }

        // ----------------------------------------

        $minDateTime = clone $nowDateTime;
        $minDateTime->modify('-30 days');

        if ((int)$lastFromDate->format('U') < (int)$minDateTime->format('U')) {
            $lastFromDate = $minDateTime;
        }

        // ---------------------------------------

        return $lastFromDate;
    }

    /**
     * @param DateTime $minPurchaseDateTime
     * @return DateTime|null
     * @throws Exception
     */
    protected function getMinPurchaseDateTime(DateTime $minPurchaseDateTime)
    {
        /** @var Ess_M2ePro_Model_Resource_Order_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Order');
        $collection->addFieldToFilter(
            'status',
            array(
                'from' => Ess_M2ePro_Model_Walmart_Order::STATUS_CREATED,
                'to'   => Ess_M2ePro_Model_Walmart_Order::STATUS_SHIPPED_PARTIALLY
            )
        );
        $collection->addFieldToFilter(
            'purchase_create_date',
            array('from' => $minPurchaseDateTime->format('Y-m-d H:i:s'))
        );
        $collection->getSelect()->limit(1);

        /** @var Ess_M2ePro_Model_Order $order */
        $order = $collection->getFirstItem();
        if ($order->getId() === null) {
            return null;
        }

        $purchaseDateTime = new DateTime(
            $order->getChildObject()->getPurchaseCreateDate(),
            new DateTimeZone('UTC')
        );
        $purchaseDateTime->modify('-1 second');

        return $purchaseDateTime;
    }

    private function isCanada(Ess_M2ePro_Model_Account $account)
    {
        return $account->getChildObject()->getMarketplace()->getCode() === 'CA';
    }
}
