<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Buy_Orders_Update_ShippingResponser
    extends Ess_M2ePro_Model_Connector_Buy_Responser
{
    // M2ePro_TRANSLATIONS
    // Rakuten.com Order status was not updated. Reason: %msg%
    // M2ePro_TRANSLATIONS
    // Rakuten.com Order status was updated to Shipped.
    // M2ePro_TRANSLATIONS
    // Tracking number "%num%" for "%code%" has been sent to Rakuten.com.

    private $orders = NULL;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Processing_Request $processingRequest
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function unsetProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::unsetProcessingLocks($processingRequest);

        foreach ($this->getOrders() as $order) {
            $order->deleteObjectLocks('update_shipping_status', $processingRequest->getHash());
        }
    }

    /**
     * @param $message
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function eventFailedExecuting($message)
    {
        parent::eventFailedExecuting($message);

        foreach ($this->getOrders() as $order) {
            $order->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);
            $order->addErrorLog('Rakuten.com Order status was not updated. Reason: %msg%', array('msg' => $message));
        }
    }

    //########################################

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function processResponseData($response)
    {
        /** @var $orders Ess_M2ePro_Model_Order[] */
        $orders = $this->getOrders();

        // Check global messages
        // ---------------------------------------
        $globalMessages = $this->messages;
        if (isset($response['messages']['0-id']) && is_array($response['messages']['0-id'])) {
            $globalMessages = array_merge($globalMessages,$response['messages']['0-id']);
        }

        if (count($globalMessages) > 0) {
            foreach ($orders as $order) {
                foreach ($globalMessages as $message) {
                    $text = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY];

                    $order->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);
                    $order->addErrorLog($text);
                }
            }

            return;
        }
        // ---------------------------------------

        // Check separate messages
        // ---------------------------------------
        $failedOrdersIds = array();

        foreach ($response['messages'] as $orderItemId => $messages) {
            $orderItemId = (int)$orderItemId;

            if ($orderItemId <= 0) {
                continue;
            }

            $orderId = $this->getOrderIdByOrderItemId($orderItemId);

            if (!is_numeric($orderId)) {
                continue;
            }

            $failedOrdersIds[] = $orderId;

            foreach ($messages as $message) {
                $text = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY];

                $orders[$orderId]->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);
                $orders[$orderId]->addErrorLog($text);
            }
        }
        // ---------------------------------------

        // ---------------------------------------
        foreach ($orders as $order) {

            if (in_array($order->getId(), $failedOrdersIds)) {
                continue;
            }

            $order->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);
            $order->addSuccessLog('Rakuten.com Order status was updated to Shipped.');

            $order->addSuccessLog('Tracking number "%num%" for "%code%" has been sent to Rakuten.com.',
                array(
                    '!num' => $this->params[$order->getId()]['tracking_number'],
                    'code' => $this->params[$order->getId()]['tracking_type']
                )
            );
        }
        // ---------------------------------------
    }

    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     * @return Ess_M2ePro_Model_Order[]
     */
    private function getOrders()
    {
        if (!is_null($this->orders)) {
            return $this->orders;
        }

        $ordersIds = array();

        foreach ($this->params as $update) {
            if (!isset($update['order_id'])) {
                throw new Ess_M2ePro_Model_Exception_Logic('Order ID is not defined.');
            }

            $ordersIds[] = (int)$update['order_id'];
        }

        $this->orders = Mage::getModel('M2ePro/Order')
            ->getCollection()
            ->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Buy::NICK)
            ->addFieldToFilter('id', array('in' => $ordersIds))
            ->getItems();

        return $this->orders;
    }

    private function getOrderIdByOrderItemId($orderItemId)
    {
        foreach ($this->params as $requestOrderItemId => $requestData) {
            if ($orderItemId == $requestOrderItemId) {
                return $requestData['order_id'];
            }
        }

        return null;
    }

    //########################################
}