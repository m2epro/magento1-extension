<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Helper_Data as Helper;

class Ess_M2ePro_Model_Amazon_Order_Shipment_Handler extends Ess_M2ePro_Model_Order_Shipment_Handler
{
    //########################################

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @param Mage_Sales_Model_Order_Shipment_Item $shipmentItem
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getItemsToShipForShipmentItem(
        Ess_M2ePro_Model_Order $order,
        Mage_Sales_Model_Order_Shipment_Item $shipmentItem
    ) {
        $additionalData = Mage::helper('M2ePro')->unserialize(
            $shipmentItem->getOrderItem()->getAdditionalData()
        );

        if (isset($additionalData[Helper::CUSTOM_IDENTIFIER]['shipments'][$shipmentItem->getId()])) {
            return $additionalData[Helper::CUSTOM_IDENTIFIER]['shipments'][$shipmentItem->getId()];
        }

        if (!isset($additionalData[Helper::CUSTOM_IDENTIFIER]['items']) ||
            !is_array($additionalData[Helper::CUSTOM_IDENTIFIER]['items'])) {
            return array();
        }

        $shipmentItems = array();
        $qtyAvailable = (int)$shipmentItem->getQty();

        $itemsCollection = $order->getItemsCollection();
        foreach ($additionalData[Helper::CUSTOM_IDENTIFIER]['items'] as &$data) {
            if ($qtyAvailable <= 0 || !isset($data['order_item_id'])) {
                continue;
            }

            /** @var Ess_M2ePro_Model_Order_Item $item */
            $orderItemId = $data['order_item_id'];
            $itemsCollection->clear()->getSelect()->reset(Zend_Db_Select::WHERE);
            $item = $itemsCollection->addFieldToFilter('amazon_order_item_id', $orderItemId)->getFirstItem();
            if ($item === null) {
                continue;
            }

            /*
             * Extension stores Shipped QTY for each item starting from v6.5.4
             */
            $itemQtyShipped = isset($data['shipped_qty'][$orderItemId]) ? $data['shipped_qty'][$orderItemId] : 0;
            $itemQty = $item->getChildObject()->getQtyPurchased();

            if ($itemQtyShipped >= $itemQty) {
                continue;
            }

            if ($itemQty > $qtyAvailable) {
                $itemQty = $qtyAvailable;
            }

            $shipmentItems[] = array(
                'amazon_order_item_id' => $orderItemId,
                'qty'                  => $itemQty
            );

            $qtyAvailable -= $itemQty;
            $data['shipped_qty'][$orderItemId] = $itemQty;
        }
        unset($data);

        $additionalData[Helper::CUSTOM_IDENTIFIER]['shipments'][$shipmentItem->getId()] = $shipmentItems;

        $shipmentItem->getOrderItem()->setAdditionalData(
            Mage::helper('M2ePro')->serialize($additionalData)
        );
        $shipmentItem->getOrderItem()->save();

        return $shipmentItems;
    }

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return array
     */
    protected function getTrackingDetails(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Shipment $shipment)
    {
        return array_merge(
            parent::getTrackingDetails($order, $shipment),
            array('fulfillment_date' => $shipment->getCreatedAt())
        );
    }

    /**
     * @return string
     */
    protected function getComponentMode()
    {
        return Ess_M2ePro_Helper_Component_Amazon::NICK;
    }

    //########################################
}
