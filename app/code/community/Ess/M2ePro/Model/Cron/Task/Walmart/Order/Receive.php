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

                $preparedResponseData = $this->receiveWalmartOrdersData($account);

                if (empty($preparedResponseData)) {
                    continue;
                }

                $this->getOperationHistory()->addTimePoint(
                    __METHOD__.'create_magento_orders'.$account->getTitle(), 'Create Magento Orders'
                );

                $processedWalmartOrders = array();

                try {

                    $accountCreateDate = new DateTime($account->getData('create_date'), new DateTimeZone('UTC'));

                    foreach ($preparedResponseData['items'] as $orderData) {

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

                        $iteration = 0;

                        /** @var $walmartOrder Ess_M2ePro_Model_Order */

                        if ($this->isOrderChangedInParallelProcess($walmartOrder)) {
                            continue;
                        }

                        $iteration++;

                        if ($iteration % 5 == 0) {
                            $this->getLockItemManager()->activate();
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

            } catch (Exception $exception) {

                $message = Mage::helper('M2ePro')->__(
                    'The "Receive" Action for Walmart Account "%title%" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }

            // ---------------------------------------
            $this->getOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getTitle());

            $this->getLockItemManager()->activate();
            // ---------------------------------------
        }
    }

    //########################################

    private function getPermittedAccounts()
    {
        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Account');
        return $accountsCollection->getItems();
    }

    // ---------------------------------------

    private function receiveWalmartOrdersData(Ess_M2ePro_Model_Account $account)
    {
        $createSinceTime = $account->getData('orders_last_synchronization');

        $fromDate = $this->prepareFromDate($createSinceTime);
        $toDate   = $this->prepareToDate();

        if (strtotime($fromDate) >= strtotime($toDate)) {
            $fromDate = new DateTime($toDate, new DateTimeZone('UTC'));
            $fromDate->modify('- 5 minutes');

            $fromDate = $fromDate->format('Y-m-d H:i:s');
        }

        $requestData = array(
            'account'          => $account->getData('server_hash'),
            'from_create_date' => $fromDate,
            'to_create_date'   => $toDate
        );

        /** @var Ess_M2ePro_Model_Connector_Command_RealTime $connectorObj */
        $dispatcherObject = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector(
            'orders', 'get', 'items', $requestData
        );

        $dispatcherObject->process($connectorObj);
        $responseData = $connectorObj->getResponseData();

        $this->processResponseMessages($connectorObj->getResponseMessages());
        $this->getOperationHistory()->saveTimePoint(__METHOD__.'get'.$account->getTitle());

        if (!isset($responseData['items']) || !isset($responseData['to_create_date'])) {
            $logData = array(
                'from_create_date'  => $fromDate,
                'to_create_date'    => $toDate,
                'account_id'        => $account->getId(),
                'response_data'     => $responseData,
                'response_messages' => $connectorObj->getResponseMessages()
            );
            Mage::helper('M2ePro/Module_Logger')->process($logData, 'Walmart orders receive task - empty response');

            return array();
        } else {
            $account->setData('orders_last_synchronization', $responseData['to_create_date'])->save();
        }

        return $responseData;
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
    private function isOrderChangedInParallelProcess(Ess_M2ePro_Model_Order $order)
    {
        /** @var Ess_M2ePro_Model_Order $dbOrder */
        $dbOrder = Mage::getModel('M2ePro/Order')->load($order->getId());

        if ($dbOrder->getMagentoOrderId() != $order->getMagentoOrderId()) {
            return true;
        }

        return false;
    }

    //########################################

    private function prepareFromDate($lastFromDate)
    {
        // Get last from date
        // ---------------------------------------
        if (empty($lastFromDate)) {
            $lastFromDate = new DateTime('now', new DateTimeZone('UTC'));
        } else {
            $lastFromDate = new DateTime($lastFromDate, new DateTimeZone('UTC'));
        }
        // ---------------------------------------

        // Get min date for synch
        // ---------------------------------------
        $minDate = new DateTime('now',new DateTimeZone('UTC'));
        $minDate->modify('-30 days');
        // ---------------------------------------

        // Prepare last date
        // ---------------------------------------
        if ((int)$lastFromDate->format('U') < (int)$minDate->format('U')) {
            $lastFromDate = $minDate;
        }
        // ---------------------------------------

        return $lastFromDate->format('Y-m-d H:i:s');
    }

    private function prepareToDate()
    {
        $operationHistory = $this->getOperationHistory()->getParentObject('synchronization');
        if (!is_null($operationHistory)) {
            $toDate = $operationHistory->getData('start_date');
        } else {
            $toDate = new DateTime('now', new DateTimeZone('UTC'));
            $toDate = $toDate->format('Y-m-d H:i:s');
        }

        return $toDate;
    }

    //########################################
}