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
        $permittedAccounts = $this->getPermittedAccounts();
        if (empty($permittedAccounts)) {
            return;
        }

        foreach ($permittedAccounts as $account) {
            /** @var $account Ess_M2ePro_Model_Account **/

            $this->getOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');
            $this->getOperationHistory()->addTimePoint(
                __METHOD__.'process'.$account->getTitle(), 'Get Orders from Walmart '.$account->getTitle()
            );
            // ---------------------------------------

            try {
                $responseData = $this->receiveWalmartOrdersData($account);
                if (empty($responseData)) {
                    continue;
                }

                $this->getOperationHistory()->addTimePoint(
                    __METHOD__.'create_magento_orders'.$account->getTitle(), 'Create Magento Orders'
                );

                $processedWalmartOrders = array();

                try {
                    $accountCreateDate = new DateTime($account->getData('create_date'), new DateTimeZone('UTC'));
                    foreach ($responseData['items'] as $orderData) {
                        $orderCreateDate = new DateTime($orderData['purchase_date'], new DateTimeZone('UTC'));
                        if ($orderCreateDate < $accountCreateDate) {
                            continue;
                        }

                        /** @var $orderBuilder Ess_M2ePro_Model_Walmart_Order_Builder */
                        $orderBuilder = Mage::getModel('M2ePro/Walmart_Order_Builder');
                        $orderBuilder->initialize($account, $orderData);

                        $order = $orderBuilder->process();

                        if (!$order) {
                            continue;
                        }

                        $processedWalmartOrders[] = $order;
                    }
                } catch (Exception $exception) {
                    $this->getSynchronizationLog()->addMessage(
                        Mage::helper('M2ePro')->__($exception->getMessage()),
                        Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                        Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
                    );

                    Mage::helper('M2ePro/Module_Exception')->process($exception);
                }

                foreach ($processedWalmartOrders as $walmartOrder) {
                    try {
                        /** @var $walmartOrder Ess_M2ePro_Model_Order */

                        if ($this->isOrderChangedInParallelProcess($walmartOrder)) {
                            continue;
                        }

                        $walmartOrder->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);

                        if ($walmartOrder->canCreateMagentoOrder()) {
                            try {
                                $message = 'Magento order creation rules are met.';
                                $message .= ' M2E Pro will attempt to create Magento order.';

                                $walmartOrder->addNoticeLog($message);
                                $walmartOrder->createMagentoOrder();
                            } catch (Exception $exception) {
                                continue;
                            }
                        }

                        if ($walmartOrder->getReserve()->isNotProcessed() && $walmartOrder->isReservable()) {
                            $walmartOrder->getReserve()->place();
                        }

                        if ($walmartOrder->getChildObject()->canCreateInvoice()) {
                            $walmartOrder->createInvoice();
                        }

                        if ($walmartOrder->getChildObject()->canCreateShipment()) {
                            $walmartOrder->createShipment();
                        }

                        if ($walmartOrder->getStatusUpdateRequired()) {
                            $walmartOrder->updateMagentoOrderStatus();
                        }
                    } catch (Exception $exception) {
                        $this->getSynchronizationLog()->addMessage(
                            Mage::helper('M2ePro')->__($exception->getMessage()),
                            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
                        );

                        Mage::helper('M2ePro/Module_Exception')->process($exception);
                    }
                }

                // ---------------------------------------

                $account->getChildObject()->setData('orders_last_synchronization', $responseData['to_create_date']);
                $account->getChildObject()->save();
            } catch (Exception $exception) {
                $message = Mage::helper('M2ePro')->__(
                    'The "Receive" Action for Walmart Account "%title%" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }

            $this->getOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getTitle());
        }
    }

    //########################################

    protected function getPermittedAccounts()
    {
        /** @var $accountsCollection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Account');
        return $accountsCollection->getItems();
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Account $account
     * @return array|null
     * @throws Exception
     */
    protected function receiveWalmartOrdersData(Ess_M2ePro_Model_Account $account)
    {

        $fromDate = $this->prepareFromDate($account->getData('orders_last_synchronization'));
        $toDate = $this->prepareToDate();

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
                'orders', 'get', 'items',
                array(
                    'account'          => $account->getData('server_hash'),
                    'from_create_date' => $fromDate->format('Y-m-d H:i:s'),
                    'to_create_date'   => $toDate->format('Y-m-d H:i:s')
                )
            );
            $dispatcherObject->process($connectorObj);

            // ----------------------------------------

            $this->processResponseMessages($connectorObj->getResponseMessages());
            $this->getOperationHistory()->saveTimePoint(__METHOD__ . 'get' . $account->getTitle());

            // ----------------------------------------

            $responseData = $connectorObj->getResponseData();
            if (!isset($responseData['items']) || !isset($responseData['to_create_date'])) {
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

            if (Mage::helper('M2ePro/Module')->isTestingManualEnvironment()) {
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
                $logType,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }
    }

    // ---------------------------------------

    /**
     * This is going to protect from Magento Orders duplicates.
     * (Is assuming that there may be a parallel process that has already created Magento Order)
     *
     * But this protection is not covering a cases when two parallel cron processes are isolated by mysql transactions
     */
    protected function isOrderChangedInParallelProcess(Ess_M2ePro_Model_Order $order)
    {
        /** @var Ess_M2ePro_Model_Order $dbOrder */
        $dbOrder = Mage::getModel('M2ePro/Order')->load($order->getId());

        if ($dbOrder->getMagentoOrderId() != $order->getMagentoOrderId()) {
            return true;
        }

        return false;
    }

    //########################################

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
     * @return DateTime
     * @throws Exception
     */
    protected function prepareToDate()
    {
        $operationHistory = $this->getOperationHistory()->getParentObject('synchronization');
        $toDate = $operationHistory !== null ? $operationHistory->getData('start_date') : 'now';

        return new DateTime($toDate, new DateTimeZone('UTC'));
    }

    //########################################
}
