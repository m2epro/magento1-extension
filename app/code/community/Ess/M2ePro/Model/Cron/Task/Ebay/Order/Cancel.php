<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Ebay_Order_Cancel extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'ebay/order/cancel';

    //########################################

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getSynchronizationLog()
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
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
            // ---------------------------------------

            try {

                $this->processAccount($account);

            } catch (Exception $exception) {

                $message = Mage::helper('M2ePro')->__(
                    'The "Cancellation" Action for eBay Account "%account%" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }

            // ---------------------------------------
            $this->getLockItemManager()->activate();
            // ---------------------------------------
        }
    }

    //########################################

    private function getPermittedAccounts()
    {
        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
        return $accountsCollection->getItems();
    }

    // ---------------------------------------

    private function processAccount(Ess_M2ePro_Model_Account $account)
    {
        if (!$account->getChildObject()->shouldCreateMagentoOrderImmediately()
            || $account->getChildObject()->getMagentoOrdersReservationDays() <= 0
        ) {
            return;
        }

        $data = $this->getUnpaidOrdersUpdates($account);
        if (empty($data)) {
            return;
        }

        /** @var $cancellationCandidates Ess_M2ePro_Model_Order[] */
        $cancellationCandidates = array();
        foreach ($data as $orderData) {
            $cancellationCandidates[] = $this->associateAndUpdateOrder($account, $orderData);
        }

        $cancellationCandidates = array_filter($cancellationCandidates);
        if (empty($cancellationCandidates)) {
            return;
        }

        foreach ($cancellationCandidates as $order) {
            $this->processOrder($order);
        }
    }

    //########################################

    private function getUnpaidOrdersUpdates(Ess_M2ePro_Model_Account $account)
    {
        $reservationDays = $account->getChildObject()->getMagentoOrdersReservationDays();
        list($startDate, $endDate) = $this->getDateRangeForUnpaidOrders($reservationDays);

        $ordersIds = Mage::getResourceModel('M2ePro/Ebay_Order')
            ->getCancellationCandidatesChannelIds($account->getId(), $startDate, $endDate);

        if (empty($ordersIds)) {
            return array();
        }

        $dispatcherObj = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector('orders', 'get', 'orders',
                                                            array('orders_ids' => $ordersIds),
                                                            NULL, NULL, $account);

        $dispatcherObj->process($connectorObj);
        $response = $connectorObj->getResponseData();

        $this->processResponseMessages($connectorObj->getResponseMessages());

        return isset($response['orders']) ? $response['orders'] : array();
    }

    private function processResponseMessages(array $messages)
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

    private function getDateRangeForUnpaidOrders($reservationDays)
    {
        $reservationDays = (int)$reservationDays;

        if ($reservationDays < 1) {
            throw new InvalidArgumentException('Reservation period cannot be less than 1 day.');
        }

        $endDate = new DateTime('now', new DateTimeZone('UTC'));
        $endDate->modify("-{$reservationDays} days");

        $startDate = clone $endDate;
        $startDate->modify('-3 days');

        return array($startDate, $endDate);
    }

    private function associateAndUpdateOrder(Ess_M2ePro_Model_Account $account, array $orderData)
    {
        /** @var $order Ess_M2ePro_Model_Order */
        $order = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order')
            ->addFieldToFilter('account_id', $account->getId())
            ->addFieldToFilter('ebay_order_id', $orderData['identifiers']['ebay_order_id'])
            ->getFirstItem();

        if (!$order->getId()) {
            return null;
        }

        $order->setAccount($account);

        /** @var $helper Ess_M2ePro_Model_Ebay_Order_Helper */
        $helper = Mage::getSingleton('M2ePro/Ebay_Order_Helper');

        $checkoutStatus = $this->getCheckoutStatus($orderData);
        $paymentStatus = $this->getPaymentStatus($orderData);
        $shippingStatus = $this->getShippingStatus($orderData);

        if ($paymentStatus == Ess_M2ePro_Model_Ebay_Order::PAYMENT_STATUS_COMPLETED) {
            $paymentDetails = $orderData['payment'];
            unset($paymentDetails['external_transactions']);

            $paymentDetails['method'] = $helper->getPaymentMethodNameByCode(
                $paymentDetails['method'], $order->getMarketplaceId()
            );

            $order->setData('payment_details', Mage::helper('M2ePro')->jsonEncode($paymentDetails));
            $order->setData('payment_status', $paymentStatus);
        }

        if (
            !$order->getChildObject()->isCheckoutCompleted() &&
            $checkoutStatus == Ess_M2ePro_Model_Ebay_Order::CHECKOUT_STATUS_COMPLETED
        ) {
            $shippingDetails = $orderData['shipping'];

            $shippingDetails['service'] = $helper->getShippingServiceNameByCode(
                $shippingDetails['service'], $order->getMarketplaceId()
            );

            $order->setData('shipping_details', Mage::helper('M2ePro')->jsonEncode($shippingDetails));
            $order->setData('shipping_status', $shippingStatus);
            $order->setData('tax_details', Mage::helper('M2ePro')->jsonEncode($orderData['selling']['tax_details']));
        }

        $order->save();

        return $order;
    }

    //########################################

    private function processOrder(Ess_M2ePro_Model_Order $order)
    {
        if ($order->getChildObject()->isPaymentCompleted()) {
            // unpaid order became paid
            // immediately created magento order should be canceled
            // and new magento order should be created instead

            if ($order->canCancelMagentoOrder()) {
                $message = 'Payment Status was updated to Paid on eBay. '.
                           'As Magento Order #%order_id% can have wrong data, it have to be cancelled.';
                $order->addWarningLog($message, array('!order_id' => $order->getMagentoOrder()->getRealOrderId()));

                try {
                    $order->cancelMagentoOrder();
                } catch (Exception $e) {
                    // magento order was not cancelled
                    // do not create new magento order to prevent oversell
                    return;
                }
            }

            $this->clearOrder($order);
            $this->createMagentoOrder($order);
        } else {
            // unpaid order did not become paid
            // immediately created magento order should be canceled
            // and unpaid item process should be opened for each order item

            if ($order->canCancelMagentoOrder()) {
                $message = 'Payment Status was not updated to Paid. Magento Order #%order_id% '.
                           'have to be cancelled according to Account\'s Automatic Cancellation Setting.';
                $order->addWarningLog($message, array('!order_id' => $order->getMagentoOrder()->getRealOrderId()));

                try {
                    $order->cancelMagentoOrder();
                } catch (Exception $e) {}
            }

            $this->openUnpaidItemProcess($order);
        }
    }

    private function createMagentoOrder(Ess_M2ePro_Model_Order $order)
    {
        if ($order->canCreateMagentoOrder()) {
            try {
                $order->addNoticeLog(
                    'Magento order creation rules are met. M2E Pro will attempt to create Magento order.'
                );
                $order->createMagentoOrder();
            } catch (Exception $exception) {
                return;
            }
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
        $order->updateMagentoOrderStatus();
    }

    private function clearOrder(Ess_M2ePro_Model_Order $order)
    {
        $order->setMagentoOrder(null);
        $order->setData('magento_order_id', null);
        $order->save();

        $order->getItemsCollection()->walk('setProduct', array(null));
    }

    //########################################

    private function openUnpaidItemProcess(Ess_M2ePro_Model_Order $order)
    {
        $items = $this->getOrderItemsForUnpaidItemProcess($order);
        if (empty($items)) {
            return;
        }

        $action = Ess_M2ePro_Model_Ebay_Connector_OrderItem_Dispatcher::ACTION_ADD_DISPUTE;
        $params = array(
            'explanation' => Ess_M2ePro_Model_Ebay_Order_Item::DISPUTE_EXPLANATION_BUYER_HAS_NOT_PAID,
            'reason'      => Ess_M2ePro_Model_Ebay_Order_Item::DISPUTE_REASON_BUYER_HAS_NOT_PAID
        );

        /** @var $dispatcher Ess_M2ePro_Model_Ebay_Connector_OrderItem_Dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Ebay_Connector_OrderItem_Dispatcher');
        $dispatcher->process($action, $items, $params);
    }

    private function getOrderItemsForUnpaidItemProcess(Ess_M2ePro_Model_Order $order)
    {
        /** @var Ess_M2ePro_Model_Mysql4_Order_Item_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order_Item');
        $collection->addFieldToFilter('order_id', $order->getId());
        $collection->addFieldToFilter(
            'unpaid_item_process_state', Ess_M2ePro_Model_Ebay_Order_Item::UNPAID_ITEM_PROCESS_NOT_OPENED
        );

        return $collection->getItems();
    }

    //########################################

    private function getCheckoutStatus($orderData)
    {
        return Mage::getSingleton('M2ePro/Ebay_Order_Helper')->getCheckoutStatus($orderData['statuses']['checkout']);
    }

    private function getPaymentStatus($orderData)
    {
        return Mage::getSingleton('M2ePro/Ebay_Order_Helper')->getPaymentStatus(
            $orderData['payment']['method'], $orderData['payment']['date'], $orderData['payment']['status']
        );
    }

    private function getShippingStatus($orderData)
    {
        return Mage::getSingleton('M2ePro/Ebay_Order_Helper')->getShippingStatus(
            $orderData['shipping']['date'], !empty($orderData['shipping']['service'])
        );
    }

    //########################################
}