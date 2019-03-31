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

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return int
     */
    public function handle(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Shipment $shipment)
    {
        if (!$order->isComponentModeWalmart()) {
            throw new InvalidArgumentException('Invalid component mode.');
        }

        $trackingDetails = $this->getTrackingDetails($order, $shipment);

        if (!$order->getChildObject()->canUpdateShippingStatus($trackingDetails)) {
            return self::HANDLE_RESULT_SKIPPED;
        }

        $items = $this->getItemsToShip($order, $shipment);
        $trackingDetails['fulfillment_date'] = $shipment->getCreatedAt();

        return $order->getChildObject()->updateShippingStatus($trackingDetails, $items)
            ? self::HANDLE_RESULT_SUCCEEDED
            : self::HANDLE_RESULT_FAILED;
    }

    /**
     * @param Ess_M2ePro_Model_Order          $order
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @throws LogicException
     *
     * @return array
     */
    private function getItemsToShip(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Shipment $shipment)
    {
        $itemsToShip = array();

        foreach ($shipment->getAllItems() as $shipmentItem) {
            /** @var Mage_Sales_Model_Order_Shipment_Item $shipmentItem */

            $additionalData = $shipmentItem->getOrderItem()->getAdditionalData();
            $additionalData = is_string($additionalData) ? @unserialize($additionalData) : array();

            //--
            if (isset($additionalData[Helper::CUSTOM_IDENTIFIER]['shipments'][$shipmentItem->getId()])) {
                $itemsToShip = array_merge(
                    $itemsToShip, $additionalData[Helper::CUSTOM_IDENTIFIER]['shipments'][$shipmentItem->getId()]
                );
                continue;
            }
            //--

            if (!isset($additionalData[Helper::CUSTOM_IDENTIFIER]['items']) ||
                !is_array($additionalData[Helper::CUSTOM_IDENTIFIER]['items'])) {
                continue;
            }

            $shipmentItems = array();
            $qtyAvailable = (int)$shipmentItem->getQty();

            $dataSize = count($additionalData[Helper::CUSTOM_IDENTIFIER]['items']);
            for ($i = 0; $i < $dataSize; $i++) {

                $data = $additionalData[Helper::CUSTOM_IDENTIFIER]['items'][$i];
                if ($qtyAvailable <= 0 || !isset($data['order_item_id'])) {
                    continue;
                }

                /** @var Ess_M2ePro_Model_Order_Item $item */
                $orderItemId = $data['order_item_id'];
                $item = $order->getItemsCollection()->getItemByColumnValue('walmart_order_item_id', $orderItemId);
                if (is_null($item)) {
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

            $itemsToShip = array_merge($itemsToShip, $shipmentItems);
            $additionalData[Helper::CUSTOM_IDENTIFIER]['shipments'][$shipmentItem->getId()] = $shipmentItems;

            $shipmentItem->getOrderItem()->setAdditionalData(serialize($additionalData));
            $shipmentItem->getOrderItem()->save();
        }

        return $itemsToShip;
    }

    //########################################
}
