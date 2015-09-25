<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Amazon_Orders_Refund_ItemsResponser
    extends Ess_M2ePro_Model_Connector_Amazon_Responser
{
    // M2ePro_TRANSLATIONS
    // Amazon Order was not refunded. Reason: %msg%
    // Amazon Order was refunded.

    private $orders = NULL;

    // ########################################

    public function unsetProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::unsetProcessingLocks($processingRequest);

        foreach ($this->getOrders() as $order) {
            $order->deleteObjectLocks('refund_order', $processingRequest->getHash());
        }
    }

    public function eventFailedExecuting($message)
    {
        parent::eventFailedExecuting($message);

        $logs = array();
        $currentDate = Mage::helper('M2ePro')->getCurrentGmtDate();

        $logMessage = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
            'Amazon Order was not refunded. Reason: %msg%', array('msg' => $message)
        );

        foreach ($this->getOrders() as $order) {
            $logs[] = array(
                'order_id'       => $order->getId(),
                'message'        => $logMessage,
                'type'           => Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                'component_mode' => Ess_M2ePro_Helper_Component_Amazon::NICK,
                'initiator'      => Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                'create_date'    => $currentDate
            );
        }

        if (!empty($logs)) {
            $this->createLogEntries($logs);
        }
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
            foreach ($this->getOrders() as $order) {
                foreach ($globalMessages as $message) {
                    $text = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY];

                    $logMessage = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Amazon Order was not refunded. Reason: %msg%', array('msg' => $text)
                    );

                    $logs[] = array(
                        'order_id'       => $order->getId(),
                        'message'        => $logMessage,
                        'type'           => Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                        'component_mode' => Ess_M2ePro_Helper_Component_Amazon::NICK,
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

        foreach ($response['messages'] as $changeId => $messages) {
            $changeId = (int)$changeId;

            if ($changeId <= 0) {
                continue;
            }

            $orderId = $this->getOrderIdByChangeId($changeId);

            if (!is_numeric($orderId)) {
                continue;
            }

            $failedOrdersIds[] = $orderId;

            foreach ($messages as $message) {
                $text = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY];

                $logMessage = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Amazon Order was not refunded. Reason: %msg%', array('msg' => $text)
                );

                $logs[] = array(
                    'order_id'       => $orderId,
                    'message'        => $logMessage,
                    'type'           => Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    'component_mode' => Ess_M2ePro_Helper_Component_Amazon::NICK,
                    'initiator'      => Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                    'create_date'    => $currentDate
                );
            }
        }
        //----------------------

        //----------------------
        foreach ($this->params as $changeId => $requestData) {
            $orderId = $this->getOrderIdByChangeId($changeId);

            if (in_array($orderId, $failedOrdersIds)) {
                continue;
            }

            if (!is_numeric($orderId)) {
                continue;
            }

            $logs[] = array(
                'order_id'       => (int)$orderId,
                'message'        => 'Amazon Order was refunded.',
                'type'           => Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                'component_mode' => Ess_M2ePro_Helper_Component_Amazon::NICK,
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
            ->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Amazon::NICK)
            ->addFieldToFilter('id', array('in' => $ordersIds))
            ->getItems();

        return $this->orders;
    }

    private function getOrderIdByChangeId($changeId)
    {
        foreach ($this->params as $requestChangeId => $requestData) {
            if ($changeId == $requestChangeId && isset($requestData['order_id'])) {
                return $requestData['order_id'];
            }
        }

        return NULL;
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