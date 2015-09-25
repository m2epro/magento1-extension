<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
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

    private $orders = array();

    // ########################################

    public function unsetProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::unsetProcessingLocks($processingRequest);

        foreach ($this->getOrders() as $order) {
            $order->deleteObjectLocks('update_shipping_status', $processingRequest->getHash());
        }
    }

    public function eventFailedExecuting($message)
    {
        parent::eventFailedExecuting($message);

        $logs = array();
        $currentDate = Mage::helper('M2ePro')->getCurrentGmtDate();

        $logMessage = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
            'Rakuten.com Order status was not updated. Reason: %msg%', array('msg' => $message)
        );

        foreach (array_keys($this->params) as $orderId) {
            $logs[] = array(
                'order_id'       => (int)$orderId,
                'message'        => $logMessage,
                'type'           => Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                'component_mode' => Ess_M2ePro_Helper_Component_Buy::NICK,
                'initiator'      => Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                'create_date'    => $currentDate
            );
        }

        $this->createLogEntries($logs);
    }

    // ########################################

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function processResponseData($response)
    {
        $logs = array();
        $currentDate = Mage::helper('M2ePro')->getCurrentGmtDate();

        // Check global messages
        //----------------------
        $globalMessages = $this->messages;
        if (isset($response['messages']['0-id']) && is_array($response['messages']['0-id'])) {
            $globalMessages = array_merge($globalMessages,$response['messages']['0-id']);
        }

        if (count($globalMessages) > 0) {
            foreach ($this->getOrdersIds() as $orderId) {
                foreach ($globalMessages as $message) {
                    $text = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY];

                    $logs[] = array(
                        'order_id'       => $orderId,
                        'message'        => $text,
                        'type'           => Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                        'component_mode' => Ess_M2ePro_Helper_Component_Buy::NICK,
                        'initiator'      => Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                        'create_date'    => $currentDate
                    );
                }
            }

            $this->createLogEntries($logs);

            return;
        }
        //----------------------

        // Check separate messages
        //----------------------
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

                $logs[] = array(
                    'order_id'       => $orderId,
                    'message'        => $text,
                    'type'           => Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    'component_mode' => Ess_M2ePro_Helper_Component_Buy::NICK,
                    'initiator'      => Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                    'create_date'    => $currentDate
                );
            }
        }
        //----------------------

        //----------------------
        foreach ($this->getOrdersIds() as $orderId) {

            if (in_array($orderId, $failedOrdersIds)) {
                continue;
            }

            $logs[] = array(
                'order_id'       => (int)$orderId,
                'message'        => 'Rakuten.com Order status was updated to Shipped.',
                'type'           => Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                'component_mode' => Ess_M2ePro_Helper_Component_Buy::NICK,
                'initiator'      => Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                'create_date'    => $currentDate
            );

            $logMessage = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                'Tracking number "%num%" for "%code%" has been sent to Rakuten.com.', array(
                    '!num' => $this->params[$orderId]['tracking_number'],
                    'code' => $this->params[$orderId]['tracking_type']
                )
            );

            $logs[] = array(
                'order_id'       => (int)$orderId,
                'message'        => $logMessage,
                'type'           => Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                'component_mode' => Ess_M2ePro_Helper_Component_Buy::NICK,
                'initiator'      => Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                'create_date'    => $currentDate
            );
        }
        //----------------------

        $this->createLogEntries($logs);
    }

    // ########################################

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

    private function getOrdersIds()
    {
        $ids = array();

        foreach ($this->params as $requestData) {
            $ids[] = (int)$requestData['order_id'];
        }

        return array_unique($ids);
    }

    // ########################################

    private function createLogEntries(array $data)
    {
        if (empty($data)) {
            return;
        }

        /** @var $writeConnection Varien_Db_Adapter_Interface */
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $writeConnection->insertMultiple(Mage::getResourceModel('M2ePro/Order_Log')->getMainTable(), $data);
    }

    // ########################################
}