<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Order_Item as OrderItem;

class Ess_M2ePro_Model_Walmart_Order_Action_Handler_Shipping
    extends Ess_M2ePro_Model_Walmart_Order_Action_Handler_Abstract
{
    //########################################

    public function isNeedProcess()
    {
        if (!$this->getWalmartOrder()->isCreated() &&
            !$this->getWalmartOrder()->isUnshipped() &&
            !$this->getWalmartOrder()->isPartiallyShipped()) {
            return false;
        }

        return true;
    }

    //########################################

    protected function getServerCommand()
    {
        return array('orders', 'update', 'shipping');
    }

    protected function getRequestData()
    {
        $resultItems = array();
        $params = $this->_orderChange->getParams();

        foreach ($params['items'] as $itemData) {
            $resultItems[] = array(
                'number' => $itemData['walmart_order_item_id'],
                'qty'    => $itemData['qty'],
                'tracking_details' => $itemData['tracking_details'],
            );
        }

        return array(
            'channel_order_id' => $this->getWalmartOrder()->getWalmartOrderId(),
            'items'            => $resultItems,
        );
    }

    protected function processResult(array $responseData)
    {
        if (!isset($responseData['result']) || !$responseData['result']) {
            $this->processError();
            return;
        }

        $itemsStatuses = array();
        $params = $this->_orderChange->getParams();

        foreach ($params['items'] as $itemData) {

            /** @var Ess_M2ePro_Model_Order_Item $orderItem */
            $orderItem = $this->getOrder()->getItemsCollection()->getItemByColumnValue(
                'walmart_order_item_id', $itemData['walmart_order_item_id']
            );

            /**
             * Walmart returns the same Order Item more than one time with single QTY. That data was merged.
             * So walmart_order_item_id of real OrderItem and walmart_order_item_id in request may be different.
             * Real walmart_order_item_id will match with the ID in request when the last item will be shipped.
             */
            if ($orderItem !== null) {
                $orderItem->setData('status', OrderItem::STATUS_SHIPPED)->save();
                $itemsStatuses[$itemData['walmart_order_item_id']] = OrderItem::STATUS_SHIPPED;
            } else {
                $itemsStatuses[$itemData['walmart_order_item_id']] = OrderItem::STATUS_SHIPPED_PARTIALLY;
            }
        }

        foreach ($this->getOrder()->getItemsCollection() as $item) {
            if (!array_key_exists($item->getData('walmart_order_item_id'), $itemsStatuses)) {
                $itemsStatuses[$item->getData('walmart_order_item_id')] = $item->getData('status');
            }
        }

        $orderStatus = Mage::getModel('M2ePro/Walmart_Order_Helper')->getOrderStatus($itemsStatuses);
        $this->getOrder()->setData('status', $orderStatus);
        $this->getOrder()->save();

        $this->_orderChange->delete();
        $this->getOrder()->addSuccessLog(
            Mage::helper('M2ePro')->__('Order was successfully marked as Shipped.')
        );
    }

    /**
     * @param Ess_M2ePro_Model_Connector_Connection_Response_Message[] $messages
     * @return mixed
     */
    protected function processError(array $messages = array())
    {
        if (empty($messages)) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData(
                Mage::helper('M2ePro')->__('Order was not shipped due to Walmart error.'),
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR
            );

            $messages = array($message);
        }

        foreach ($messages as $message) {
            $this->getOrder()->getLog()->addServerResponseMessage($this->getOrder(), $message);
        }
    }

    //########################################
}
