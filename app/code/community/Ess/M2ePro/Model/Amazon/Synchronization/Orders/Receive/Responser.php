<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Synchronization_Orders_Receive_Responser
    extends Ess_M2ePro_Model_Connector_Amazon_Orders_Get_ItemsResponser
{
    protected $synchronizationLog = NULL;

    //########################################

    protected function processResponseMessages(array $messages = array())
    {
        parent::processResponseMessages($messages);

        foreach ($this->messages as $message) {

            if (!$this->isMessageError($message) && !$this->isMessageWarning($message)) {
                continue;
            }

            $logType = $this->isMessageError($message) ? Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                                                       : Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;

            $this->getSynchronizationLog()->addMessage(
                Mage::helper('M2ePro')->__($message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY]),
                $logType,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }
    }

    protected function isNeedToParseResponseData($responseBody)
    {
        if (!parent::isNeedToParseResponseData($responseBody)) {
            return false;
        }

        if ($this->hasErrorMessages()) {
            return false;
        }

        return true;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Processing_Request $processingRequest
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function unsetProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::unsetProcessingLocks($processingRequest);

        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');

        $lockItem->setNick(
            Ess_M2ePro_Model_Amazon_Synchronization_Orders_Receive::LOCK_ITEM_PREFIX.'_'.$this->params['account_id']
        );
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);
        $lockItem->remove();

        $this->getAccount()->deleteObjectLocks(NULL, $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks('synchronization', $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks('synchronization_amazon', $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks(
            Ess_M2ePro_Model_Amazon_Synchronization_Orders_Receive::LOCK_ITEM_PREFIX, $processingRequest->getHash()
        );
    }

    public function eventFailedExecuting($message)
    {
        parent::eventFailedExecuting($message);

        $this->getSynchronizationLog()->addMessage(
            Mage::helper('M2ePro')->__($message),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
        );
    }

    //########################################

    protected function processResponseData($response)
    {
        try {

            $amazonOrders = $this->processAmazonOrders($response, $this->getAccount());
            if (empty($amazonOrders)) {
                return;
            }

            $this->createMagentoOrders($amazonOrders);

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

    private function processAmazonOrders($response, Ess_M2ePro_Model_Account $account)
    {
        /** @var Ess_M2ePro_Model_Amazon_Account $amazonAccount */
        $amazonAccount = $account->getChildObject();

        $ordersLastSynchronization = $amazonAccount->getData('orders_last_synchronization');

        $orders = array();

        foreach ($response as $orderData) {
            $currentOrderUpdateDate = $orderData['purchase_update_date'];

            if (strtotime($currentOrderUpdateDate) > strtotime($ordersLastSynchronization)) {
                $ordersLastSynchronization = $currentOrderUpdateDate;
            }

            /** @var $orderBuilder Ess_M2ePro_Model_Amazon_Order_Builder */
            $orderBuilder = Mage::getModel('M2ePro/Amazon_Order_Builder');
            $orderBuilder->initialize($account, $orderData);

            $order = $orderBuilder->process();

            if (!$order) {
                continue;
            }

            $orders[] = $order;
        }

        $amazonAccount->setData('orders_last_synchronization', $ordersLastSynchronization)->save();

        return $orders;
    }

    private function createMagentoOrders($amazonOrders)
    {
        foreach ($amazonOrders as $order) {
            /** @var $order Ess_M2ePro_Model_Order */
            $order->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);

            if ($order->canCreateMagentoOrder()) {
                try {
                    $order->createMagentoOrder();
                } catch (Exception $exception) {
                    continue;
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
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account','account_id');
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getAccount()->getChildObject()->getMarketplace();
    }

    //########################################

    private function getSynchronizationLog()
    {
        if (!is_null($this->synchronizationLog)) {
            return $this->synchronizationLog;
        }

        $this->synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
        $this->synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_ORDERS);

        return $this->synchronizationLog;
    }

    //########################################
}