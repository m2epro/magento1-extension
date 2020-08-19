<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Walmart_Order_Creator
{
    /** @var Ess_M2ePro_Model_Synchronization_Log */
    protected $_synchronizationLog;

    /** @var bool */
    protected $_validateAccountCreateDate = true;

    //########################################

    public function setSynchronizationLog(Ess_M2ePro_Model_Synchronization_Log $log)
    {
        $this->_synchronizationLog = $log;
    }

    public function setValidateAccountCreateDate($mode)
    {
        $this->_validateAccountCreateDate = $mode;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Account $account
     * @param array $ordersData
     * @return Ess_M2ePro_Model_Order[]
     */
    public function processWalmartOrders(Ess_M2ePro_Model_Account $account, array $ordersData)
    {
        $orders = array();
        $accountCreateDate = new DateTime($account->getData('create_date'), new DateTimeZone('UTC'));

        foreach ($ordersData as $orderData) {
            try {
                $orderCreateDate = new DateTime($orderData['purchase_date'], new DateTimeZone('UTC'));
                if ($this->_validateAccountCreateDate && $orderCreateDate < $accountCreateDate) {
                    continue;
                }

                /** @var $orderBuilder Ess_M2ePro_Model_Walmart_Order_Builder */
                $orderBuilder = Mage::getModel('M2ePro/Walmart_Order_Builder');
                $orderBuilder->initialize($account, $orderData);

                $order = $orderBuilder->process();
                $order && $orders[] = $order;
            } catch (\Exception $e) {
                $this->_synchronizationLog->addMessageFromException($e);
                Mage::helper('M2ePro/Module_Exception')->process($e);
                continue;
            }
        }

        return $orders;
    }

    /**
     * @param Ess_M2ePro_Model_Order[] $orders
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function processMagentoOrders($orders)
    {
        foreach ($orders as $order) {
            if ($this->isOrderChangedInParallelProcess($order)) {
                continue;
            }

            try {
                $this->createMagentoOrder($order);
            } catch (\Exception $e) {
                $this->_synchronizationLog->addMessageFromException($e);
                Mage::helper('M2ePro/Module_Exception')->process($e);
                continue;
            }
        }
    }

    //########################################

    public function createMagentoOrder(Ess_M2ePro_Model_Order $order)
    {
        if ($order->canCreateMagentoOrder()) {
            try {
                $order->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);
                $order->addNoticeLog(
                    'Magento order creation rules are met. M2E Pro will attempt to create Magento order.'
                );
                $order->createMagentoOrder();
            } catch (Exception $exception) {
                return;
            }
        }

        if ($order->getReserve()->isNotProcessed() && $order->isReservable()) {
            $order->getReserve()->place();
        }

        if ($order->getChildObject()->canCreateInvoice()) {
            $order->createInvoice();
        }

        if ($order->getChildObject()->canCreateShipment()) {
            $order->createShipment();
        }

        if ($order->getStatusUpdateRequired()) {
            $order->updateMagentoOrderStatus();
        }
    }

    /**
     * This is going to protect from Magento Orders duplicates.
     * (Is assuming that there may be a parallel process that has already created Magento Order)
     *
     * But this protection is not covering cases when two parallel cron processes are isolated by mysql transactions
     */
    public function isOrderChangedInParallelProcess(Ess_M2ePro_Model_Order $order)
    {
        /** @var Ess_M2ePro_Model_Order $dbOrder */
        $dbOrder = Mage::getModel('M2ePro/Order')->load($order->getId());

        if ($dbOrder->getMagentoOrderId() != $order->getMagentoOrderId()) {
            return true;
        }

        return false;
    }

    //########################################
}