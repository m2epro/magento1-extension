<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Order_Shipment_Handler extends Ess_M2ePro_Model_Order_Shipment_Handler
{
    //########################################

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @param array $trackingDetails
     * @param array $itemsToShip
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function processStatusUpdates(Ess_M2ePro_Model_Order $order, array $trackingDetails, array $itemsToShip)
    {
        if (empty($trackingDetails)) {
            return $order->getChildObject()->updateShippingStatus();
        }

        if (empty($itemsToShip) || count($itemsToShip) == $order->getItemsCollection()->getSize()) {
            return $order->getChildObject()->updateShippingStatus($trackingDetails);
        }

        $succeeded = true;
        $initianor = $order->getLog()->getInitiator();

        foreach ($itemsToShip as $item) {
            /**@var Ess_M2ePro_Model_Order_Item $item */
            $item->getChildObject()->getEbayOrder()->getParentObject()->getLog()->setInitiator($initianor);
            if ($item->getChildObject()->updateShippingStatus($trackingDetails)) {
                continue;
            }

            $succeeded = false;
        }

        return $succeeded;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return array
     */
    protected function getItemsToShip(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Shipment $shipment)
    {
        $itemsToShip = array();
        $allowedItems = array();
        foreach ($shipment->getAllItems() as $shipmentItem) {
            /** @var $shipmentItem Mage_Sales_Model_Order_Shipment_Item */
            $orderItem = $shipmentItem->getOrderItem();
            $parentOrderItemId = $orderItem->getParentItemId();

            if ($parentOrderItemId !== null) {
                !in_array($parentOrderItemId, $allowedItems) && ($allowedItems[] = $parentOrderItemId);
                continue;
            }

            if (!Mage::helper('M2ePro/Magento_Product')->isBundleType($orderItem->getProductType()) &&
                !Mage::helper('M2ePro/Magento_Product')->isGroupedType($orderItem->getProductType())) {
                $allowedItems[] = $orderItem->getId();
            }

            $orderItems = $this->getItemsToShipForShipmentItem($order, $shipmentItem);
            if ($orderItems === null) {
                return array();
            }

            $itemsToShip += $orderItems;
        }

        $resultItems = array();
        foreach ($itemsToShip as $orderItemId => $item) {
            if (!in_array($orderItemId, $allowedItems)) {
                continue;
            }

            $resultItems[] = $item;
        }

        return $resultItems;
    }

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @param Mage_Sales_Model_Order_Shipment_Item $shipmentItem
     * @return array|null
     */
    protected function getItemsToShipForShipmentItem(
        Ess_M2ePro_Model_Order $order,
        Mage_Sales_Model_Order_Shipment_Item $shipmentItem
    ) {
        $orderItem = $shipmentItem->getOrderItem();
        $additionalData = Mage::helper('M2ePro')->unserialize($orderItem->getAdditionalData());

        $itemId = $transactionId = null;
        $orderItemDataIdentifier = Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER;

        if (isset($additionalData[$orderItemDataIdentifier]['items'])) {
            if (!is_array($additionalData[$orderItemDataIdentifier]['items'])
                || count($additionalData[$orderItemDataIdentifier]['items']) != 1
            ) {
                return null;
            }

            if (isset($additionalData[$orderItemDataIdentifier]['items'][0]['item_id'])) {
                $itemId = $additionalData[$orderItemDataIdentifier]['items'][0]['item_id'];
            }

            if (isset($additionalData[$orderItemDataIdentifier]['items'][0]['transaction_id'])) {
                $transactionId = $additionalData[$orderItemDataIdentifier]['items'][0]['transaction_id'];
            }
        }

        if ($itemId === null || $transactionId === null) {
            return array();
        }

        $item = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Order_Item')
            ->addFieldToFilter('order_id', $order->getId())
            ->addFieldToFilter('item_id', $itemId)
            ->addFieldToFilter('transaction_id', $transactionId)
            ->getFirstItem();

        if (!$item->getId()) {
            return array();
        }

        return array($orderItem->getId() => $item);
    }

    /**
     * @return string
     */
    protected function getComponentMode()
    {
        return Ess_M2ePro_Helper_Component_Ebay::NICK;
    }

    //########################################
}