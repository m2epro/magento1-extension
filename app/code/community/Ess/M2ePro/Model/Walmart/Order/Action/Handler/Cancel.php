<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Order_Item as OrderItem;

class Ess_M2ePro_Model_Walmart_Order_Action_Handler_Cancel
    extends Ess_M2ePro_Model_Walmart_Order_Action_Handler_Abstract
{
    private $params = array();

    //########################################

    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }

    //########################################

    public function isNeedProcess()
    {
        if (!$this->getWalmartOrder()->isCreated() &&
            !$this->getWalmartOrder()->isUnshipped() &&
            !$this->getWalmartOrder()->isPartiallyShipped()) {
            return false;
        }

        $orderItemCollection = $this->getOrder()->getItemsCollection();
        $orderItemCollection->addFieldToFilter(
            'status',
            array(
                'in' => array(
                    OrderItem::STATUS_ACKNOWLEDGED,
                    OrderItem::STATUS_CREATED
                )
            )
        );

        if ($orderItemCollection->getSize() == 0) {
            return false;
        }

        return true;
    }

    //########################################

    protected function getServerCommand()
    {
        return array('orders', 'cancel', 'entity');
    }

    protected function getRequestData()
    {
        $resultItems = array();

        foreach ($this->params['items'] as $itemData) {

            /** @var Ess_M2ePro_Model_Order_Item $orderItem */
            $orderItem = $this->getOrder()->getItemsCollection()->getItemByColumnValue(
                'walmart_order_item_id', $itemData['item_id']
            );

            if (!is_null($orderItem) &&
                ($orderItem->getData('status') != OrderItem::STATUS_ACKNOWLEDGED &&
                 $orderItem->getData('status') != OrderItem::STATUS_CREATED))
            {
                continue;
            }

            $resultItems[] = array(
                'number' => $itemData['item_id'],
                'qty'    => $itemData['qty'],
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

        foreach ($this->params['items'] as $itemData) {

            /** @var Ess_M2ePro_Model_Order_Item $orderItem */
            $orderItem = $this->getOrder()->getItemsCollection()->getItemByColumnValue(
                'walmart_order_item_id', $itemData['item_id']
            );

            /**
             * Walmart returns the same Order Item more than one time with single QTY. That data was merged.
             * So walmart_order_item_id of real OrderItem and walmart_order_item_id in request may be different.
             * Real walmart_order_item_id will match with the ID in request when the last item will be cancelled.
             */
            if (!is_null($orderItem)) {
                $orderItem->setData('status', OrderItem::STATUS_CANCELLED)->save();
                $itemsStatuses[$itemData['item_id']] = OrderItem::STATUS_CANCELLED;
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

        $this->getOrder()->getLog()->addMessage(
            $this->getOrder()->getId(),
            Mage::helper('M2ePro')->__('Order was successfully cancelled.'),
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS
        );
    }

    /**
     * @param Ess_M2ePro_Model_Connector_Connection_Response_Message[] $messages
     */
    protected function processError(array $messages = array())
    {
        if (empty($messages)) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData(
                Mage::helper('M2ePro')->__('Order was not cancelled due to Walmart error.'),
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR
            );

            $messages = array($message);
        }

        foreach ($messages as $message) {
            $this->getOrder()->getLog()->addMessage(
                $this->getOrder()->getId(),
                $message->getText(),
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
            );
        }
    }

    //########################################
}