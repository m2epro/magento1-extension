<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Orders_Cancellation
    extends Ess_M2ePro_Model_Ebay_Synchronization_Orders_Abstract
{
    // ##########################################################

    protected function getNick()
    {
        return '/cancellation/';
    }

    protected function getTitle()
    {
        return 'Cancellation';
    }

    // ----------------------------------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    // ----------------------------------------------------------

    protected function intervalIsEnabled()
    {
        return true;
    }

    protected function intervalIsLocked()
    {
        if ($this->getInitiator() == Ess_M2ePro_Helper_Data::INITIATOR_USER ||
            $this->getInitiator() == Ess_M2ePro_Helper_Data::INITIATOR_DEVELOPER) {
            return false;
        }

        return parent::intervalIsLocked();
    }

    // ##########################################################

    protected function performActions()
    {
        $permittedAccounts = $this->getPermittedAccounts();
        if (empty($permittedAccounts)) {
            return;
        }

        $iteration = 1;
        $percentsForOneStep = $this->getPercentsInterval() / count($permittedAccounts);

        foreach ($permittedAccounts as $account) {
            /** @var $account Ess_M2ePro_Model_Account **/

            // ----------------------------------------------------------
            $this->getActualOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');

            // M2ePro_TRANSLATIONS
            // The "Cancellation" Action for eBay Account: "%account_title%" is started. Please wait...
            $status = 'The "Cancellation" Action for eBay Account: "%account_title%" is started. Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            // ----------------------------------------------------------

            $this->processAccount($account);

            // ----------------------------------------------------------
            // M2ePro_TRANSLATIONS
            // The "Cancellation" Action for eBay Account: "%account_title%" is finished. Please wait...
            $status = 'The "Cancellation" Action for eBay Account: "%account_title%" is finished. Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $iteration * $percentsForOneStep);
            $this->getActualLockItem()->activate();
            // ----------------------------------------------------------

            $iteration++;
        }
    }

    // ##########################################################

    private function getPermittedAccounts()
    {
        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
        return $accountsCollection->getItems();
    }

    // ----------------------------------------------------------

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

    // ##########################################################

    private function getUnpaidOrdersUpdates(Ess_M2ePro_Model_Account $account)
    {
        $reservationDays = $account->getChildObject()->getMagentoOrdersReservationDays();
        list($startDate, $endDate) = $this->getDateRangeForUnpaidOrders($reservationDays);

        $ordersIds = Mage::getResourceModel('M2ePro/Ebay_Order')
            ->getCancellationCandidatesChannelIds($account->getId(), $startDate, $endDate);

        if (empty($ordersIds)) {
            return array();
        }

        $dispatcherObj = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector('sales', 'get', 'orders',
                                                            array('orders_ids' => $ordersIds),
                                                            NULL, NULL, $account, NULL);

        $response = $dispatcherObj->process($connectorObj);
        $this->processResponseMessages($connectorObj);

        return isset($response['orders']) ? $response['orders'] : array();
    }

    private function processResponseMessages(Ess_M2ePro_Model_Connector_Protocol $connectorObj)
    {
        foreach ($connectorObj->getErrorMessages() as $message) {

            if (!$connectorObj->isMessageError($message) && !$connectorObj->isMessageWarning($message)) {
                continue;
            }

            $logType = $connectorObj->isMessageError($message) ? Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                                                               : Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;

            $this->getLog()->addMessage(
                Mage::helper('M2ePro')->__($message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY]),
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
            ->addFieldToFilter('ebay_order_id', $orderData['ebay_order_id'])
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

            $order->setData('payment_details', json_encode($paymentDetails));
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

            $order->setData('shipping_details', json_encode($shippingDetails));
            $order->setData('shipping_status', $shippingStatus);
            $order->setData('tax_details', json_encode($orderData['tax_details']));
        }

        $order->save();

        return $order;
    }

    // ##########################################################

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

    // ##########################################################

    private function openUnpaidItemProcess(Ess_M2ePro_Model_Order $order)
    {
        $items = $this->getOrderItemsForUnpaidItemProcess($order);
        if (empty($items)) {
            return;
        }

        $action = Ess_M2ePro_Model_Connector_Ebay_OrderItem_Dispatcher::ACTION_ADD_DISPUTE;
        $params = array(
            'explanation' => Ess_M2ePro_Model_Ebay_Order_Item::DISPUTE_EXPLANATION_BUYER_HAS_NOT_PAID,
            'reason'      => Ess_M2ePro_Model_Ebay_Order_Item::DISPUTE_REASON_BUYER_HAS_NOT_PAID
        );

        /** @var $dispatcher Ess_M2ePro_Model_Connector_Ebay_OrderItem_Dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Connector_Ebay_OrderItem_Dispatcher');
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

    // ##########################################################

    private function getCheckoutStatus($orderData)
    {
        return Mage::getSingleton('M2ePro/Ebay_Order_Helper')->getCheckoutStatus($orderData['checkout_status']);
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

    // ##########################################################
}