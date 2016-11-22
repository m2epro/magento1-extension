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

        if ($this->hasErrorMessages() && !isset($response['items'])) {
            return false;
        }

        return true;
    }

    //########################################

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
        $accounts = $this->getAccountsByAccessTokens();

        $processedAmazonOrders = array();
        foreach ($response['orders'] as $accountAccessToken => $ordersData) {

            $amazonOrders = $this->processAmazonOrders($ordersData, $accounts[$accountAccessToken]);

            if (empty($amazonOrders)) {
                continue;
            }

            $processedAmazonOrders[] = $amazonOrders;
        }

        $merchantId = current($accounts)->getChildObject()->getMerchantId();

        if (!empty($response['job_token'])) {
            Mage::getSingleton('M2ePro/Config_Synchronization')->setGroupValue(
                "/amazon/orders/receive/{$merchantId}/", "job_token", $response['job_token']
            );
        } else {
            Mage::getSingleton('M2ePro/Config_Synchronization')->deleteGroupValue(
                "/amazon/orders/receive/{$merchantId}/", "job_token"
            );
        }

        Mage::getSingleton('M2ePro/Config_Synchronization')->setGroupValue(
            "/amazon/orders/receive/{$merchantId}/", "from_update_date", $response['to_update_date']
        );

        foreach ($processedAmazonOrders as $amazonOrders) {
            try {

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
    }

    // ---------------------------------------

    private function processAmazonOrders($response, Ess_M2ePro_Model_Account $account)
    {
        $orders = array();

        foreach ($response as $orderData) {

            /** @var $orderBuilder Ess_M2ePro_Model_Amazon_Order_Builder */
            $orderBuilder = Mage::getModel('M2ePro/Amazon_Order_Builder');
            $orderBuilder->initialize($account, $orderData);

            $order = $orderBuilder->process();

            if (!$order) {
                continue;
            }

            $orders[] = $order;
        }

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