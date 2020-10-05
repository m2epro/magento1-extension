<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Helper_Data as Helper;

class Ess_M2ePro_Model_Walmart_Order_Shipment_Handler extends Ess_M2ePro_Model_Order_Shipment_Handler
{
    //########################################

    protected function getItemsToShipForShipmentItem(
        Ess_M2ePro_Model_Order $order,
        Mage_Sales_Model_Order_Shipment_Item $shipmentItem
    ) {
        $additionalData = Mage::helper('M2ePro')->unserialize($shipmentItem->getOrderItem()->getAdditionalData());

        if (isset($additionalData[Helper::CUSTOM_IDENTIFIER]['shipments'][$shipmentItem->getId()])) {
            return $additionalData[Helper::CUSTOM_IDENTIFIER]['shipments'][$shipmentItem->getId()];
        }

        if (!isset($additionalData[Helper::CUSTOM_IDENTIFIER]['items']) ||
            !is_array($additionalData[Helper::CUSTOM_IDENTIFIER]['items'])) {
            return array();
        }

        $shipmentItems = array();
        $qtyAvailable = (int)$shipmentItem->getQty();

        $dataSize = count($additionalData[Helper::CUSTOM_IDENTIFIER]['items']);
        $itemsCollection = $order->getItemsCollection();
        for ($i = 0; $i < $dataSize; $i++) {
            $data = $additionalData[Helper::CUSTOM_IDENTIFIER]['items'][$i];
            if ($qtyAvailable <= 0 || !isset($data['order_item_id'])) {
                continue;
            }

            /** @var Ess_M2ePro_Model_Order_Item $item */
            $orderItemId = $data['order_item_id'];
            $itemsCollection->clear()->getSelect()->reset(Zend_Db_Select::WHERE);
            $item = $itemsCollection->addFieldToFilter('walmart_order_item_id', $orderItemId)->getFirstItem();
            if ($item === null) {
                continue;
            }

            /**
             * Walmart returns the same Order Item more than one time with single QTY. That data was merged
             */
            $mergedOrderItems = $item->getChildObject()->getMergedWalmartOrderItemIds();
            while ($mergedOrderItemId = array_shift($mergedOrderItems)) {
                if (!isset($data['shipped_qty'][$mergedOrderItemId])) {
                    $orderItemId = $mergedOrderItemId;
                    break;
                }
            }

            /**
             * - Extension stores Refunded QTY for each item starting from v6.5.4
             * - Walmart Order Item QTY is always equals 1
             */
            $itemQtyShipped = isset($data['shipped_qty'][$orderItemId]) ? $data['shipped_qty'][$orderItemId] : 0;
            $itemQty = 1;

            if ($itemQtyShipped >= $itemQty) {
                continue;
            }

            if ($itemQty > $qtyAvailable) {
                $itemQty = $qtyAvailable;
            }

            $shipmentItems[] = array(
                'walmart_order_item_id' => $orderItemId,
                'qty'                   => $itemQty
            );

            $qtyAvailable -= $itemQty;
            $data['shipped_qty'][$orderItemId] = $itemQty;

            $additionalData[Helper::CUSTOM_IDENTIFIER]['items'][$i] = $data;
            $mergedOrderItemId && $i--;
        }

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
        return Ess_M2ePro_Helper_Component_Walmart::NICK;
    }

    //########################################
}
