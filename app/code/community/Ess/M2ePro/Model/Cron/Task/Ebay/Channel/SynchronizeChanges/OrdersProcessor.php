<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Ebay_Channel_SynchronizeChanges_OrdersProcessor
{
    /** @var Ess_M2ePro_Model_Synchronization_Log */
    protected $_synchronizationLog = null;

    protected $_receiveOrdersToDate = null;

    //########################################

    public function setSynchronizationLog(Ess_M2ePro_Model_Synchronization_Log $log)
    {
        $this->_synchronizationLog = $log;
        return $this;
    }

    public function setReceiveOrdersToDate($toDate)
    {
        $this->_receiveOrdersToDate = $toDate;
        return $this;
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

    public function process()
    {
        $permittedAccounts = $this->getPermittedAccounts();
        if (empty($permittedAccounts)) {
            return;
        }

        foreach ($permittedAccounts as $account) {
            /** @var $account Ess_M2ePro_Model_Account **/

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

    //########################################

    protected function getPermittedAccounts()
    {
        /** @var $accountsCollection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
        return $accountsCollection->getItems();
    }

    //########################################

    protected function processAccount(Ess_M2ePro_Model_Account $account)
    {
        $ebayData = $this->receiveEbayOrdersData($account);
        /** @var Ess_M2ePro_Model_Ebay_Account $ebayAccount */
        $ebayAccount = $account->getChildObject();

        if (empty($ebayData)) {
            return NULL;
        }

        if (!empty($ebayData['job_token'])) {
            $ebayAccount->setData('job_token', $ebayData['job_token']);
        } else {
            $ebayAccount->setData('job_token', NULL);
        }

        $processedEbayOrders = $this->processEbayOrders($account, $ebayData['items']);

        // ---------------------------------------

        if (!empty($processedEbayOrders)) {
            $this->createMagentoOrders($processedEbayOrders);
        }

        $ebayAccount->setData('orders_last_synchronization', $ebayData['to_update_date']);
        $ebayAccount->save();
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Account $account
     * @return array
     */
    protected function receiveEbayOrdersData(Ess_M2ePro_Model_Account $account)
    {
        $toTime   = $this->prepareToTime();
        $fromTime = $this->prepareFromTime($account, $toTime);

        $params = array(
            'from_update_date' => Mage::helper('M2ePro/Component_Ebay')->timeToString($fromTime),
            'to_update_date'=> Mage::helper('M2ePro/Component_Ebay')->timeToString($toTime)
        );

        $jobToken = $account->getData('job_token');
        if (!empty($jobToken)) {
            $params['job_token'] = $jobToken;
        }

        /** @var Ess_M2ePro_Model_Connector_Command_RealTime $connectorObj */
        $dispatcherObj = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
        $connectorObj = $dispatcherObj->getCustomConnector(
            'Ebay_Connector_Order_Receive_Items', $params, NULL, $account
        );

        $dispatcherObj->process($connectorObj);
        $responseData = $connectorObj->getResponseData();

        $this->processResponseMessages($connectorObj->getResponseMessages());

        if (!isset($responseData['items']) || !isset($responseData['to_update_date'])) {
            $logData = array(
                'params'            => $params,
                'account_id'        => $account->getId(),
                'response_data'     => $responseData,
                'response_messages' => $connectorObj->getResponseMessages()
            );
            Mage::helper('M2ePro/Module_Logger')->process($logData, 'eBay orders receive task - empty response');

            return array();
        }

        return $responseData;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Account $account
     * @param array $ordersData
     * @return Ess_M2ePro_Model_Order[]
     */
    protected function processEbayOrders(Ess_M2ePro_Model_Account $account, array $ordersData)
    {
        $accountCreateDate = new DateTime($account->getData('create_date'), new DateTimeZone('UTC'));

        $orders = array();
        foreach ($ordersData as $ebayOrderData) {
            $orderCreateDate = new DateTime($ebayOrderData['purchase_create_date'], new DateTimeZone('UTC'));
            if ($orderCreateDate < $accountCreateDate) {
                continue;
            }

            /** @var $ebayOrder Ess_M2ePro_Model_Ebay_Order_Builder */
            $ebayOrder = Mage::getModel('M2ePro/Ebay_Order_Builder');
            $ebayOrder->initialize($account, $ebayOrderData);

            $orders[] = $ebayOrder->process();
        }

        return array_filter($orders);
    }

    // ---------------------------------------

    protected function processResponseMessages(array $messages)
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

            $this->_synchronizationLog->addMessage(
                Mage::helper('M2ePro')->__($message->getText()),
                $logType,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }
    }

    // ---------------------------------------

    protected function createMagentoOrders($ebayOrders)
    {
        $iteration = 0;

        foreach ($ebayOrders as $order) {
            /** @var $order Ess_M2ePro_Model_Order */

            $iteration++;

            if ($this->isOrderChangedInParallelProcess($order)) {
                continue;
            }

            if ($order->canCreateMagentoOrder()) {
                try {
                    $order->addNoticeLog(
                        'Magento order creation rules are met. M2E Pro will attempt to create Magento order.'
                    );
                    $order->createMagentoOrder();
                } catch (Exception $exception) {
                    continue;
                }
            }

            if ($order->getReserve()->isNotProcessed() && $order->isReservable()) {
                $order->getReserve()->place();
            }

            if ($order->getChildObject()->canCreatePaymentTransaction()) {
                $order->getChildObject()->createPaymentTransactions();
            }

            if ($order->getChildObject()->canCreateInvoice()) {
                $order->createInvoice();
            }

            if ($order->getChildObject()->canCreateShipment()) {
                $order->createShipment();
            }

            if ($order->getChildObject()->canCreateTracks()) {
                $order->getChildObject()->createTracks();
            }

            if ($order->getStatusUpdateRequired()) {
                $order->updateMagentoOrderStatus();
            }
        }
    }

    //########################################

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
     * @param Ess_M2ePro_Model_Account $account
     * @param DateTime $toTime
     * @return DateTime
     */
    protected function prepareFromTime(Ess_M2ePro_Model_Account $account, DateTime $toTime)
    {
        $lastSynchronizationDate = $account->getData('orders_last_synchronization');

        if ($lastSynchronizationDate === null) {
            $sinceTime = new DateTime('now', new DateTimeZone('UTC'));
        } else {
            $sinceTime = new DateTime($lastSynchronizationDate, new DateTimeZone('UTC'));

            // Get min date for synch
            // ---------------------------------------
            $minDate = new DateTime('now', new DateTimeZone('UTC'));
            $minDate->modify('-90 days');
            // ---------------------------------------

            // Prepare last date
            // ---------------------------------------
            if ((int)$sinceTime->format('U') < (int)$minDate->format('U')) {
                $sinceTime = $minDate;
            }

            // ---------------------------------------
        }

        if ($sinceTime->getTimestamp() >= $toTime->getTimeStamp()) {
            $sinceTime = clone $toTime;
            $sinceTime->modify('- 5 minutes');
        }

        return $sinceTime;
    }

    /**
     * @return DateTime
     */
    protected function prepareToTime()
    {
        if ($this->_receiveOrdersToDate !== null) {
            $toTime = new DateTime($this->_receiveOrdersToDate, new DateTimeZone('UTC'));
        } else {
            $toTime = new DateTime('now', new DateTimeZone('UTC'));
        }

        return $toTime;
    }

    //########################################
}
